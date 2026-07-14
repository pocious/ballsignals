<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your BallSignals VIP Access Has Expired</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: 'Helvetica Neue', Arial, sans-serif; color: #111827; }
        .wrapper { max-width: 580px; margin: 32px auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07); }
        .header { background: #0a0f1a; padding: 28px 32px; text-align: center; }
        .logo-text { font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px; }
        .logo-text span { color: #4ade80; }
        .badge { display: inline-block; margin-top: 10px; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #f87171; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 14px; border-radius: 999px; }
        .body { padding: 32px; }
        .greeting { font-size: 15px; color: #374151; line-height: 1.7; margin-bottom: 24px; }
        .expired-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .expired-box p { font-size: 14px; color: #991b1b; line-height: 1.6; }
        .perks { margin-bottom: 24px; }
        .perks p { font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .perk { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px; }
        .perk-icon { width: 20px; height: 20px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 11px; margin-top: 1px; }
        .perk-text { font-size: 13px; color: #374151; line-height: 1.5; }
        .btn { display: block; text-align: center; background: #16a34a; color: #ffffff; text-decoration: none; padding: 14px 24px; border-radius: 10px; font-size: 15px; font-weight: 700; margin-bottom: 24px; }
        .divider { border: none; border-top: 1px solid #f3f4f6; margin: 24px 0; }
        .footer { text-align: center; padding: 20px 32px 28px; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.7; }
        .footer a { color: #6b7280; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        <div class="header">
            <div class="logo-text">Ball<span>Signals</span></div>
            <div class="badge">❌ Subscription Expired</div>
        </div>

        <div class="body">
            <p class="greeting">
                Hi <strong>{{ $sub->name }}</strong>,<br><br>
                Your BallSignals VIP subscription expired on
                <strong>{{ $sub->expires_at->format('d M Y') }}</strong>.
                Your premium access has been paused — renew now to get back in.
            </p>

            <div class="expired-box">
                <p>
                    <strong>Your access is currently inactive.</strong>
                    You can no longer view premium tips, accumulators, or VIP alerts.
                    Renew to restore full access instantly.
                </p>
            </div>

            <div class="perks">
                <p>What you get when you renew</p>
                <div class="perk">
                    <div class="perk-icon">✓</div>
                    <div class="perk-text">Daily high-confidence premium tips</div>
                </div>
                <div class="perk">
                    <div class="perk-icon">✓</div>
                    <div class="perk-text">ML-powered predictions using team form + live odds</div>
                </div>
                <div class="perk">
                    <div class="perk-icon">✓</div>
                    <div class="perk-text">One premium accumulator pick per day</div>
                </div>
                <div class="perk">
                    <div class="perk-icon">✓</div>
                    <div class="perk-text">Basketball tips — NBA, EuroLeague and more</div>
                </div>
                <div class="perk">
                    <div class="perk-icon">✓</div>
                    <div class="perk-text">Early tip alerts via email and Telegram</div>
                </div>
            </div>

            <a href="{{ $renewUrl }}" class="btn">Renew My {{ $sub->plan_label }} Plan →</a>

            <hr class="divider">

            <p style="font-size:13px;color:#6b7280;line-height:1.7;text-align:center;">
                Prefer a different plan?
                <a href="{{ config('app.url') }}/premium" style="color:#16a34a;font-weight:600;">View all plans →</a>
            </p>
        </div>

        <div class="footer">
            <p>
                You're receiving this because you had an active subscription with
                <a href="{{ config('app.url') }}">BallSignals</a>.<br>
                Questions? <a href="{{ config('app.url') }}/contact">Contact us</a>.
            </p>
        </div>

    </div>
</div>
</body>
</html>
