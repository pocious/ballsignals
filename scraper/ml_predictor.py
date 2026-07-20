"""
BallSignals — ML Outcome Predictor
Trains a GradientBoosting classifier on historical betting_tips data
(form strings + bookmaker odds) to predict Home Win / Draw / Away Win.

Model is retrained weekly from your own DB results.
Falls back to None when not enough data or scikit-learn is missing.
"""

import os
import sys
import pickle
import numpy as np
from datetime import datetime

MODEL_PATH          = os.path.join(os.path.dirname(__file__), 'ml_model.pkl')
MIN_SAMPLES         = 30    # minimum won records before we trust the model
CONFIDENCE_THRESHOLD = 0.58  # only use ML prediction if it's at least this confident
RETRAIN_DAYS        = 7     # retrain weekly


# ── Feature engineering ────────────────────────────────────────────────────────

def _form_features(form: str) -> list:
    """Convert form string like 'WWDLW' into 6 numerical features."""
    if not form:
        return [0, 0, 0, 0, 0.0, 0]
    form   = form.upper().strip()[:5]
    wins   = form.count('W')
    draws  = form.count('D')
    losses = form.count('L')
    n      = max(len(form), 1)
    points = wins * 3 + draws
    rate   = wins / n
    # streak: +N = N-game win streak, -N = loss streak, 0 = draw/mixed
    streak = 0
    if form:
        last = form[-1]
        count = 0
        for c in reversed(form):
            if c == last:
                count += 1
            else:
                break
        streak = count if last == 'W' else (-count if last == 'L' else 0)
    return [wins, draws, losses, points, rate, streak]


def _build_features(home_form, away_form, home_odds, draw_odds, away_odds) -> list:
    """Build the full 22-feature vector for one match."""
    hf = _form_features(home_form or '')
    af = _form_features(away_form or '')
    h  = float(home_odds or 2.0)
    d  = float(draw_odds or 3.5)
    a  = float(away_odds or 2.0)

    margin = (1/h + 1/d + 1/a) or 1.0
    ph = (1/h) / margin   # normalised implied probability — home
    pd = (1/d) / margin   # draw
    pa = (1/a) / margin   # away

    gap       = abs(h - a)
    form_pts  = hf[3] - af[3]   # points differential (home minus away)
    form_wins = hf[0] - af[0]   # win differential

    return hf + af + [h, d, a, ph, pd, pa, gap, form_pts, form_wins]
    # 6 + 6 + 3 + 3 + 1 + 1 + 1 = 21 features + gap = 22 total


# ── Training ───────────────────────────────────────────────────────────────────

def train(cursor) -> bool:
    """
    Train model from DB historical data.
    Only uses records where the prediction was a 1x2 call AND it won
    (these are the only unambiguous actual-outcome labels we have).
    Returns True if model was saved.
    """
    try:
        from sklearn.ensemble import GradientBoostingClassifier
        from sklearn.model_selection import cross_val_score
    except ImportError:
        print("  [ML] scikit-learn missing — run: pip install scikit-learn numpy", file=sys.stderr)
        return False

    cursor.execute("""
        SELECT home_form, away_form, home_odds, draw_odds, away_odds, prediction
        FROM betting_tips
        WHERE status = 'won'
          AND prediction IN ('Home Win', 'Draw', 'Away Win')
          AND home_odds IS NOT NULL
          AND away_odds IS NOT NULL
          AND sport = 'Football'
        ORDER BY created_at DESC
        LIMIT 10000
    """)
    rows = cursor.fetchall()

    if len(rows) < MIN_SAMPLES:
        print(f"  [ML] Not enough data to train ({len(rows)} records, need {MIN_SAMPLES})", file=sys.stderr)
        return False

    # rows may be tuples (col order: home_form, away_form, home_odds, draw_odds, away_odds, prediction)
    def _row(r, key):
        keys = ['home_form', 'away_form', 'home_odds', 'draw_odds', 'away_odds', 'prediction']
        return r[key] if isinstance(r, dict) else r[keys.index(key)]

    X = np.array(
        [_build_features(_row(r,'home_form'), _row(r,'away_form'),
                         _row(r,'home_odds'), _row(r,'draw_odds'), _row(r,'away_odds'))
         for r in rows],
        dtype=float,
    )
    y = [_row(r, 'prediction') for r in rows]

    clf = GradientBoostingClassifier(
        n_estimators=150, max_depth=4, learning_rate=0.1,
        subsample=0.8, random_state=42,
    )
    clf.fit(X, y)

    # Cross-validation accuracy
    try:
        n_classes = len(set(y))
        cv = min(5, n_classes, len(rows) // 10)
        if cv >= 2:
            scores   = cross_val_score(clf, X, y, cv=cv, scoring='accuracy')
            accuracy = round(float(scores.mean()), 3)
        else:
            accuracy = None
    except Exception:
        accuracy = None

    with open(MODEL_PATH, 'wb') as f:
        pickle.dump({
            'model':      clf,
            'trained_at': datetime.now().isoformat(),
            'n_samples':  len(rows),
            'accuracy':   accuracy,
            'classes':    list(clf.classes_),
        }, f)

    acc_str = f" | CV accuracy: {accuracy:.1%}" if accuracy else ''
    print(f"  [ML] Model trained — {len(rows)} samples{acc_str}", file=sys.stderr)
    return True


# ── Inference ──────────────────────────────────────────────────────────────────

def predict(home_form, away_form, home_odds, draw_odds, away_odds):
    """
    Predict match outcome.
    Returns (prediction: str, confidence: float) or None if model unavailable.
    """
    if not os.path.exists(MODEL_PATH):
        return None
    try:
        with open(MODEL_PATH, 'rb') as f:
            saved = pickle.load(f)
        clf   = saved['model']
        feats = np.array(
            [_build_features(home_form, away_form, home_odds, draw_odds, away_odds)],
            dtype=float,
        )
        proba = clf.predict_proba(feats)[0]
        idx   = int(np.argmax(proba))
        return clf.classes_[idx], round(float(proba[idx]), 3)
    except Exception as e:
        print(f"  [ML] Predict error: {e}", file=sys.stderr)
        return None


# ── Utilities ──────────────────────────────────────────────────────────────────

def needs_training() -> bool:
    """Returns True if no model exists or it's older than RETRAIN_DAYS."""
    if not os.path.exists(MODEL_PATH):
        return True
    try:
        with open(MODEL_PATH, 'rb') as f:
            saved = pickle.load(f)
        trained = datetime.fromisoformat(saved.get('trained_at', '2000-01-01'))
        return (datetime.now() - trained).days >= RETRAIN_DAYS
    except Exception:
        return True


def model_info() -> str:
    if not os.path.exists(MODEL_PATH):
        return 'not trained yet'
    try:
        with open(MODEL_PATH, 'rb') as f:
            saved = pickle.load(f)
        n   = saved.get('n_samples', '?')
        acc = saved.get('accuracy')
        ts  = saved.get('trained_at', '')[:10]
        acc_str = f", {acc:.1%} CV accuracy" if acc else ''
        return f"{n} samples{acc_str}, trained {ts}"
    except Exception:
        return 'error'
