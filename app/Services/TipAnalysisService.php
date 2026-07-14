<?php

namespace App\Services;

use App\Models\BettingTip;

class TipAnalysisService
{
    public function generate(BettingTip $tip): string
    {
        $home       = $tip->home_team;
        $away       = $tip->away_team;
        $prediction = $tip->prediction;
        $odds       = $tip->odds ? number_format((float) $tip->odds, 2) : null;
        $confidence = $tip->confidence ?? 3;
        $league     = $tip->league ?? 'this competition';

        return match (true) {
            $prediction === 'Home Win'           => $this->homeWin($home, $away, $odds, $confidence, $league),
            $prediction === 'Away Win'           => $this->awayWin($home, $away, $odds, $confidence, $league),
            $prediction === 'Draw'               => $this->draw($home, $away, $odds, $confidence, $league),
            $prediction === 'Over 0.5'           => $this->overGoals($home, $away, '0.5', $odds, $confidence, $league),
            $prediction === 'Over 1.5'           => $this->overGoals($home, $away, '1.5', $odds, $confidence, $league),
            $prediction === 'Over 2.5'           => $this->overGoals($home, $away, '2.5', $odds, $confidence, $league),
            $prediction === 'Over 3.5'           => $this->overGoals($home, $away, '3.5', $odds, $confidence, $league),
            $prediction === 'Under 0.5'          => $this->underGoals($home, $away, '0.5', $odds, $confidence, $league),
            $prediction === 'Under 1.5'          => $this->underGoals($home, $away, '1.5', $odds, $confidence, $league),
            $prediction === 'Under 2.5'          => $this->underGoals($home, $away, '2.5', $odds, $confidence, $league),
            $prediction === 'Under 3.5'          => $this->underGoals($home, $away, '3.5', $odds, $confidence, $league),
            $prediction === 'BTTS - Yes'         => $this->bttsYes($home, $away, $odds, $confidence, $league),
            $prediction === 'BTTS - No'          => $this->bttsNo($home, $away, $odds, $confidence, $league),
            $prediction === 'Double Chance 1X'   => $this->doubleChance($home, $away, '1X', $home, $odds, $confidence, $league),
            $prediction === 'Double Chance X2'   => $this->doubleChance($home, $away, 'X2', $away, $odds, $confidence, $league),
            $prediction === 'Double Chance 12'   => $this->doubleChance12($home, $away, $odds, $confidence, $league),
            $prediction === 'HT Home Win'        => $this->halfTime($home, $away, $home, 'home', $odds, $confidence, $league),
            $prediction === 'HT Draw'            => $this->halfTimeDraw($home, $away, $odds, $confidence, $league),
            $prediction === 'HT Away Win'        => $this->halfTime($home, $away, $away, 'away', $odds, $confidence, $league),
            $prediction === 'Over 3.5 Corners'   => $this->corners($home, $away, '3.5', $odds, $confidence, $league),
            $prediction === 'Over 9.5 Corners'   => $this->corners($home, $away, '9.5', $odds, $confidence, $league),
            $prediction === 'Over 1.5 Cards'     => $this->cards($home, $away, $odds, $confidence, $league),
            default                              => $this->generic($home, $away, $prediction, $odds, $confidence, $league),
        };
    }

    // ─── Match Result ────────────────────────────────────────────────────────

    private function homeWin(string $home, string $away, ?string $odds, int $conf, string $league): string
    {
        $conf_text  = $this->confidenceText($conf);
        $odds_text  = $odds ? "priced at {$odds}" : 'at current market price';
        $risk       = $this->riskNote($conf);

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "{$home} enter this fixture as favourites on home soil, where their record and crowd support typically give them a significant advantage. Playing in front of their own fans, they have historically been difficult to beat and are expected to control the tempo of this match.\n\n"
            . "🔍 Key Factors\n"
            . "• {$home} benefit from the home advantage, which statistically accounts for roughly 46% of all football match outcomes at this level.\n"
            . "• {$away} face the challenge of an away fixture, where unfamiliar surroundings, travel fatigue, and a hostile atmosphere often affect performance.\n"
            . "• The odds of {$odds_text} reflect the bookmakers' view that {$home} are the clear favourite — a sign of genuine market confidence in a home victory.\n"
            . "• {$home}'s tactical setup at home typically favours a higher press and aggressive attacking play, which can overwhelm visiting sides.\n\n"
            . "⚠️ Risk Assessment\n"
            . "{$risk} {$away} are capable of making life difficult on the road, so a slow start or early red card could shift momentum. However, {$home}'s home record makes them the logical selection here.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Back {$home} to take all three points in front of their home crowd.";
    }

    private function awayWin(string $home, string $away, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "priced at {$odds}" : 'at current market price';
        $risk      = $this->riskNote($conf);

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "{$away} travel to face {$home} in what looks like a strong away-win opportunity. Despite playing away from home, {$away} carry enough quality and recent form to overcome the home side.\n\n"
            . "🔍 Key Factors\n"
            . "• {$away} have shown the ability to perform consistently on the road, with a compact defensive shape and dangerous counter-attacking threat.\n"
            . "• {$home} have shown vulnerability at home, particularly against organised, defensively disciplined opponents — exactly the profile {$away} carry.\n"
            . "• Away wins in football offer great value — the odds of {$odds_text} reflect a market that acknowledges {$away}'s genuine quality edge in this matchup.\n"
            . "• {$away}'s squad depth and tactical flexibility give them multiple ways to win this game, even if the early exchanges are tight.\n\n"
            . "⚠️ Risk Assessment\n"
            . "{$risk} Home sides naturally have the crowd behind them, and any early goal for {$home} could change the game's shape entirely. Discipline and concentration are key for {$away} in the opening 20 minutes.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. {$away} should have enough to secure the three points away from home.";
    }

    private function draw(string $home, string $away, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "at {$odds}" : 'at current market price';

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "This fixture has the hallmarks of a closely contested battle where neither side is expected to pull clear. Both {$home} and {$away} appear evenly matched in quality, form, and tactical approach, pointing strongly towards a share of the spoils.\n\n"
            . "🔍 Key Factors\n"
            . "• The gap in quality between {$home} and {$away} is minimal, making a decisive winner difficult to call with confidence.\n"
            . "• Draws occur in approximately 25–28% of all football matches, and this fixture's dynamics — two cautious, well-organised sides — pushes that probability higher.\n"
            . "• Neither team can afford a defeat in the wider context of their league/competition standing, meaning both sides are likely to prioritise defensive solidity over attacking risk.\n"
            . "• The draw {$odds_text} offers excellent value given the game's expected pattern.\n\n"
            . "⚠️ Risk Assessment\n"
            . "A single moment of individual brilliance or a set-piece goal could separate the sides. Draws are inherently difficult to predict but the evidence here strongly favours a split.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Expect both managers to cancel each other out — back the draw.";
    }

    // ─── Goals ───────────────────────────────────────────────────────────────

    private function overGoals(string $home, string $away, string $line, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "priced at {$odds}" : 'at current market price';
        $risk      = $this->riskNote($conf);

        $line_context = match ($line) {
            '0.5' => "at least one goal to be scored — an outcome that occurs in over 95% of football matches",
            '1.5' => "two or more goals in the match — a target hit in roughly 70% of top-flight fixtures",
            '2.5' => "three or more goals across the 90 minutes — hit in around 50–55% of matches at this level",
            '3.5' => "four or more goals — a higher-risk market that requires an open, attacking game from both sides",
            default => "goals beyond {$line}",
        };

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "Our analysis points to an entertaining, goal-filled contest between {$home} and {$away}. We are backing Over {$line} Goals, meaning we need {$line_context}.\n\n"
            . "🔍 Key Factors\n"
            . "• Both {$home} and {$away} have shown attacking intent in recent outings, creating chances and putting pressure on opposing defences.\n"
            . "• {$home} playing at home typically adopt an aggressive, high-tempo style that opens space for both teams to exploit.\n"
            . "• {$away} are not a side that sits deep and absorbs — they look to attack in transition, which should create an open, back-and-forth game.\n"
            . "• Defensive errors and set-piece situations in matches between these two types of sides often add to the goal tally.\n"
            . "• The odds of {$odds_text} represent solid value for what looks like a match primed for goals.\n\n"
            . "⚠️ Risk Assessment\n"
            . "{$risk} Low-scoring matches can arise when tactical setups change due to early goals or red cards. A 0-0 or 1-0 result would void this tip, though based on current evidence that appears unlikely.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Back Over {$line} Goals as both sides have the attacking quality to deliver a high-scoring affair.";
    }

    private function underGoals(string $home, string $away, string $line, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "priced at {$odds}" : 'at current market price';

        $line_context = match ($line) {
            '0.5' => "a goalless draw — a rare but realistic outcome in matches between two very organised sides",
            '1.5' => "no more than one goal across the 90 minutes — a tight, low-scoring affair",
            '2.5' => "no more than two goals in total — the most popular under/over line in football",
            '3.5' => "no more than three goals — this gives us plenty of margin, needing only a non-thrashing result",
            default => "goals below {$line}",
        };

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "This match has the characteristics of a tight, defensively disciplined encounter. We are backing Under {$line} Goals — {$line_context}.\n\n"
            . "🔍 Key Factors\n"
            . "• Both {$home} and {$away} have shown defensive resilience in recent matches, with clean sheets and low-scoring results as a pattern.\n"
            . "• The tactical setups of both managers suggest caution, with defensive solidity prioritised over expansive attacking play.\n"
            . "• Matches between these two sides in {$league} tend to be tight affairs decided by fine margins — not goal-fests.\n"
            . "• {$home} playing at home may push forward, but {$away}'s organised defensive block should limit clear-cut chances.\n"
            . "• The odds of {$odds_text} provide fair value for what looks like a cagey, low-scoring encounter.\n\n"
            . "⚠️ Risk Assessment\n"
            . "A fast start with two early goals could put pressure on this selection. However, the defensive profiles of both sides suggest goals will be hard to come by.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Back Under {$line} Goals — defensive discipline from both sides should keep the score tight.";
    }

    // ─── BTTS ────────────────────────────────────────────────────────────────

    private function bttsYes(string $home, string $away, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "at {$odds}" : 'at current market price';

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "We are backing Both Teams to Score (BTTS — Yes) in this fixture. This market requires {$home} AND {$away} to both find the net at least once during the 90 minutes.\n\n"
            . "🔍 Key Factors\n"
            . "• {$home} have demonstrated a consistent ability to score at home, but their defence has shown a tendency to concede — particularly against sides with pace and directness like {$away}.\n"
            . "• {$away} carry a genuine goal threat on the road and are not a side that travels just to defend — they look to attack and create.\n"
            . "• BTTS-Yes lands in approximately 50–55% of matches in top European leagues, and this fixture's attacking profiles push it higher.\n"
            . "• Neither goalkeeper is likely to keep a clean sheet given the attacking quality on display from both dugouts.\n"
            . "• The odds {$odds_text} reflect reasonable value for a market with solid probability.\n\n"
            . "⚠️ Risk Assessment\n"
            . "If either side's attack is unusually wasteful or a goalkeeper delivers a heroic performance, this tip could fall short. A clean sheet from either side would void the selection.\n\n"
            . "✅ Verdict\n"
            . "This is a {$this->confidenceText($conf)} pick. Both {$home} and {$away} have the firepower to get on the scoresheet — back BTTS Yes.";
    }

    private function bttsNo(string $home, string $away, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "at {$odds}" : 'at current market price';

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "We are backing Both Teams to Score — No (BTTS — No). This market requires at least one side to keep a clean sheet. Either {$home} or {$away} (or both) must fail to score.\n\n"
            . "🔍 Key Factors\n"
            . "• At least one of these sides has shown significant defensive strength recently, keeping clean sheets and limiting opponents to very few shots on target.\n"
            . "• The attacking threat from one or both sides appears limited — whether through injuries to key forwards, poor form, or an ultra-defensive tactical setup.\n"
            . "• Matches in {$league} between defensively organised sides often see at least one team fail to register — backing BTTS-No takes advantage of this.\n"
            . "• The odds {$odds_text} represent value for a market that requires simply one clean sheet across 90 minutes.\n\n"
            . "⚠️ Risk Assessment\n"
            . "A goalkeeping error, deflected effort, or individual moment of magic could see both sides score regardless of the game's overall pattern.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Defensive solidity from at least one side should ensure BTTS-No lands.";
    }

    // ─── Double Chance ───────────────────────────────────────────────────────

    private function doubleChance(string $home, string $away, string $type, string $favoured, ?string $odds, int $conf, string $league): string
    {
        $conf_text  = $this->confidenceText($conf);
        $odds_text  = $odds ? "at {$odds}" : 'at current market price';
        $other      = $favoured === $home ? $away : $home;
        $description = match ($type) {
            '1X'   => "a {$home} win OR a draw",
            'X2'   => "an {$away} win OR a draw",
            default => "either team winning",
        };

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "We are backing Double Chance {$type} — covering {$description}. This is a safety-net market that gives us two of the three possible outcomes to win.\n\n"
            . "🔍 Key Factors\n"
            . "• {$favoured} are the stronger side in this matchup and are unlikely to lose, even if they don't dominate proceedings entirely.\n"
            . "• The Double Chance market sacrifices some odds in exchange for significantly increased security — a defeat for {$favoured} is the only losing outcome.\n"
            . "• {$other} will be competitive but may not carry enough quality to take all three points.\n"
            . "• In volatile or evenly matched fixtures, Double Chance provides a measured, disciplined approach to value betting.\n"
            . "• The odds {$odds_text} still offer a reasonable return given the improved win probability.\n\n"
            . "⚠️ Risk Assessment\n"
            . "The only way this bet loses is if {$other} win outright — which, while possible, is considered the least likely of the three outcomes.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Double Chance {$type} gives us excellent coverage and a strong chance of landing this selection.";
    }

    private function doubleChance12(string $home, string $away, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "at {$odds}" : 'at current market price';

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "We are backing Double Chance 12 — covering either a {$home} win OR an {$away} win. This market pays out as long as the match has a decisive winner (no draw).\n\n"
            . "🔍 Key Factors\n"
            . "• Both {$home} and {$away} are attack-minded sides with the firepower to win, making a draw the least likely of the three outcomes.\n"
            . "• Head-to-head history and current form indicate that encounters between these two sides tend to produce a winner rather than settling for a point each.\n"
            . "• The match dynamics — an open, attacking style from both sides — reduce the likelihood of the cagey, defensive play that typically produces draws.\n"
            . "• The odds {$odds_text} are attractive given we cover two outcomes, losing only to a draw.\n\n"
            . "⚠️ Risk Assessment\n"
            . "A tactical, cautious match where neither side takes risks could end level. However, the profiles of both sides suggest a decisive result is the most probable outcome.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Expect a winner in this one — Double Chance 12 keeps us covered regardless of which side prevails.";
    }

    // ─── Half Time ───────────────────────────────────────────────────────────

    private function halfTime(string $home, string $away, string $team, string $side, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "at {$odds}" : 'at current market price';
        $opponent  = $side === 'home' ? $away : $home;

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "We are backing {$team} to be winning at Half Time. This market focuses entirely on the first 45 minutes — {$team} must lead at the break regardless of the full-time result.\n\n"
            . "🔍 Key Factors\n"
            . "• {$team} are known for strong starts to matches — their pressing game and intensity in the opening period often yields an early goal advantage.\n"
            . "• {$opponent} have shown a tendency to struggle in the first half, particularly when facing teams that press high and force mistakes early.\n"
            . "• A {$side} half-time lead has been a consistent pattern in recent fixtures featuring {$team}, making this an informed rather than speculative selection.\n"
            . "• The odds {$odds_text} offer excellent value for a team that routinely dominates the early exchanges.\n\n"
            . "⚠️ Risk Assessment\n"
            . "Half-time markets are inherently volatile. A red card, penalty, or early set-piece goal for {$opponent} could shift the half-time scoreline. The full 45 minutes must go in {$team}'s favour for this to land.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. {$team} to lead at the break — their fast-starting style makes this a well-reasoned HT selection.";
    }

    private function halfTimeDraw(string $home, string $away, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "at {$odds}" : 'at current market price';

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "We are backing a Half Time Draw. This market requires the scores to be level at the break — 0-0, 1-1, 2-2, or any other tied scoreline after 45 minutes.\n\n"
            . "🔍 Key Factors\n"
            . "• Approximately 40–45% of all football matches are level at half time, making this one of the most frequently occurring half-time results.\n"
            . "• Both {$home} and {$away} tend to be cautious in their approach during the first half, using it to assess the opposition before committing bodies forward.\n"
            . "• Neither side's first-half attacking output has been dominant enough to consistently open scoring inside the first 45 minutes.\n"
            . "• The odds {$odds_text} present fair value given the high base probability of a level half-time score.\n\n"
            . "⚠️ Risk Assessment\n"
            . "An early penalty or individual mistake could give one side a lead they hold to the break. However, the patterns of play from both teams suggest an even first half.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Expect a cagey opening 45 minutes — back the Half Time Draw.";
    }

    // ─── Corners ─────────────────────────────────────────────────────────────

    private function corners(string $home, string $away, string $line, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "at {$odds}" : 'at current market price';

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "We are backing Over {$line} Corners in this match. This is a specialist market focused on the total number of corner kicks taken by both sides across the full 90 minutes.\n\n"
            . "🔍 Key Factors\n"
            . "• {$home} playing at home tend to dominate territorial possession, forcing the ball wide and winning corners through sustained attacking pressure.\n"
            . "• {$away} also generate corners when pushing for goals, particularly in the second half when chasing a result or protecting a lead with high defensive lines.\n"
            . "• Matches in {$league} averaging {$line}+ corners are the norm when at least one attacking, possession-based side is involved — which applies here.\n"
            . "• Wide players, set-piece specialists, and the tactical approach of both managers all contribute to a high corner count.\n"
            . "• The odds {$odds_text} reflect a market where the probability is genuinely in our favour.\n\n"
            . "⚠️ Risk Assessment\n"
            . "A very direct, no-nonsense game style could reduce corners if both sides bypass wide areas and play through the middle. However, the attacking profiles of both teams make this unlikely.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Back Over {$line} Corners — both sides generate corners regularly and this fixture should deliver.";
    }

    // ─── Cards ───────────────────────────────────────────────────────────────

    private function cards(string $home, string $away, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "at {$odds}" : 'at current market price';

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "We are backing Over 1.5 Cards in this match. This market requires at least 2 bookings across the full 90 minutes, which occurs in the vast majority of competitive football fixtures.\n\n"
            . "🔍 Key Factors\n"
            . "• The referee assigned to this fixture has a history of active card management, making bookings more likely in physical or contested duels.\n"
            . "• Rivalries or high-stakes fixtures in {$league} often produce a physical edge — late tackles, dissent, and time-wasting all contribute to a higher card count.\n"
            . "• {$home} and {$away} both contain combative, aggressive midfielders who operate on the edge — a recipe for early bookings.\n"
            . "• Statistically, Over 1.5 Cards lands in approximately 80–85% of all professional football matches, making this one of the highest-probability markets available.\n"
            . "• The odds {$odds_text} provide value for what is effectively a near-certainty in this context.\n\n"
            . "⚠️ Risk Assessment\n"
            . "A very open, flowing match with minimal fouls could see a referee choose not to intervene. An early red card can also ironically lead to less physical confrontation. These scenarios are rare.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Over 1.5 Cards is one of the safest markets in football — back it with confidence here.";
    }

    // ─── Generic Fallback ────────────────────────────────────────────────────

    private function generic(string $home, string $away, string $prediction, ?string $odds, int $conf, string $league): string
    {
        $conf_text = $this->confidenceText($conf);
        $odds_text = $odds ? "at {$odds}" : 'at current market price';

        return "{$home} vs {$away} — {$league}\n\n"
            . "📊 Match Overview\n"
            . "After thorough analysis of this fixture, our team is backing {$prediction} for the {$home} vs {$away} encounter in {$league}.\n\n"
            . "🔍 Key Factors\n"
            . "• Current form, tactical setups, and historical data all support the case for {$prediction} in this matchup.\n"
            . "• The market odds of {$odds_text} reflect reasonable value given the underlying probability of this outcome.\n"
            . "• Both sides' recent performances, squad availability, and motivation levels have been factored into this selection.\n"
            . "• Statistical models and expert analyst input point consistently towards {$prediction} as the highest-value outcome in this fixture.\n\n"
            . "⚠️ Risk Assessment\n"
            . "As with all football predictions, unexpected events — injuries, red cards, weather conditions — can influence results. Manage your stake accordingly.\n\n"
            . "✅ Verdict\n"
            . "This is a {$conf_text} pick. Our analysts back {$prediction} with conviction based on the evidence available.";
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function confidenceText(int $conf): string
    {
        return match (true) {
            $conf >= 5 => 'very high confidence (★★★★★)',
            $conf >= 4 => 'high confidence (★★★★)',
            $conf >= 3 => 'medium confidence (★★★)',
            $conf >= 2 => 'low-medium confidence (★★)',
            default    => 'speculative (★)',
        };
    }

    private function riskNote(int $conf): string
    {
        return match (true) {
            $conf >= 5 => 'Risk is minimal based on current evidence.',
            $conf >= 4 => 'Risk is low but not zero — no outcome in football is guaranteed.',
            $conf >= 3 => 'There is a moderate level of risk attached to this selection.',
            default    => 'This carries above-average risk — stake accordingly.',
        };
    }
}
