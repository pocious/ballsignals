<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ScrapeOdds extends Command
{
    protected $signature = 'tips:scrape
        {--results-only : Only update results, skip fetching fixtures}
        {--fixtures-only : Only fetch fixtures, skip result updates}
        {--week : Fetch all fixtures for the next 7 days}
        {--update-odds : Refresh pending tips that have placeholder neutral odds with real Sofascore odds}
        {--update-form : Backfill home_form/away_form on pending tips that currently have no form data}
        {--backfill-scores : Populate home_score/away_score on already-settled tips that are missing scores}
        {--odds-api : Fetch football fixtures directly from The Odds API (use when Sofascore is blocked)}
        {--basketball : Fetch basketball fixtures + results from The Odds API only}
        {--all-sports : Fetch all sports (basketball, tennis, cricket, MMA, baseball, NFL, NHL, rugby) from The Odds API}';

    protected $description = 'Fetch football + basketball tips and results via The Odds API + API-Football (pure PHP, no scraping)';

    // ── Config ────────────────────────────────────────────────────────────────

    private string $oddsKey  = '';
    private string $afKey    = '';
    private int    $afQuota  = 9999;
    private array  $oddsCache = [];
    private array  $formCache = [];

    private const ODDS_BASE = 'https://api.the-odds-api.com/v4/sports';
    private const AF_BASE   = 'https://v3.football.api-sports.io';

    private const FOOTBALL_LEAGUES = [
        39  => ['name' => 'Premier League',        'country' => 'England',      'odds_key' => 'soccer_epl'],
        140 => ['name' => 'La Liga',               'country' => 'Spain',        'odds_key' => 'soccer_spain_la_liga'],
        135 => ['name' => 'Serie A',               'country' => 'Italy',        'odds_key' => 'soccer_italy_serie_a'],
        78  => ['name' => 'Bundesliga',            'country' => 'Germany',      'odds_key' => 'soccer_germany_bundesliga'],
        61  => ['name' => 'Ligue 1',               'country' => 'France',       'odds_key' => 'soccer_france_ligue_one'],
        2   => ['name' => 'Champions League',      'country' => 'Europe',       'odds_key' => 'soccer_uefa_champs_league'],
        3   => ['name' => 'Europa League',         'country' => 'Europe',       'odds_key' => 'soccer_uefa_europa_league'],
        848 => ['name' => 'Conference League',     'country' => 'Europe',       'odds_key' => 'soccer_uefa_conference_league'],
        88  => ['name' => 'Eredivisie',            'country' => 'Netherlands',  'odds_key' => 'soccer_netherlands_eredivisie'],
        94  => ['name' => 'Primeira Liga',         'country' => 'Portugal',     'odds_key' => 'soccer_portugal_primeira_liga'],
        179 => ['name' => 'Scottish Premiership',  'country' => 'Scotland',     'odds_key' => 'soccer_scotland_premiership'],
        203 => ['name' => 'Super Lig',             'country' => 'Turkey',       'odds_key' => 'soccer_turkey_super_league'],
        144 => ['name' => 'Belgian Pro League',    'country' => 'Belgium',      'odds_key' => 'soccer_belgium_first_division'],
        253 => ['name' => 'MLS',                   'country' => 'USA',          'odds_key' => 'soccer_usa_mls'],
        71  => ['name' => 'Brasileirao',           'country' => 'Brazil',       'odds_key' => 'soccer_brazil_campeonato'],
        128 => ['name' => 'Primera Division',      'country' => 'Argentina',    'odds_key' => 'soccer_argentina_primera_division'],
        262 => ['name' => 'Liga MX',               'country' => 'Mexico',       'odds_key' => 'soccer_mexico_ligamx'],
        307 => ['name' => 'Saudi Pro League',      'country' => 'Saudi Arabia', 'odds_key' => 'soccer_saudi_arabias_league'],
        98  => ['name' => 'J1 League',             'country' => 'Japan',        'odds_key' => 'soccer_japan_j_league'],
        // Summer-active leagues (Nordic/Eastern Europe run Apr–Nov; fill the gap when big leagues are on break)
        113 => ['name' => 'Allsvenskan',           'country' => 'Sweden',       'odds_key' => 'soccer_sweden_allsvenskan'],
        103 => ['name' => 'Eliteserien',           'country' => 'Norway',       'odds_key' => 'soccer_norway_eliteserien'],
        119 => ['name' => 'Superliga',             'country' => 'Denmark',      'odds_key' => 'soccer_denmark_superliga'],
        218 => ['name' => 'Austrian Bundesliga',   'country' => 'Austria',      'odds_key' => 'soccer_austria_bundesliga'],
        207 => ['name' => 'Swiss Super League',    'country' => 'Switzerland',  'odds_key' => 'soccer_switzerland_superleague'],
        106 => ['name' => 'Ekstraklasa',           'country' => 'Poland',       'odds_key' => 'soccer_poland_ekstraklasa'],
        283 => ['name' => 'Liga 1',                'country' => 'Romania',      'odds_key' => 'soccer_romania_1_liga'],
        // Leagues without an Odds API key — use API-Football predictions only
        11  => ['name' => 'Copa Sudamericana',     'country' => 'South America','odds_key' => null],
        13  => ['name' => 'Copa Libertadores',     'country' => 'South America','odds_key' => null],
        288 => ['name' => 'Premier Soccer League', 'country' => 'South Africa', 'odds_key' => null],
        233 => ['name' => 'Egypt Premier League',  'country' => 'Egypt',        'odds_key' => null],
        20  => ['name' => 'CAF Champions League',  'country' => 'Africa',       'odds_key' => null],
        332 => ['name' => 'NPFL',                  'country' => 'Nigeria',      'odds_key' => null],
        663 => ['name' => 'Kenya Premier League',  'country' => 'Kenya',        'odds_key' => null],
        650 => ['name' => 'Uganda Premier League', 'country' => 'Uganda',       'odds_key' => null],
        200 => ['name' => 'Botola Pro',            'country' => 'Morocco',      'odds_key' => null],
        169 => ['name' => 'Chinese Super League',  'country' => 'China',        'odds_key' => null],
        292 => ['name' => 'K League 1',            'country' => 'South Korea',  'odds_key' => null],
        323 => ['name' => 'Indian Super League',   'country' => 'India',        'odds_key' => null],
        435 => ['name' => 'UAE Pro League',        'country' => 'UAE',          'odds_key' => null],
    ];

    // Sofascore uniqueTournament ID → league meta (used for weekly schedule scrape)
    private const SF_TOURNAMENTS = [
        17    => ['name' => 'Premier League',        'country' => 'England',      'odds_key' => 'soccer_epl'],
        8     => ['name' => 'La Liga',               'country' => 'Spain',        'odds_key' => 'soccer_spain_la_liga'],
        23    => ['name' => 'Serie A',               'country' => 'Italy',        'odds_key' => 'soccer_italy_serie_a'],
        35    => ['name' => 'Bundesliga',            'country' => 'Germany',      'odds_key' => 'soccer_germany_bundesliga'],
        34    => ['name' => 'Ligue 1',               'country' => 'France',       'odds_key' => 'soccer_france_ligue_one'],
        7     => ['name' => 'Champions League',      'country' => 'Europe',       'odds_key' => 'soccer_uefa_champs_league'],
        679   => ['name' => 'Europa League',         'country' => 'Europe',       'odds_key' => 'soccer_uefa_europa_league'],
        17015 => ['name' => 'Conference League',     'country' => 'Europe',       'odds_key' => 'soccer_uefa_conference_league'],
        37    => ['name' => 'Eredivisie',            'country' => 'Netherlands',  'odds_key' => 'soccer_netherlands_eredivisie'],
        238   => ['name' => 'Primeira Liga',         'country' => 'Portugal',     'odds_key' => 'soccer_portugal_primeira_liga'],
        36    => ['name' => 'Scottish Premiership',  'country' => 'Scotland',     'odds_key' => 'soccer_scotland_premiership'],
        52    => ['name' => 'Super Lig',             'country' => 'Turkey',       'odds_key' => 'soccer_turkey_super_league'],
        4     => ['name' => 'Belgian Pro League',    'country' => 'Belgium',      'odds_key' => 'soccer_belgium_first_division'],
        242   => ['name' => 'MLS',                   'country' => 'USA',          'odds_key' => 'soccer_usa_mls'],
        325   => ['name' => 'Brasileirao',           'country' => 'Brazil',       'odds_key' => 'soccer_brazil_campeonato'],
        155   => ['name' => 'Primera Division',      'country' => 'Argentina',    'odds_key' => 'soccer_argentina_primera_division'],
        352   => ['name' => 'Liga MX',               'country' => 'Mexico',       'odds_key' => 'soccer_mexico_ligamx'],
        955   => ['name' => 'Saudi Pro League',      'country' => 'Saudi Arabia', 'odds_key' => 'soccer_saudi_arabias_league'],
        196   => ['name' => 'J1 League',             'country' => 'Japan',        'odds_key' => 'soccer_japan_j_league'],
        // Summer-active leagues
        40    => ['name' => 'Allsvenskan',           'country' => 'Sweden',       'odds_key' => 'soccer_sweden_allsvenskan'],
        41    => ['name' => 'Eliteserien',           'country' => 'Norway',       'odds_key' => 'soccer_norway_eliteserien'],
        39    => ['name' => 'Superliga',             'country' => 'Denmark',      'odds_key' => 'soccer_denmark_superliga'],
        45    => ['name' => 'Austrian Bundesliga',   'country' => 'Austria',      'odds_key' => 'soccer_austria_bundesliga'],
        83    => ['name' => 'Swiss Super League',    'country' => 'Switzerland',  'odds_key' => 'soccer_switzerland_superleague'],
        57    => ['name' => 'Ekstraklasa',           'country' => 'Poland',       'odds_key' => 'soccer_poland_ekstraklasa'],
        15    => ['name' => 'Copa Sudamericana',     'country' => 'South America','odds_key' => null],
        25144 => ['name' => 'U17 Africa Cup of Nations', 'country' => 'Africa',       'odds_key' => null],
        13    => ['name' => 'Copa Libertadores',     'country' => 'South America','odds_key' => null],
        186   => ['name' => 'Premier Soccer League', 'country' => 'South Africa', 'odds_key' => null],
        203   => ['name' => 'Egypt Premier League',  'country' => 'Egypt',        'odds_key' => null],
        329   => ['name' => 'NPFL',                  'country' => 'Nigeria',      'odds_key' => null],
        305   => ['name' => 'Kenya Premier League',  'country' => 'Kenya',        'odds_key' => null],
        456   => ['name' => 'Uganda Premier League', 'country' => 'Uganda',       'odds_key' => null],
        200   => ['name' => 'Botola Pro',            'country' => 'Morocco',      'odds_key' => null],
        703   => ['name' => 'Chinese Super League',  'country' => 'China',        'odds_key' => null],
        55    => ['name' => 'K League 1',            'country' => 'South Korea',  'odds_key' => null],
        323   => ['name' => 'Indian Super League',   'country' => 'India',        'odds_key' => null],
        435   => ['name' => 'UAE Pro League',        'country' => 'UAE',          'odds_key' => null],
    ];

    private const BASKETBALL_SPORTS = [
        ['key' => 'basketball_nba',        'name' => 'NBA',        'country' => 'USA'],
        ['key' => 'basketball_euroleague', 'name' => 'EuroLeague', 'country' => 'Europe'],
        ['key' => 'basketball_eurocup',    'name' => 'EuroCup',    'country' => 'Europe'],
        ['key' => 'basketball_spain_acb',  'name' => 'ACB',        'country' => 'Spain'],
        ['key' => 'basketball_ncaab',      'name' => 'NCAA',       'country' => 'USA'],
        ['key' => 'basketball_wnba',       'name' => 'WNBA',       'country' => 'USA'],
        ['key' => 'basketball_nbl',        'name' => 'NBL',        'country' => 'Australia'],
    ];

    private const TENNIS_SPORTS = [
        ['key' => 'tennis_atp_wimbledon',       'name' => 'Wimbledon (ATP)',       'country' => 'England'],
        ['key' => 'tennis_wta_wimbledon',       'name' => 'Wimbledon (WTA)',       'country' => 'England'],
        ['key' => 'tennis_atp_us_open',         'name' => 'US Open (ATP)',         'country' => 'USA'],
        ['key' => 'tennis_wta_us_open',         'name' => 'US Open (WTA)',         'country' => 'USA'],
        ['key' => 'tennis_atp_french_open',     'name' => 'French Open (ATP)',     'country' => 'France'],
        ['key' => 'tennis_wta_french_open',     'name' => 'French Open (WTA)',     'country' => 'France'],
        ['key' => 'tennis_atp_australian_open', 'name' => 'Australian Open (ATP)', 'country' => 'Australia'],
        ['key' => 'tennis_wta_australian_open', 'name' => 'Australian Open (WTA)', 'country' => 'Australia'],
    ];

    private const CRICKET_SPORTS = [
        ['key' => 'cricket_icc_world_cup',       'name' => 'ICC World Cup',     'country' => 'International'],
        ['key' => 'cricket_icc_t20_world_cup',   'name' => 'T20 World Cup',     'country' => 'International'],
        ['key' => 'cricket_ipl',                 'name' => 'IPL',               'country' => 'India'],
        ['key' => 'cricket_big_bash',            'name' => 'Big Bash',          'country' => 'Australia'],
        ['key' => 'cricket_psl',                 'name' => 'PSL',               'country' => 'Pakistan'],
        ['key' => 'cricket_sa20',                'name' => 'SA20',              'country' => 'South Africa'],
        ['key' => 'cricket_test_match',          'name' => 'Test Match',        'country' => 'International'],
    ];

    private const MMA_SPORTS = [
        ['key' => 'mma_mixed_martial_arts', 'name' => 'UFC / MMA', 'country' => 'International'],
    ];

    private const BASEBALL_SPORTS = [
        ['key' => 'baseball_mlb',  'name' => 'MLB',  'country' => 'USA'],
        ['key' => 'baseball_kbo',  'name' => 'KBO',  'country' => 'South Korea'],
        ['key' => 'baseball_npb',  'name' => 'NPB',  'country' => 'Japan'],
    ];

    private const NFL_SPORTS = [
        ['key' => 'americanfootball_nfl',   'name' => 'NFL',          'country' => 'USA'],
        ['key' => 'americanfootball_ncaaf', 'name' => 'NCAA Football', 'country' => 'USA'],
    ];

    private const NHL_SPORTS = [
        ['key' => 'icehockey_nhl',                    'name' => 'NHL',  'country' => 'USA'],
        ['key' => 'icehockey_sweden_hockey_league',   'name' => 'SHL',  'country' => 'Sweden'],
        ['key' => 'icehockey_liiga',                  'name' => 'Liiga','country' => 'Finland'],
    ];

    private const RUGBY_SPORTS = [
        ['key' => 'rugbyleague_nrl',             'name' => 'NRL',         'country' => 'Australia'],
        ['key' => 'rugbyunion_super_rugby',      'name' => 'Super Rugby', 'country' => 'International'],
        ['key' => 'rugbyunion_premiership',      'name' => 'Premiership', 'country' => 'England'],
        ['key' => 'rugbyunion_six_nations',      'name' => 'Six Nations', 'country' => 'Europe'],
        ['key' => 'rugbyunion_world_cup',        'name' => 'Rugby World Cup', 'country' => 'International'],
    ];

    // Sofascore uniqueTournament IDs for basketball
    private const SF_BASKETBALL_TOURNAMENTS = [
        132  => ['name' => 'NBA',        'country' => 'USA'],
        96   => ['name' => 'EuroLeague', 'country' => 'Europe'],
        283  => ['name' => 'WNBA',       'country' => 'USA'],
        484  => ['name' => 'NBL',        'country' => 'Australia'],
        10   => ['name' => 'NCAA',       'country' => 'USA'],
        685  => ['name' => 'EuroCup',    'country' => 'Europe'],
    ];

    // ── Entry point ───────────────────────────────────────────────────────────

    public function handle(): int
    {
        $this->oddsKey = env('ODDS_API_KEY', '');
        $this->afKey   = env('API_FOOTBALL_KEY', '');

        if (!$this->afKey) {
            $this->error('API_FOOTBALL_KEY not set in .env');
            return self::FAILURE;
        }

        $this->info('=== BallSignals Tip Fetcher (API-only) ===');

        if ($this->option('update-odds')) {
            $this->info("\n[1/1] Refreshing neutral-odds tips with real Sofascore odds...");
            $this->updateNeutralOdds();
            $this->info("\n=== Done ===");
            return self::SUCCESS;
        }

        if ($this->option('update-form')) {
            $this->info("\n[1/1] Backfilling team form from Sofascore...");
            $this->updateTeamForm();
            $this->info("\n=== Done ===");
            return self::SUCCESS;
        }

        if ($this->option('backfill-scores')) {
            $this->info("\n[1/1] Backfilling scores on settled tips...");
            $this->backfillScores();
            $this->info("\n=== Done ===");
            return self::SUCCESS;
        }

        if ($this->option('odds-api')) {
            $this->info("\n[1/1] Fetching football fixtures from The Odds API...");
            $this->fetchFromOddsApi();
            $this->info("\n=== Done ===");
            return self::SUCCESS;
        }

        if ($this->option('basketball')) {
            $this->info("\n[1/2] Fetching basketball fixtures from The Odds API...");
            $this->fetchBasketball();
            $this->info("\n[2/2] Updating basketball results...");
            $this->updateBasketballResults();
            $this->info("\n=== Done ===");
            return self::SUCCESS;
        }

        if ($this->option('all-sports')) {
            $this->info("\n[1/2] Fetching all sports from The Odds API...");
            $this->fetchBasketball();
            $this->fetchSport(self::TENNIS_SPORTS,  'Tennis');
            $this->fetchSport(self::CRICKET_SPORTS, 'Cricket');
            $this->fetchSport(self::MMA_SPORTS,     'MMA');
            $this->fetchSport(self::BASEBALL_SPORTS,'Baseball');
            $this->fetchSport(self::NFL_SPORTS,     'American Football');
            $this->fetchSport(self::NHL_SPORTS,     'Hockey');
            $this->fetchSport(self::RUGBY_SPORTS,   'Rugby');
            $this->info("\n[2/2] Updating results...");
            $this->updateBasketballResults();
            $this->updateSportResults(self::TENNIS_SPORTS,  'Tennis');
            $this->updateSportResults(self::CRICKET_SPORTS, 'Cricket');
            $this->updateSportResults(self::MMA_SPORTS,     'MMA');
            $this->updateSportResults(self::BASEBALL_SPORTS,'Baseball');
            $this->updateSportResults(self::NFL_SPORTS,     'American Football');
            $this->updateSportResults(self::NHL_SPORTS,     'Hockey');
            $this->updateSportResults(self::RUGBY_SPORTS,   'Rugby');
            $this->info("\n=== Done ===");
            return self::SUCCESS;
        }

        if (!$this->option('results-only')) {
            if ($this->option('week')) {
                $this->info("\n[1/3] Scraping full week fixtures from Sofascore...");
                $this->scrapeWeekFromSofascore();
            } else {
                $this->info("\n[1/3] Fetching football fixtures + odds...");
                $this->fetchFootball();
            }

            $this->info("\n[2/3] Fetching other sports fixtures + odds...");
            if ($this->option('week')) {
                $this->scrapeBasketballWeekFromSofascore();
            } else {
                $this->fetchBasketball();
            }
            $this->fetchSport(self::TENNIS_SPORTS,  'Tennis');
            $this->fetchSport(self::CRICKET_SPORTS, 'Cricket');
            $this->fetchSport(self::MMA_SPORTS,     'MMA');
            $this->fetchSport(self::BASEBALL_SPORTS,'Baseball');
            $this->fetchSport(self::NFL_SPORTS,     'American Football');
            $this->fetchSport(self::NHL_SPORTS,     'Hockey');
            $this->fetchSport(self::RUGBY_SPORTS,   'Rugby');
        }

        if (!$this->option('fixtures-only')) {
            $this->info("\n[3/3] Updating results...");
            $this->updateFootballResults();
            $this->updateBasketballResults();
            $this->updateSportResults(self::TENNIS_SPORTS,  'Tennis');
            $this->updateSportResults(self::CRICKET_SPORTS, 'Cricket');
            $this->updateSportResults(self::MMA_SPORTS,     'MMA');
            $this->updateSportResults(self::BASEBALL_SPORTS,'Baseball');
            $this->updateSportResults(self::NFL_SPORTS,     'American Football');
            $this->updateSportResults(self::NHL_SPORTS,     'Hockey');
            $this->updateSportResults(self::RUGBY_SPORTS,   'Rugby');
        }

        $this->info("\n=== Done ===");
        return self::SUCCESS;
    }

    // ── Refresh neutral-odds tips ─────────────────────────────────────────────

    private function updateNeutralOdds(): void
    {
        $tips = DB::table('betting_tips')
            ->where('sport', 'Football')
            ->where('status', 'pending')
            ->where('home_odds', 2.10)
            ->where('draw_odds', 3.40)
            ->where('away_odds', 3.20)
            ->get(['id', 'home_team', 'away_team', 'match_time', 'league', 'country', 'is_premium']);

        if ($tips->isEmpty()) {
            $this->line('  No tips with neutral placeholder odds found.');
            return;
        }

        $this->line("  Found {$tips->count()} tips with neutral odds to refresh...");

        $byDate   = $tips->groupBy(fn($t) => substr($t->match_time, 0, 10));
        $updated  = $skipped = 0;

        foreach ($byDate as $date => $dateTips) {
            $data   = $this->sfGet("/sport/football/scheduled-events/{$date}");
            $events = collect($data['events'] ?? []);

            if ($events->isEmpty()) {
                $this->warn("  [Sofascore] No events for {$date}");
                $skipped += $dateTips->count();
                continue;
            }

            foreach ($dateTips as $tip) {
                // Fuzzy-match team names to find the Sofascore event
                $best = null; $bestScore = 0.0;
                foreach ($events as $ev) {
                    similar_text(strtolower($tip->home_team), strtolower($ev['homeTeam']['name'] ?? ''), $hp);
                    similar_text(strtolower($tip->away_team), strtolower($ev['awayTeam']['name'] ?? ''), $ap);
                    $score = ($hp + $ap) / 2;
                    if ($score > $bestScore) { $bestScore = $score; $best = $ev; }
                }

                if ($bestScore < 55 || !$best) {
                    $this->warn("    [SKIP] No Sofascore match for {$tip->home_team} vs {$tip->away_team}");
                    $skipped++;
                    continue;
                }

                $markets = $this->sfOdds($best['id']);
                if (!$markets) {
                    $skipped++;
                    continue;
                }

                [$pred, $predOdds] = $this->pickPrediction($markets);
                $m1x2    = $markets['1x2'];
                $conf    = $this->calcConfidence($markets);
                $reasoning = $this->generateAnalysis(
                    $tip->home_team, $tip->away_team, $pred,
                    $markets, $tip->league, $tip->country, 'sofascore'
                );

                DB::table('betting_tips')->where('id', $tip->id)->update([
                    'home_odds'  => $m1x2['home'],
                    'draw_odds'  => $m1x2['draw'],
                    'away_odds'  => $m1x2['away'],
                    'odds'       => $predOdds,
                    'prediction' => $pred,
                    'confidence' => $conf,
                    'reasoning'  => $reasoning,
                    'analyst'    => 'Sofascore',
                    'updated_at' => now(),
                ]);

                // Mirror to tips table
                DB::table('tips')
                    ->where('home_team', $tip->home_team)
                    ->where('away_team', $tip->away_team)
                    ->whereDate('match_date', $date)
                    ->where('status', 'pending')
                    ->update([
                        'odds'       => $predOdds,
                        'prediction' => $pred,
                        'confidence' => $conf,
                        'updated_at' => now(),
                    ]);

                $updated++;
                $tag = $tip->is_premium ? 'VIP' : 'FREE';
                $this->line("    [{$tag}] {$tip->home_team} vs {$tip->away_team} → {$pred} @ {$predOdds}");
            }
        }

        $this->line("\n  Odds refresh done — updated: {$updated} | skipped/no-odds: {$skipped}");
    }

    // ── Backfill team form ────────────────────────────────────────────────────

    private function updateTeamForm(): void
    {
        $tips = DB::table('betting_tips')
            ->where('sport', 'Football')
            ->where('status', 'pending')
            ->where(fn($q) => $q->whereNull('home_form')->orWhereNull('away_form'))
            ->where('match_time', '>=', now()->startOfDay())
            ->get(['id', 'home_team', 'away_team', 'home_form', 'away_form', 'match_time', 'league']);

        if ($tips->isEmpty()) { $this->line('  No tips missing form data.'); return; }

        $this->line("  Found {$tips->count()} tips without form — fetching from Sofascore...");

        $updated = $skipped = 0;

        // Build team → Sofascore ID map by re-fetching scheduled events for each date
        $byDate = $tips->groupBy(fn($t) => substr($t->match_time, 0, 10));
        $teamIdMap = []; // team name → sofascore team id

        foreach ($byDate as $date => $dateTips) {
            $data = $this->sfGet("/sport/football/scheduled-events/{$date}");
            foreach ($data['events'] ?? [] as $ev) {
                $hName = $ev['homeTeam']['name'] ?? '';
                $aName = $ev['awayTeam']['name'] ?? '';
                $hId   = $ev['homeTeam']['id']   ?? 0;
                $aId   = $ev['awayTeam']['id']    ?? 0;
                if ($hName && $hId) $teamIdMap[$hName] = $hId;
                if ($aName && $aId) $teamIdMap[$aName] = $aId;
            }
        }

        foreach ($tips as $tip) {
            // Use already-stored form for partial tips — only fetch what's missing
            $homeForm = $tip->home_form ?: null;
            $awayForm = $tip->away_form ?: null;

            $fetchHome = !$homeForm;
            $fetchAway = !$awayForm;

            if ($fetchHome || $fetchAway) {
                $hId = $teamIdMap[$tip->home_team] ?? null;
                $aId = $teamIdMap[$tip->away_team] ?? null;

                if (!$hId || !$aId) {
                    foreach ($teamIdMap as $name => $id) {
                        similar_text(strtolower($tip->home_team), strtolower($name), $hp);
                        if ($hp > 80 && !$hId) $hId = $id;
                        similar_text(strtolower($tip->away_team), strtolower($name), $ap);
                        if ($ap > 80 && !$aId) $aId = $id;
                    }
                }

                // Search-based fallback when scheduled-events is blocked
                if ($fetchHome && !$hId) $hId = $this->sfSearchTeamId($tip->home_team);
                if ($fetchAway && !$aId) $aId = $this->sfSearchTeamId($tip->away_team);

                if ($hId || $aId) {
                    if ($fetchHome) $homeForm = $hId ? $this->sfTeamForm($hId) : null;
                    if ($fetchAway) $awayForm = $aId ? $this->sfTeamForm($aId) : null;
                }

                // Sofascore blocked or IDs not found — use API-Football
                if ($fetchHome && !$homeForm) $homeForm = $this->afTeamForm($tip->home_team);
                if ($fetchAway && !$awayForm) $awayForm = $this->afTeamForm($tip->away_team);

                // Final free fallback: TheSportsDB (no key, no quota)
                if ($fetchHome && !$homeForm) $homeForm = $this->tsdbTeamForm($tip->home_team);
                if ($fetchAway && !$awayForm) $awayForm = $this->tsdbTeamForm($tip->away_team);
            }

            if (!$homeForm && !$awayForm) { $skipped++; continue; }

            DB::table('betting_tips')->where('id', $tip->id)->update([
                'home_form'  => $homeForm ?: null,
                'away_form'  => $awayForm ?: null,
                'updated_at' => now(),
            ]);
            $updated++;
            $this->line("    [OK] {$tip->home_team} [{$homeForm}] vs {$tip->away_team} [{$awayForm}]");
        }

        $this->line("\n  Football form done — updated: {$updated} | skipped: {$skipped}");

        // ── Basketball ────────────────────────────────────────────────────────
        $btips = DB::table('betting_tips')
            ->where('sport', 'Basketball')
            ->where('status', 'pending')
            ->where(fn($q) => $q->whereNull('home_form')->orWhereNull('away_form'))
            ->where('match_time', '>=', now()->startOfDay())
            ->get(['id', 'home_team', 'away_team', 'match_time']);

        if ($btips->isNotEmpty()) {
            $this->line("\n  Found {$btips->count()} basketball tips without form...");
            $bTeamMap = [];

            $byDate = $btips->groupBy(fn($t) => substr($t->match_time, 0, 10));
            foreach ($byDate as $date => $_) {
                $data = $this->sfGet("/sport/basketball/scheduled-events/{$date}");
                foreach ($data['events'] ?? [] as $ev) {
                    $hName = $ev['homeTeam']['name'] ?? ''; $hId = $ev['homeTeam']['id'] ?? 0;
                    $aName = $ev['awayTeam']['name'] ?? ''; $aId = $ev['awayTeam']['id'] ?? 0;
                    if ($hName && $hId) $bTeamMap[$hName] = $hId;
                    if ($aName && $aId) $bTeamMap[$aName] = $aId;
                }
            }

            $bu = $bs = 0;
            foreach ($btips as $tip) {
                $hId = $bTeamMap[$tip->home_team] ?? null;
                $aId = $bTeamMap[$tip->away_team] ?? null;
                if (!$hId || !$aId) { $bs++; continue; }

                $homeForm = $this->sfTeamForm($hId);
                $awayForm = $this->sfTeamForm($aId);
                if (!$homeForm && !$awayForm) { $bs++; continue; }

                DB::table('betting_tips')->where('id', $tip->id)->update([
                    'home_form' => $homeForm ?: null, 'away_form' => $awayForm ?: null,
                    'updated_at' => now(),
                ]);
                $bu++;
                $this->line("    [OK] {$tip->home_team} [{$homeForm}] vs {$tip->away_team} [{$awayForm}]");
            }
            $this->line("  Basketball form done — updated: {$bu} | skipped: {$bs}");
        }
    }

    // ── Weekly Sofascore schedule scrape ─────────────────────────────────────

    private function sfSearchTeamId(string $name): ?int
    {
        $data = $this->sfGet('/search/all?q=' . rawurlencode($name));
        $teams = $data['teams'] ?? [];
        if (empty($teams)) return null;

        foreach ($teams as $team) {
            if (strtolower($team['name'] ?? '') === strtolower($name)) {
                return (int) ($team['id'] ?? 0) ?: null;
            }
        }

        $bestId = null; $bestPct = 0;
        foreach ($teams as $team) {
            similar_text(strtolower($name), strtolower($team['name'] ?? ''), $pct);
            if ($pct > $bestPct && $pct > 72) { $bestPct = $pct; $bestId = (int)($team['id'] ?? 0) ?: null; }
        }
        return $bestId;
    }

    private function sfGet(string $path): ?array
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.6367.208 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:126.0) Gecko/20100101 Firefox/126.0',
        ];

        try {
            $resp = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders([
                    'User-Agent'                => $userAgents[array_rand($userAgents)],
                    'Accept'                    => 'application/json, text/plain, */*',
                    'Accept-Language'           => 'en-US,en;q=0.9',
                    'Accept-Encoding'           => 'gzip, deflate, br',
                    'Referer'                   => 'https://www.sofascore.com/',
                    'Origin'                    => 'https://www.sofascore.com',
                    'Cache-Control'             => 'no-cache',
                    'Pragma'                    => 'no-cache',
                    'Sec-Fetch-Dest'            => 'empty',
                    'Sec-Fetch-Mode'            => 'cors',
                    'Sec-Fetch-Site'            => 'same-site',
                    'Sec-Ch-Ua'                 => '"Chromium";v="125", "Not.A/Brand";v="24"',
                    'Sec-Ch-Ua-Mobile'          => '?0',
                    'Sec-Ch-Ua-Platform'        => '"Windows"',
                    'Connection'                => 'keep-alive',
                ])
                ->get('https://api.sofascore.com/api/v1' . $path);

            if ($resp->successful()) {
                sleep(rand(1, 3));
                return $resp->json();
            }
            $this->warn('[Sofascore] HTTP ' . $resp->status() . ' for ' . $path);
        } catch (\Exception $e) {
            $this->warn('[Sofascore] ' . $e->getMessage());
        }
        return null;
    }

    private function sfOdds(int $eventId): ?array
    {
        $data = $this->sfGet("/event/{$eventId}/odds/1/featured");
        if (!$data) return null;

        $featured = $data['featured'] ?? [];
        $h1x2 = $ou25 = $btts = null;

        foreach ($featured as $key => $market) {
            if (!is_array($market)) continue;
            $choices  = collect($market['choices'] ?? [])->keyBy('name');
            $mktName  = strtolower($market['marketName'] ?? $key ?? '');

            if (!$h1x2 && ($key === 'default' || str_contains($mktName, '1x2') || str_contains($mktName, 'match winner') || str_contains($mktName, 'result'))) {
                $h = $this->sfPrice($choices->get('1') ?? $choices->get('Home'));
                $d = $this->sfPrice($choices->get('X') ?? $choices->get('Draw'));
                $a = $this->sfPrice($choices->get('2') ?? $choices->get('Away'));
                if ($h && $a) $h1x2 = ['home' => $h, 'draw' => $d, 'away' => $a];
            }

            if (!$ou25 && str_contains($mktName, '2.5') && (str_contains($mktName, 'over') || str_contains($mktName, 'goal'))) {
                $over  = $this->sfPrice($choices->get('Over') ?? $choices->get('Over 2.5'));
                $under = $this->sfPrice($choices->get('Under') ?? $choices->get('Under 2.5'));
                if ($over && $under) $ou25 = ['over' => $over, 'under' => $under];
            }

            if (!$btts && (str_contains($mktName, 'both teams') || str_contains($mktName, 'btts'))) {
                $yes = $this->sfPrice($choices->get('Yes'));
                $no  = $this->sfPrice($choices->get('No'));
                if ($yes && $no) $btts = ['yes' => $yes, 'no' => $no];
            }
        }

        if (!$h1x2) return null;

        $derived = $this->deriveMarkets($h1x2['home'], $h1x2['draw'] ?? 3.5, $h1x2['away']);
        return [
            '1x2'           => $h1x2,
            'over_under'    => $ou25 ?? $derived['over_under'],
            'over_under_05' => $derived['over_under_05'],
            'over_under_15' => $derived['over_under_15'],
            'over_under_35' => $derived['over_under_35'],
            'btts'          => $btts ?? $derived['btts'],
        ];
    }

    private function sfPrice(?array $choice): ?float
    {
        if (!$choice) return null;
        $raw = $choice['sourceValue'] ?? $choice['fractionalValue'] ?? null;
        if (!$raw) return null;
        $raw = trim((string) $raw);
        try {
            if (str_contains($raw, '/')) {
                [$n, $d] = explode('/', $raw, 2);
                return round((float)$n / (float)$d + 1, 2);
            }
            return round((float)$raw, 2);
        } catch (\Throwable) { return null; }
    }

    private function sfTeamForm(int $teamId): string
    {
        if (isset($this->formCache[$teamId])) return $this->formCache[$teamId];

        $data = $this->sfGet("/team/{$teamId}/events/last/0");

        // API returns newest-first; filter to only fully finished matches,
        // then take the 5 most recent and reverse for oldest→newest display
        $finished = array_values(array_filter(
            $data['events'] ?? [],
            fn($e) => ($e['status']['type'] ?? '') === 'finished'
        ));
        $events = array_reverse(array_slice($finished, 0, 5));

        $form = '';
        foreach ($events as $ev) {
            $hScore = $ev['homeScore']['current'] ?? null;
            $aScore = $ev['awayScore']['current'] ?? null;
            if ($hScore === null || $aScore === null) continue;
            $isHome = ($ev['homeTeam']['id'] ?? 0) === $teamId;
            if ($hScore === $aScore) {
                $form .= 'D';
            } elseif (($isHome && $hScore > $aScore) || (!$isHome && $aScore > $hScore)) {
                $form .= 'W';
            } else {
                $form .= 'L';
            }
        }

        return $this->formCache[$teamId] = $form;
    }

    private function scrapeWeekFromSofascore(): void
    {
        $tzOffset = (int) env('TZ_OFFSET_HOURS', 3);
        $sportId  = DB::table('sports')->where('name', 'like', '%Football%')->value('id');
        $userId   = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id')
                    ?? DB::table('users')->orderBy('id')->value('id');
        $totalBt  = $totalT = $skipped = 0;

        for ($day = 0; $day < 7; $day++) {
            $date = now()->utc()->addDays($day)->format('Y-m-d');
            $data = $this->sfGet("/sport/football/scheduled-events/{$date}");

            if (!$data) { $this->warn("  [Sofascore] No data for {$date}"); continue; }

            $events = $data['events'] ?? [];
            $target = array_filter($events, fn($e) =>
                isset(self::SF_TOURNAMENTS[$e['tournament']['uniqueTournament']['id'] ?? 0])
            );

            $this->line("  [{$date}] " . count($target) . ' target matches (from ' . count($events) . ' total)');

            $rows = []; $oddsHit = 0;

            foreach ($target as $event) {
                $home    = $event['homeTeam']['name'] ?? '';
                $away    = $event['awayTeam']['name'] ?? '';
                $tId     = $event['tournament']['uniqueTournament']['id'] ?? 0;
                $meta    = self::SF_TOURNAMENTS[$tId];
                $ts      = $event['startTimestamp'] ?? 0;
                $matchDt = (new \DateTime('@' . $ts))->modify("+{$tzOffset} hours");
                $matchTime = $matchDt->format('Y-m-d H:i:s');
                $matchDate = $matchDt->format('Y-m-d');

                if (!$home || !$away) continue;

                // Skip odds fetch entirely if match already in DB
                if (DB::table('betting_tips')
                    ->where('home_team', $home)->where('away_team', $away)
                    ->where('match_time', $matchTime)->exists()) { $skipped++; continue; }

                // 1. The Odds API — real bookmaker odds
                $markets = null; $source = 'sofascore';
                if ($meta['odds_key'] && $this->oddsKey) {
                    $markets = $this->oddsMarkets($meta['odds_key'], $home, $away);
                    if ($markets) { $source = 'odds_api'; $oddsHit++; }
                }
                // 2. Sofascore real bookmaker odds
                if (!$markets) {
                    $markets = $this->sfOdds($event['id']);
                    if ($markets) { $oddsHit++; } // source stays 'sofascore'
                }
                // 3. Neutral fallback so fixture still gets inserted
                if (!$markets) {
                    $markets = array_merge(
                        ['1x2' => ['home' => 2.10, 'draw' => 3.40, 'away' => 3.20]],
                        $this->deriveMarkets(2.10, 3.40, 3.20)
                    );
                }

                [$pred, $predOdds] = $this->pickPrediction($markets);
                $m1x2 = $markets['1x2'];

                $homeTeamId = $event['homeTeam']['id'] ?? 0;
                $awayTeamId = $event['awayTeam']['id'] ?? 0;
                $homeForm   = $homeTeamId ? $this->sfTeamForm($homeTeamId) : '';
                $awayForm   = $awayTeamId ? $this->sfTeamForm($awayTeamId) : '';

                $rows[] = [
                    'home'       => $home, 'away'       => $away,
                    'league'     => $meta['name'], 'country'    => $meta['country'],
                    'match_time' => $matchTime, 'match_date' => $matchDate,
                    'markets'    => $markets,
                    'home_odds'  => $m1x2['home'], 'draw_odds' => $m1x2['draw'], 'away_odds' => $m1x2['away'],
                    'prediction' => $pred, 'best_odds' => $predOdds,
                    'confidence' => $this->calcConfidence($markets), 'source' => $source,
                    'home_form'  => $homeForm ?: null, 'away_form' => $awayForm ?: null,
                ];
            }

            $this->line("    Odds API: {$oddsHit}/" . count($target) . ' matches with real odds');

            $premiumPicks = $this->selectPremiumThree($rows);

            foreach ($rows as $row) {
                $reasoning = $this->generateAnalysis(
                    $row['home'], $row['away'], $row['prediction'],
                    $row['markets'], $row['league'], $row['country'], $row['source']
                );
                DB::table('betting_tips')->insert($this->tipData('Football', $row, $row['prediction'], $row['best_odds'], $row['confidence'], 0, $reasoning));
                $totalBt++;
                $this->line("      [NEW] {$row['home']} vs {$row['away']} → {$row['prediction']} @ {$row['best_odds']}");

                if ($sportId && $userId && !DB::table('tips')
                    ->where('home_team', $row['home'])->where('away_team', $row['away'])
                    ->where('match_date', $row['match_date'])->exists()) {
                    DB::table('tips')->insert([
                        'user_id'      => $userId,   'sport_id'   => $sportId,
                        'match_date'   => $row['match_date'],
                        'home_team'    => $row['home'], 'away_team' => $row['away'],
                        'league'       => $row['league'],
                        'prediction'   => $row['prediction'], 'tip_type' => '1X2',
                        'odds'         => $row['best_odds'], 'confidence' => $row['confidence'],
                        'status'       => 'pending', 'is_published' => 1,
                        'published_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $totalT++;
                }
            }

            foreach ($premiumPicks as [$row, $premPred, $premOdds]) {
                if (DB::table('betting_tips')
                    ->where('home_team', $row['home'])->where('away_team', $row['away'])
                    ->where('match_time', $row['match_time'])->where('is_premium', 1)->exists()) continue;

                $reasoning = $this->generateAnalysis(
                    $row['home'], $row['away'], $premPred,
                    $row['markets'], $row['league'], $row['country'], $row['source']
                );
                DB::table('betting_tips')->insert($this->tipData('Football', $row, $premPred, $premOdds, 5, 1, $reasoning));
                $totalBt++;
                $this->line("      [VIP] {$row['home']} vs {$row['away']} → {$premPred} @ {$premOdds}");
            }
        }

        $this->line("\n  Weekly scrape done — betting_tips: +{$totalBt} | tips: +{$totalT} | skipped: {$skipped}");
    }

    // ── Football fixtures ─────────────────────────────────────────────────────

    private function fetchFootball(): void
    {
        $days  = $this->option('week') ? 7 : 2;
        $dates = collect(range(0, $days - 1))->map(fn($i) => now()->utc()->addDays($i)->format('Y-m-d'))->all();
        $sportId  = DB::table('sports')->where('name', 'like', '%Football%')->value('id');
        $userId   = DB::table('users')->orderBy('id')->where('role', 'admin')->value('id')
                    ?? DB::table('users')->orderBy('id')->value('id');
        $tzOffset = (int) env('TZ_OFFSET_HOURS', 3);
        $totalBt  = $totalT = $skipped = 0;

        foreach ($dates as $date) {
            $this->line("\n  Date: {$date}");
            $data = $this->afGet('/fixtures', ['date' => $date, 'timezone' => 'UTC']);
            if (!$data) continue;

            $target = array_filter(
                $data['response'] ?? [],
                fn($f) => isset(self::FOOTBALL_LEAGUES[$f['league']['id']])
            );
            $this->line('    ' . count($target) . ' target matches — quota remaining: ' . $this->afQuota);

            $rows    = [];
            $oddsHit = 0;

            foreach ($target as $fix) {
                if ($this->afQuota < 5) { $this->warn('    Quota low — stopping early'); break; }

                $fid      = $fix['fixture']['id'];
                $home     = $fix['teams']['home']['name'];
                $away     = $fix['teams']['away']['name'];
                $meta     = self::FOOTBALL_LEAGUES[$fix['league']['id']];

                $ts      = $fix['fixture']['timestamp'] ?? null;
                $matchDt = $ts
                    ? (new \DateTime('@' . $ts))->modify("+{$tzOffset} hours")
                    : new \DateTime($date . ' 00:00:00');
                $matchTime = $matchDt->format('Y-m-d H:i:s');
                $matchDate = $matchDt->format('Y-m-d');

                // 1. Odds API — real bookmaker odds
                $markets = null;
                $source  = 'predictions';
                if ($meta['odds_key'] && $this->oddsKey) {
                    $markets = $this->oddsMarkets($meta['odds_key'], $home, $away);
                    if ($markets) { $source = 'odds_api'; $oddsHit++; }
                }

                // 2. API-Football AI predictions — fallback
                if (!$markets) {
                    $markets = $this->afPrediction($fid);
                }

                if (!$markets) {
                    $this->line("      [SKIP] No odds: {$home} vs {$away}");
                    continue;
                }

                [$pred, $predOdds] = $this->pickPrediction($markets);
                $m1x2 = $markets['1x2'];

                $rows[] = [
                    'fixture_id' => $fid,
                    'home'       => $home,
                    'away'       => $away,
                    'league'     => $meta['name'],
                    'country'    => $meta['country'],
                    'match_time' => $matchTime,
                    'match_date' => $matchDate,
                    'markets'    => $markets,
                    'home_odds'  => $m1x2['home'],
                    'draw_odds'  => $m1x2['draw'],
                    'away_odds'  => $m1x2['away'],
                    'prediction' => $pred,
                    'best_odds'  => $predOdds,
                    'confidence' => $this->calcConfidence($markets),
                    'source'     => $source,
                ];
            }

            $this->line("    Odds API real odds: {$oddsHit}/" . count($target) . ' matches');

            $premiumPicks = $this->selectPremiumThree($rows);

            foreach ($rows as $row) {
                if (DB::table('betting_tips')
                    ->where('home_team', $row['home'])->where('away_team', $row['away'])
                    ->where('match_time', $row['match_time'])->exists()) {
                    $skipped++; continue;
                }

                $reasoning = $this->generateAnalysis(
                    $row['home'], $row['away'], $row['prediction'],
                    $row['markets'], $row['league'], $row['country'], $row['source']
                );

                DB::table('betting_tips')->insert($this->tipData('Football', $row, $row['prediction'], $row['best_odds'], $this->calcConfidence($row['markets']), 0, $reasoning));
                $totalBt++;
                $this->line("      [NEW] {$row['home']} vs {$row['away']} → {$row['prediction']} @ {$row['best_odds']}");

                if ($sportId && $userId && !DB::table('tips')
                    ->where('home_team', $row['home'])->where('away_team', $row['away'])
                    ->where('match_date', $row['match_date'])->exists()) {
                    DB::table('tips')->insert([
                        'user_id'      => $userId,   'sport_id'   => $sportId,
                        'match_date'   => $row['match_date'],
                        'home_team'    => $row['home'], 'away_team' => $row['away'],
                        'league'       => $row['league'],
                        'prediction'   => $row['prediction'],
                        'tip_type'     => '1X2',
                        'odds'         => $row['best_odds'],
                        'confidence'   => $row['confidence'],
                        'status'       => 'pending',
                        'is_published' => 1,
                        'published_at' => now(),
                        'created_at'   => now(), 'updated_at' => now(),
                    ]);
                    $totalT++;
                }
            }

            foreach ($premiumPicks as [$row, $premPred, $premOdds]) {
                if (DB::table('betting_tips')
                    ->where('home_team', $row['home'])->where('away_team', $row['away'])
                    ->where('match_time', $row['match_time'])->where('is_premium', 1)->exists()) continue;

                $reasoning = $this->generateAnalysis(
                    $row['home'], $row['away'], $premPred,
                    $row['markets'], $row['league'], $row['country'], $row['source']
                );
                DB::table('betting_tips')->insert($this->tipData('Football', $row, $premPred, $premOdds, 5, 1, $reasoning));
                $totalBt++;
                $this->line("      [VIP] {$row['home']} vs {$row['away']} → {$premPred} @ {$premOdds}");
            }
        }

        $this->line("\n  Football done — betting_tips: +{$totalBt} | tips: +{$totalT} | skipped: {$skipped}");
    }

    // ── Football results ──────────────────────────────────────────────────────

    private function updateFootballResults(): void
    {
        $updated = 0;

        // Find all dates that still have pending tips in the past (up to 14 days back)
        $dates = DB::table('betting_tips')
            ->where('sport', 'Football')
            ->where('status', 'pending')
            ->where('match_time', '<', now()->subHours(2))
            ->where('match_time', '>=', now()->subDays(14))
            ->selectRaw('DATE(match_time) as d')
            ->distinct()->orderBy('d')
            ->pluck('d')->toArray();

        if (empty($dates)) { $this->line('  No overdue pending football tips.'); return; }
        $this->line('  Checking ' . count($dates) . ' dates with pending results...');

        foreach ($dates as $date) {
            // Primary: Sofascore finished events (no quota)
            $sfData = $this->sfGet("/sport/football/scheduled-events/{$date}");
            $sfFinished = array_filter(
                $sfData['events'] ?? [],
                fn($e) => ($e['status']['type'] ?? '') === 'finished'
            );

            // Secondary: API-Football — always try as fallback when Sofascore is blocked/empty
            $afFinished = [];
            if (empty($sfFinished) && $this->afQuota >= 1) {
                $afData = $this->afGet('/fixtures', ['date' => $date, 'timezone' => 'UTC']);
                $afFinished = array_filter(
                    $afData['response'] ?? [],
                    fn($f) => in_array($f['fixture']['status']['short'] ?? '', ['FT', 'AET', 'PEN'])
                );
            }

            // Get all pending tips for this date
            $pendingTips = DB::table('betting_tips')
                ->where('sport', 'Football')
                ->whereDate('match_time', $date)
                ->where('status', 'pending')
                ->select('id', 'home_team', 'away_team', 'prediction')
                ->get();

            foreach ($pendingTips as $tip) {
                $gh = $ga = null;

                // Try Sofascore first
                foreach ($sfFinished as $ev) {
                    similar_text(strtolower($tip->home_team), strtolower($ev['homeTeam']['name'] ?? ''), $hp);
                    similar_text(strtolower($tip->away_team), strtolower($ev['awayTeam']['name'] ?? ''), $ap);
                    if (($hp + $ap) / 2 >= 70) {
                        $gh = $ev['homeScore']['current'] ?? null;
                        $ga = $ev['awayScore']['current'] ?? null;
                        break;
                    }
                }

                // Fallback: API-Football
                if ($gh === null) {
                    foreach ($afFinished as $fix) {
                        similar_text(strtolower($tip->home_team), strtolower($fix['teams']['home']['name'] ?? ''), $hp);
                        similar_text(strtolower($tip->away_team), strtolower($fix['teams']['away']['name'] ?? ''), $ap);
                        if (($hp + $ap) / 2 >= 70) {
                            $gh = $fix['goals']['home'] ?? null;
                            $ga = $fix['goals']['away'] ?? null;
                            break;
                        }
                    }
                }

                if ($gh === null || $ga === null) continue;
                $gh = (int) $gh; $ga = (int) $ga;

                $result = $this->evaluateResult($gh, $ga, $tip->prediction);
                if ($result) {
                    DB::table('betting_tips')->where('id', $tip->id)->update([
                        'status' => $result, 'home_score' => $gh, 'away_score' => $ga, 'updated_at' => now(),
                    ]);
                    $updated++;
                    $this->line("  {$tip->home_team} vs {$tip->away_team} {$gh}:{$ga} — {$tip->prediction} → " . strtoupper($result));

                    // Mirror to tips table
                    DB::table('tips')
                        ->where('home_team', $tip->home_team)->where('away_team', $tip->away_team)
                        ->where('match_date', $date)->where('status', 'pending')
                        ->update(['status' => $result, 'updated_at' => now()]);
                }
            }
        }
        $this->line("  Football results updated: {$updated}");
    }

    // ── Backfill scores on already-settled tips ───────────────────────────────

    private function backfillScores(): void
    {
        $updated = 0;

        // Find settled tips (won/lost) that are missing scores, up to 30 days back
        $dates = DB::table('betting_tips')
            ->where('sport', 'Football')
            ->whereIn('status', ['won', 'lost'])
            ->whereNull('home_score')
            ->where('match_time', '>=', now()->subDays(30))
            ->selectRaw('DATE(match_time) as d')
            ->distinct()->orderBy('d')
            ->pluck('d')->toArray();

        if (empty($dates)) {
            $this->line('  No settled tips with missing scores found.');
            return;
        }

        $this->line('  Checking ' . count($dates) . ' dates for missing scores...');

        foreach ($dates as $date) {
            $sfData = $this->sfGet("/sport/football/scheduled-events/{$date}");
            $sfFinished = array_filter(
                $sfData['events'] ?? [],
                fn($e) => ($e['status']['type'] ?? '') === 'finished'
            );

            $tips = DB::table('betting_tips')
                ->where('sport', 'Football')
                ->whereDate('match_time', $date)
                ->whereIn('status', ['won', 'lost'])
                ->whereNull('home_score')
                ->select('id', 'home_team', 'away_team', 'status')
                ->get();

            foreach ($tips as $tip) {
                $gh = $ga = null;

                foreach ($sfFinished as $ev) {
                    similar_text(strtolower($tip->home_team), strtolower($ev['homeTeam']['name'] ?? ''), $hp);
                    similar_text(strtolower($tip->away_team), strtolower($ev['awayTeam']['name'] ?? ''), $ap);
                    if (($hp + $ap) / 2 >= 70) {
                        $gh = $ev['homeScore']['current'] ?? null;
                        $ga = $ev['awayScore']['current'] ?? null;
                        break;
                    }
                }

                if ($gh === null || $ga === null) continue;

                DB::table('betting_tips')->where('id', $tip->id)->update([
                    'home_score' => (int) $gh, 'away_score' => (int) $ga, 'updated_at' => now(),
                ]);
                $updated++;
                $this->line("  {$tip->home_team} vs {$tip->away_team} → {$gh}:{$ga} (score added)");
            }
        }

        $this->line("  Scores backfilled: {$updated}");
    }

    // ── Basketball weekly Sofascore scrape ────────────────────────────────────

    private function scrapeBasketballWeekFromSofascore(): void
    {
        $tzOffset = (int) env('TZ_OFFSET_HOURS', 3);
        $inserted = $skipped = 0;

        for ($day = 0; $day < 7; $day++) {
            $date = now()->utc()->addDays($day)->format('Y-m-d');
            $data = $this->sfGet("/sport/basketball/scheduled-events/{$date}");
            if (!$data) { $this->warn("  [Sofascore] No basketball data for {$date}"); continue; }

            $events = $data['events'] ?? [];
            $target = array_filter($events, fn($e) =>
                isset(self::SF_BASKETBALL_TOURNAMENTS[$e['tournament']['uniqueTournament']['id'] ?? 0])
            );

            $this->line("  [{$date}] " . count($target) . ' basketball matches (from ' . count($events) . ' total)');

            foreach ($target as $event) {
                $home  = $event['homeTeam']['name'] ?? '';
                $away  = $event['awayTeam']['name'] ?? '';
                $tId   = $event['tournament']['uniqueTournament']['id'] ?? 0;
                $meta  = self::SF_BASKETBALL_TOURNAMENTS[$tId];
                $ts    = $event['startTimestamp'] ?? 0;
                $matchDt  = (new \DateTime('@' . $ts))->modify("+{$tzOffset} hours");
                $matchTime = $matchDt->format('Y-m-d H:i:s');

                if (!$home || !$away) continue;

                if (DB::table('betting_tips')
                    ->where('home_team', $home)->where('away_team', $away)
                    ->where('match_time', $matchTime)->exists()) { $skipped++; continue; }

                [$hOdds, $aOdds] = $this->sfBasketballOdds($event['id']);
                if (!$hOdds || !$aOdds) { $hOdds = 1.91; $aOdds = 1.91; }

                $pred   = $hOdds <= $aOdds ? 'Home Win' : 'Away Win';
                $odds   = min($hOdds, $aOdds);
                $margin = 1/$hOdds + 1/$aOdds;
                $ph = round(1/$hOdds/$margin*100);
                $pa = round(1/$aOdds/$margin*100);
                $ctx = " in the {$meta['name']}";

                $homeTeamId = $event['homeTeam']['id'] ?? 0;
                $awayTeamId = $event['awayTeam']['id'] ?? 0;
                $homeForm   = $homeTeamId ? $this->sfTeamForm($homeTeamId) : null;
                $awayForm   = $awayTeamId ? $this->sfTeamForm($awayTeamId) : null;

                $reasoning = $pred === 'Home Win'
                    ? "{$home} host {$away}{$ctx}. The market makes {$home} the favourite at {$hOdds} ({$ph}% implied), with {$away} at {$aOdds} ({$pa}%). Our call is {$home} to use home-court advantage and secure the win."
                    : "{$home} host {$away}{$ctx}. The market makes {$away} the favourite at {$aOdds} ({$pa}% implied), with {$home} at {$hOdds} ({$ph}%). Our call is {$away} to earn the road victory.";

                DB::table('betting_tips')->insert([
                    'sport'      => 'Basketball', 'home_team' => $home, 'away_team' => $away,
                    'league'     => $meta['name'], 'country'  => $meta['country'],
                    'prediction' => $pred, 'confidence' => 3,  'odds'     => $odds,
                    'home_odds'  => $hOdds, 'draw_odds' => null, 'away_odds' => $aOdds,
                    'match_time' => $matchTime, 'status' => 'pending', 'is_premium' => 0,
                    'analyst'    => 'Sofascore', 'reasoning' => $reasoning,
                    'home_form'  => $homeForm ?: null, 'away_form' => $awayForm ?: null,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $inserted++;
                $this->line("    [NEW] {$home} vs {$away} → {$pred} @ {$odds}");
            }
        }

        $this->line("\n  Basketball weekly scrape done — inserted: {$inserted} | skipped: {$skipped}");
    }

    private function sfBasketballOdds(int $eventId): array
    {
        $data = $this->sfGet("/event/{$eventId}/odds/1/featured");
        if (!$data) return [null, null];

        foreach ($data['featured'] ?? [] as $key => $market) {
            if (!is_array($market)) continue;
            $choices = collect($market['choices'] ?? [])->keyBy('name');
            $mktName = strtolower($market['marketName'] ?? $key ?? '');

            if ($key === 'default' || str_contains($mktName, 'moneyline') || str_contains($mktName, 'winner') || str_contains($mktName, '12')) {
                $h = $this->sfPrice($choices->get('1') ?? $choices->get('Home'));
                $a = $this->sfPrice($choices->get('2') ?? $choices->get('Away'));
                if ($h && $a) return [$h, $a];
            }
        }
        return [null, null];
    }

    // ── Basketball fixtures ───────────────────────────────────────────────────

    private function fetchBasketball(): void
    {
        if (!$this->oddsKey) { $this->warn('  No ODDS_API_KEY — skipping basketball'); return; }

        $inserted = $skipped = 0;
        $days     = $this->option('week') ? 7 : 3;
        $now      = now();
        $cutoff   = now()->addDays($days);
        $tzOffset = (int) env('TZ_OFFSET_HOURS', 3);

        foreach (self::BASKETBALL_SPORTS as $sport) {
            $events = $this->oddsGet($sport['key']);
            if (!$events) continue;
            $this->line("  [Basketball] {$sport['name']}: " . count($events) . ' events');

            foreach ($events as $ev) {
                $home = $ev['home_team'] ?? ''; $away = $ev['away_team'] ?? '';
                if (!$home || !$away) continue;

                try {
                    $dt = (new \DateTime($ev['commence_time'] ?? ''))->modify("+{$tzOffset} hours");
                } catch (\Exception) { continue; }

                if ($dt < $now || $dt > $cutoff) continue;
                $matchTime = $dt->format('Y-m-d H:i:s');

                if (DB::table('betting_tips')->where('home_team', $home)->where('away_team', $away)->where('match_time', $matchTime)->exists()) {
                    $skipped++; continue;
                }

                $hVals = []; $aVals = [];
                foreach ($ev['bookmakers'] ?? [] as $bm) {
                    foreach ($bm['markets'] ?? [] as $mkt) {
                        if ($mkt['key'] !== 'h2h') continue;
                        $byName = collect($mkt['outcomes'] ?? [])->pluck('price', 'name')->all();
                        if (isset($byName[$home], $byName[$away])) {
                            $hVals[] = $byName[$home]; $aVals[] = $byName[$away];
                        }
                    }
                }
                if (!$hVals || !$aVals) { $skipped++; continue; }

                $hOdds = round(array_sum($hVals) / count($hVals), 2);
                $aOdds = round(array_sum($aVals) / count($aVals), 2);

                // True implied probability (margin-removed)
                $margin  = 1/$hOdds + 1/$aOdds;
                $ph      = round(1/$hOdds / $margin * 100, 1);
                $pa      = round(1/$aOdds / $margin * 100, 1);

                $pred       = $hOdds <= $aOdds ? 'Home Win' : 'Away Win';
                $odds       = min($hOdds, $aOdds);
                $winner     = $pred === 'Home Win' ? $home : $away;
                $loser      = $pred === 'Home Win' ? $away : $home;
                $winProb    = max($ph, $pa);
                $loseProb   = min($ph, $pa);
                $winnerOdds = $odds;
                $loserOdds  = max($hOdds, $aOdds);
                $confidence = $this->oddsConfidence($winProb);
                $strength   = $this->oddsStrengthPhrase($winProb, $winner);
                $bmCount    = count($hVals);

                $gap2b   = abs($winProb - $loseProb);
                $courtPara = $pred === 'Home Win'
                    ? "Playing on their home court, {$winner} carry a meaningful crowd and routine advantage — historically a significant factor in basketball."
                    : "{$winner} arrive as the market-backed road team, suggesting their form and quality override the host's home-court edge.";

                if ($winProb >= 68) {
                    $para1b = "{$winner} are commanding favourites in this match-up — bookmakers back them with {$winProb}% implied win probability, leaving {$loser} at just {$loseProb}% ({$loserOdds}).";
                    $para2b = "A {$gap2b}% gap in a two-way market is emphatic. Across {$bmCount} bookmakers the consensus is clear: {$winner} are the significantly stronger side. {$courtPara}";
                } elseif ($winProb >= 57) {
                    $para1b = "{$winner} hold a meaningful edge at {$winProb}% implied probability, compared to {$loseProb}% for {$loser} ({$loserOdds}).";
                    $para2b = "The {$bmCount}-bookmaker consensus leans clearly towards {$winner}, though a {$loseProb}% opponent win probability means the opposition are far from out of the picture. {$courtPara}";
                } else {
                    $para1b = "A near coin-flip — {$winner} ({$winProb}%) vs {$loser} ({$loseProb}%), split by just {$gap2b}% across {$bmCount} bookmakers.";
                    $para2b = "This is a genuinely competitive contest. Both teams are capable of winning on the night. {$courtPara}";
                }

                $reasoning = "{$home} vs {$away} — {$sport['name']}.\n\n"
                    . "MATCH ODDS ({$bmCount} bookmaker" . ($bmCount !== 1 ? 's' : '') . ")\n"
                    . "{$home}: {$hOdds} ({$ph}%)  |  {$away}: {$aOdds} ({$pa}%)\n\n"
                    . "MARKET ANALYSIS\n"
                    . "{$para1b}\n"
                    . "{$para2b}\n\n"
                    . "OUR PICK: {$winner} to win @ {$winnerOdds}\n"
                    . "At {$winnerOdds}, {$winner} are the clear market selection. "
                    . $strength;

                DB::table('betting_tips')->insert([
                    'sport' => 'Basketball', 'home_team' => $home, 'away_team' => $away,
                    'league' => $sport['name'], 'country' => $sport['country'],
                    'prediction' => $pred, 'confidence' => $confidence, 'odds' => $odds,
                    'home_odds' => $hOdds, 'draw_odds' => null, 'away_odds' => $aOdds,
                    'match_time' => $matchTime, 'status' => 'pending', 'is_premium' => 0,
                    'analyst' => 'OddsAPI', 'reasoning' => $reasoning,
                    'home_form' => null, 'away_form' => null,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $inserted++;
                $this->line("    [NEW] {$home} vs {$away} → {$pred} @ {$odds} (conf:{$confidence} prob:{$winProb}%)");
            }
        }
        $this->line("  Basketball done — {$inserted} inserted, {$skipped} skipped");
    }

    // ── Basketball results ────────────────────────────────────────────────────

    private function updateBasketballResults(): void
    {
        if (!$this->oddsKey) return;
        $updated = 0; $tzOffset = (int) env('TZ_OFFSET_HOURS', 3);

        foreach (self::BASKETBALL_SPORTS as $sport) {
            try {
                $resp = Http::withoutVerifying()->get(self::ODDS_BASE . "/{$sport['key']}/scores/", [
                    'apiKey' => $this->oddsKey, 'daysFrom' => 3,
                ]);
                if (!$resp->successful()) continue;
                $scores = $resp->json();
            } catch (\Exception) { continue; }

            foreach ($scores as $ev) {
                if (!($ev['completed'] ?? false)) continue;
                $home = $ev['home_team'] ?? ''; $away = $ev['away_team'] ?? '';
                if (!$home || !$away) continue;

                try {
                    $dt = (new \DateTime($ev['commence_time'] ?? ''))->modify("+{$tzOffset} hours");
                    $matchTime = $dt->format('Y-m-d H:i:s');
                } catch (\Exception) { continue; }

                $scoreMap = collect($ev['scores'] ?? [])->pluck('score', 'name')->all();
                $gh = $scoreMap[$home] ?? null; $ga = $scoreMap[$away] ?? null;
                if ($gh === null || $ga === null) continue;
                $gh = (int) $gh; $ga = (int) $ga;

                foreach (DB::table('betting_tips')
                    ->where('home_team', $home)->where('away_team', $away)
                    ->where('match_time', $matchTime)->where('sport', 'Basketball')->where('status', 'pending')
                    ->select('id', 'prediction')->get() as $r) {
                    $status = $this->evaluateResult($gh, $ga, $r->prediction);
                    if ($status) {
                        DB::table('betting_tips')->where('id', $r->id)->update(['status' => $status, 'updated_at' => now()]);
                        $updated++;
                        $this->line("  [Basketball] {$home} vs {$away} {$gh}:{$ga} → " . strtoupper($status));
                    }
                }
            }
        }
        $this->line("  Basketball results updated: {$updated}");
    }

    // ── API clients ───────────────────────────────────────────────────────────

    private function afGet(string $endpoint, array $params = []): ?array
    {
        if ($this->afQuota < 3) { $this->warn('API-Football quota exhausted'); return null; }
        try {
            $resp = Http::withoutVerifying()
                ->withHeaders(['x-apisports-key' => $this->afKey, 'Accept' => 'application/json'])
                ->get(self::AF_BASE . $endpoint, $params);
            $rem = $resp->header('x-ratelimit-requests-remaining');
            if (is_numeric($rem)) $this->afQuota = (int) $rem;
            return $resp->successful() ? $resp->json() : null;
        } catch (\Exception $e) {
            $this->warn("API-Football error: {$e->getMessage()}"); return null;
        }
    }

    private function tsdbTeamForm(string $teamName): ?string
    {
        if (isset($this->formCache["tsdb:{$teamName}"])) return $this->formCache["tsdb:{$teamName}"];

        $search = function ($q) {
            usleep(300000); // 300ms between TheSportsDB calls to avoid rate limiting
            return Http::withoutVerifying()->timeout(10)
                ->get('https://www.thesportsdb.com/api/v1/json/3/searchteams.php', ['t' => $q])
                ->json()['teams'] ?? [];
        };

        $teams = $search($teamName);
        if (empty($teams)) {
            $trimmed = preg_replace('/\s+(CF|SC)$/i', '', $teamName);
            if ($trimmed !== $teamName) $teams = $search($trimmed);
        }
        if (empty($teams)) return $this->formCache["tsdb:{$teamName}"] = null;

        $teamId = null;
        foreach ($teams as $t) {
            if (strtolower($t['strTeam'] ?? '') === strtolower($teamName)) { $teamId = $t['idTeam']; break; }
        }
        if (!$teamId) {
            $bestPct = 0;
            foreach ($teams as $t) {
                similar_text(strtolower($teamName), strtolower($t['strTeam'] ?? ''), $pct);
                if ($pct > $bestPct && $pct > 70) { $bestPct = $pct; $teamId = $t['idTeam']; }
            }
        }
        if (!$teamId) return $this->formCache["tsdb:{$teamName}"] = null;

        usleep(300000);
        $events = Http::withoutVerifying()->timeout(10)
            ->get('https://www.thesportsdb.com/api/v1/json/3/eventslast.php', ['id' => $teamId])
            ->json()['results'] ?? [];
        if (empty($events)) return $this->formCache["tsdb:{$teamName}"] = null;

        usort($events, fn($a, $b) => strcmp($b['dateEvent'] ?? '', $a['dateEvent'] ?? ''));
        $events = array_reverse(array_slice($events, 0, 5));

        $form = '';
        foreach ($events as $ev) {
            $hs = (int)($ev['intHomeScore'] ?? -1);
            $as = (int)($ev['intAwayScore'] ?? -1);
            if ($hs < 0 || $as < 0) continue;
            similar_text(strtolower($teamName), strtolower($ev['strHomeTeam'] ?? ''), $hp);
            similar_text(strtolower($teamName), strtolower($ev['strAwayTeam'] ?? ''), $ap);
            $isHome = $hp >= $ap;
            if ($hs === $as)                                             $form .= 'D';
            elseif (($isHome && $hs > $as) || (!$isHome && $as > $hs)) $form .= 'W';
            else                                                          $form .= 'L';
        }

        return $this->formCache["tsdb:{$teamName}"] = ($form ?: null);
    }

    private function afTeamForm(string $teamName): ?string
    {
        if (isset($this->formCache["af:{$teamName}"])) return $this->formCache["af:{$teamName}"];

        $data  = $this->afGet('/teams', ['search' => $teamName]);
        $teams = $data['response'] ?? [];
        // Retry without North-American club suffixes (CF, SC) when search returns nothing
        if (empty($teams)) {
            $trimmed = preg_replace('/\s+(CF|SC)$/i', '', $teamName);
            if ($trimmed !== $teamName) {
                $data  = $this->afGet('/teams', ['search' => $trimmed]);
                $teams = $data['response'] ?? [];
            }
        }
        if (empty($teams)) return $this->formCache["af:{$teamName}"] = null;

        $teamId = null;
        foreach ($teams as $t) {
            if (strtolower($t['team']['name'] ?? '') === strtolower($teamName)) {
                $teamId = $t['team']['id']; break;
            }
        }
        if (!$teamId) {
            $bestPct = 0;
            foreach ($teams as $t) {
                similar_text(strtolower($teamName), strtolower($t['team']['name'] ?? ''), $pct);
                if ($pct > $bestPct && $pct > 70) { $bestPct = $pct; $teamId = $t['team']['id']; }
            }
        }
        if (!$teamId) return $this->formCache["af:{$teamName}"] = null;

        // Free plan: no 'last' param, no 2025 season. Use 2024 with FT filter.
        $fix   = $this->afGet('/fixtures', ['team' => $teamId, 'season' => 2024, 'status' => 'FT']);
        $games = $fix['response'] ?? [];
        if (empty($games)) return $this->formCache["af:{$teamName}"] = null;

        // Sort newest-first, take 5 most recent, then reverse to oldest→newest for display
        usort($games, fn($a, $b) => strcmp($b['fixture']['date'] ?? '', $a['fixture']['date'] ?? ''));
        $games = array_reverse(array_slice($games, 0, 5));

        $form  = '';
        foreach ($games as $g) {
            $homeId = $g['teams']['home']['id'] ?? 0;
            $hg     = $g['goals']['home'] ?? 0;
            $ag     = $g['goals']['away'] ?? 0;
            $isHome = $homeId === $teamId;
            if ($hg === $ag)                                             $form .= 'D';
            elseif (($isHome && $hg > $ag) || (!$isHome && $ag > $hg)) $form .= 'W';
            else                                                          $form .= 'L';
        }

        return $this->formCache["af:{$teamName}"] = ($form ?: null);
    }

    // ── Odds-based confidence & reasoning helpers ─────────────────────────────

    private function oddsConfidence(float $winProb): int
    {
        return match(true) {
            $winProb >= 80 => 5,
            $winProb >= 70 => 4,
            $winProb >= 62 => 3,
            $winProb >= 55 => 2,
            default        => 1,
        };
    }

    private function oddsStrengthPhrase(float $winProb, string $winner): string
    {
        return match(true) {
            $winProb >= 80 => "The market is overwhelmingly confident in {$winner} — {$winProb}% implied win probability across bookmakers.",
            $winProb >= 70 => "Bookmakers strongly back {$winner} with a {$winProb}% implied chance of winning.",
            $winProb >= 62 => "The market moderately favours {$winner} at {$winProb}% implied probability.",
            $winProb >= 55 => "{$winner} hold a slight market edge at {$winProb}% implied — a competitive contest.",
            default        => "This is a near coin-flip at {$winProb}% implied — proceed with caution.",
        };
    }

    // ── Generic sport fetch (any h2h sport via Odds API) ─────────────────────

    private function fetchSport(array $sports, string $sportName): void
    {
        if (!$this->oddsKey) { $this->warn("  No ODDS_API_KEY — skipping {$sportName}"); return; }

        $inserted = $skipped = 0;
        $days     = $this->option('week') ? 7 : 3;
        $now      = now();
        $cutoff   = now()->addDays($days);
        $tzOffset = (int) env('TZ_OFFSET_HOURS', 3);

        foreach ($sports as $sport) {
            $events = $this->oddsGet($sport['key']);
            if (!$events) continue;
            $this->line("  [{$sportName}] {$sport['name']}: " . count($events) . ' events');

            foreach ($events as $ev) {
                $home = $ev['home_team'] ?? '';
                $away = $ev['away_team'] ?? '';
                if (!$home || !$away) continue;

                try {
                    $dt = (new \DateTime($ev['commence_time'] ?? ''))->modify("+{$tzOffset} hours");
                } catch (\Exception) { continue; }

                if ($dt < $now || $dt > $cutoff) continue;
                $matchTime = $dt->format('Y-m-d H:i:s');

                if (DB::table('betting_tips')
                    ->where('home_team', $home)->where('away_team', $away)
                    ->where('match_time', $matchTime)->exists()) { $skipped++; continue; }

                $hVals = []; $aVals = []; $bmCount = 0;
                foreach ($ev['bookmakers'] ?? [] as $bm) {
                    foreach ($bm['markets'] ?? [] as $mkt) {
                        if ($mkt['key'] !== 'h2h') continue;
                        $byName = collect($mkt['outcomes'] ?? [])->pluck('price', 'name')->all();
                        if (isset($byName[$home], $byName[$away])) {
                            $hVals[] = $byName[$home]; $aVals[] = $byName[$away]; $bmCount++;
                        }
                    }
                }
                if (!$hVals || !$aVals) { $skipped++; continue; }

                $hOdds = round(array_sum($hVals) / count($hVals), 2);
                $aOdds = round(array_sum($aVals) / count($aVals), 2);

                // True implied probability (margin-removed)
                $margin  = 1/$hOdds + 1/$aOdds;
                $ph      = round(1/$hOdds / $margin * 100, 1);
                $pa      = round(1/$aOdds / $margin * 100, 1);

                $pred       = $hOdds <= $aOdds ? 'Home Win' : 'Away Win';
                $odds       = min($hOdds, $aOdds);
                $winner     = $pred === 'Home Win' ? $home : $away;
                $loser      = $pred === 'Home Win' ? $away : $home;
                $winProb    = max($ph, $pa);
                $loseProb   = min($ph, $pa);
                $winnerOdds = $odds;
                $loserOdds  = max($hOdds, $aOdds);
                $confidence = $this->oddsConfidence($winProb);
                $strength   = $this->oddsStrengthPhrase($winProb, $winner);

                // Multi-paragraph market narrative for 2-way sports
                $gap2 = abs($winProb - $loseProb);
                if ($winProb >= 70) {
                    $para1 = "{$winner} are dominant favourites — bookmakers back them with {$winProb}% implied win probability, pricing {$loser} at just {$loseProb}% ({$loserOdds}). This is a match where the market firmly expects one outcome.";
                    $para2 = "A gap of {$gap2}% between the two sides is significant in a head-to-head market — it reflects a genuine performance and form differential that {$bmCount} bookmakers have priced consistently.";
                } elseif ($winProb >= 58) {
                    $para1 = "{$winner} hold a meaningful market edge at {$winProb}% implied win probability, compared to {$loseProb}% for {$loser} ({$loserOdds}).";
                    $para2 = "This is a competitive match-up, but the market has a clear directional lean. A {$gap2}% separation across {$bmCount} bookmakers suggests {$winner} are genuinely expected to perform better on the day.";
                } else {
                    $para1 = "This shapes up as a closely-contested encounter — {$winner} ({$winProb}%) vs {$loser} ({$loseProb}%), separated by just {$gap2}% in implied probability across {$bmCount} bookmakers.";
                    $para2 = "Either outcome is fully within reach. The slight lean towards {$winner} may reflect a marginal form edge or home advantage, but discipline is required — this is a high-variance pick.";
                }

                $reasoning = "{$home} vs {$away} — {$sport['name']}.\n\n"
                    . "MATCH ODDS ({$bmCount} bookmaker" . ($bmCount !== 1 ? 's' : '') . ")\n"
                    . "{$home}: {$hOdds} ({$ph}%)  |  {$away}: {$aOdds} ({$pa}%)\n\n"
                    . "MARKET ANALYSIS\n"
                    . "{$para1}\n"
                    . "{$para2}\n\n"
                    . "OUR PICK: {$winner} to win @ {$winnerOdds}\n"
                    . "At {$winnerOdds}, {$winner} are backed by the {$bmCount}-bookmaker consensus with {$winProb}% implied probability. "
                    . $strength;

                DB::table('betting_tips')->insert([
                    'sport'      => $sportName, 'home_team' => $home, 'away_team' => $away,
                    'league'     => $sport['name'], 'country' => $sport['country'],
                    'prediction' => $pred, 'confidence' => $confidence, 'odds' => $odds,
                    'home_odds'  => $hOdds, 'draw_odds' => null, 'away_odds' => $aOdds,
                    'match_time' => $matchTime, 'status' => 'pending', 'is_premium' => 0,
                    'analyst'    => 'OddsAPI', 'reasoning' => $reasoning,
                    'home_form'  => null, 'away_form' => null,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $inserted++;
                $this->line("    [NEW] {$home} vs {$away} → {$pred} @ {$odds} (conf:{$confidence} prob:{$winProb}%)");
            }
        }
        $this->line("  {$sportName} done — {$inserted} inserted, {$skipped} skipped");
    }

    private function updateSportResults(array $sports, string $sportName): void
    {
        if (!$this->oddsKey) return;
        $updated = 0;
        $tzOffset = (int) env('TZ_OFFSET_HOURS', 3);

        foreach ($sports as $sport) {
            try {
                $resp = Http::withoutVerifying()->get(self::ODDS_BASE . "/{$sport['key']}/scores/", [
                    'apiKey' => $this->oddsKey, 'daysFrom' => 3,
                ]);
                if (!$resp->successful()) continue;
                $scores = $resp->json();
            } catch (\Exception) { continue; }

            foreach ($scores as $ev) {
                if (!($ev['completed'] ?? false)) continue;
                $home = $ev['home_team'] ?? '';
                $away = $ev['away_team'] ?? '';
                if (!$home || !$away) continue;

                try {
                    $dt = (new \DateTime($ev['commence_time'] ?? ''))->modify("+{$tzOffset} hours");
                    $matchTime = $dt->format('Y-m-d H:i:s');
                } catch (\Exception) { continue; }

                $scoreMap = collect($ev['scores'] ?? [])->pluck('score', 'name')->all();
                $gh = $scoreMap[$home] ?? null;
                $ga = $scoreMap[$away] ?? null;
                if ($gh === null || $ga === null) continue;
                $gh = (int) $gh; $ga = (int) $ga;

                foreach (DB::table('betting_tips')
                    ->where('home_team', $home)->where('away_team', $away)
                    ->where('match_time', $matchTime)->where('sport', $sportName)
                    ->where('status', 'pending')
                    ->select('id', 'prediction')->get() as $r) {
                    $status = $this->evaluateResult($gh, $ga, $r->prediction);
                    if ($status) {
                        DB::table('betting_tips')->where('id', $r->id)
                            ->update(['status' => $status, 'updated_at' => now()]);
                        $updated++;
                        $this->line("  [{$sportName}] {$home} vs {$away} {$gh}:{$ga} → " . strtoupper($status));
                    }
                }
            }
        }
        $this->line("  {$sportName} results updated: {$updated}");
    }

    private function oddsGet(string $sportKey): array
    {
        if (array_key_exists($sportKey, $this->oddsCache)) return $this->oddsCache[$sportKey];
        try {
            $resp = Http::withoutVerifying()->get(self::ODDS_BASE . "/{$sportKey}/odds/", [
                'apiKey' => $this->oddsKey, 'regions' => 'eu',
                'markets' => 'h2h,totals', 'oddsFormat' => 'decimal', 'dateFormat' => 'iso',
            ]);
            if ($resp->status() === 422) { return $this->oddsCache[$sportKey] = []; }
            if (!$resp->successful()) {
                $this->warn("[OddsAPI] HTTP {$resp->status()} for {$sportKey}");
                return $this->oddsCache[$sportKey] = [];
            }
            $events = $resp->json();
            $this->line("  [OddsAPI] {$sportKey}: " . count($events) . ' events (remaining=' . ($resp->header('x-requests-remaining') ?? '?') . ')');
            return $this->oddsCache[$sportKey] = $events;
        } catch (\Exception $e) {
            $this->warn("[OddsAPI] {$e->getMessage()}"); return $this->oddsCache[$sportKey] = [];
        }
    }

    private function oddsMarkets(string $sportKey, string $home, string $away): ?array
    {
        $events = $this->oddsGet($sportKey);
        if (!$events) return null;

        $hLow = strtolower($home); $aLow = strtolower($away);
        $best = null; $bestScore = 0.0;
        foreach ($events as $ev) {
            similar_text($hLow, strtolower($ev['home_team'] ?? ''), $hp);
            similar_text($aLow, strtolower($ev['away_team'] ?? ''), $ap);
            $score = ($hp + $ap) / 2;
            if ($score > $bestScore) { $bestScore = $score; $best = $ev; }
        }
        if ($bestScore < 68 || !$best) return null;

        $hn = $best['home_team']; $an = $best['away_team'];
        $hV=[]; $aV=[]; $dV=[]; $o25=[]; $u25=[];

        foreach ($best['bookmakers'] ?? [] as $bm) {
            foreach ($bm['markets'] ?? [] as $mkt) {
                $byName = collect($mkt['outcomes'] ?? [])->pluck('price', 'name')->all();
                if ($mkt['key'] === 'h2h') {
                    if (isset($byName[$hn], $byName[$an])) {
                        $hV[] = $byName[$hn]; $aV[] = $byName[$an];
                        if (isset($byName['Draw'])) $dV[] = $byName['Draw'];
                    }
                } elseif ($mkt['key'] === 'totals') {
                    foreach ($mkt['outcomes'] ?? [] as $o) {
                        if (($o['point'] ?? null) == 2.5) {
                            if ($o['name'] === 'Over')  $o25[] = $o['price'];
                            if ($o['name'] === 'Under') $u25[] = $o['price'];
                        }
                    }
                }
            }
        }
        if (!$hV || !$aV) return null;

        $avg = fn($a) => $a ? round(array_sum($a)/count($a), 2) : null;
        $ho = $avg($hV); $ao = $avg($aV); $do = $avg($dV);
        $derived = $this->deriveMarkets($ho, $do ?? 3.5, $ao);
        return [
            '1x2'           => ['home' => $ho, 'draw' => $do, 'away' => $ao],
            'over_under'    => ($avg($o25) && $avg($u25)) ? ['over' => $avg($o25), 'under' => $avg($u25)] : $derived['over_under'],
            'over_under_05' => $derived['over_under_05'],
            'over_under_15' => $derived['over_under_15'],
            'over_under_35' => $derived['over_under_35'],
            'btts'          => $derived['btts'],
        ];
    }

    private function afPrediction(int $fid): ?array
    {
        $data = $this->afGet('/predictions', ['fixture' => $fid]);
        if (!$data || empty($data['response'])) return null;
        $pct = $data['response'][0]['predictions']['percent'] ?? [];
        $ho  = $this->pctToOdds($pct['home'] ?? '0%');
        $do  = $this->pctToOdds($pct['draw'] ?? '0%');
        $ao  = $this->pctToOdds($pct['away'] ?? '0%');
        if (!$ho || !$ao) return null;
        $derived = $this->deriveMarkets($ho, $do ?? 3.5, $ao);
        return array_merge(['1x2' => ['home' => $ho, 'draw' => $do, 'away' => $ao]], $derived);
    }

    // ── Prediction logic ──────────────────────────────────────────────────────

    private function deriveMarkets(float $h, float $d, float $a): array
    {
        $ph = 1/$h; $pd = 1/$d; $pa = 1/$a;
        $bal = 1.0 - abs($ph - $pa);
        $p25 = max(0.35, min(0.70, 0.45 + $bal*0.12 - $pd*0.40));
        $p05 = min(0.97, $p25 + 0.40); $p15 = min(0.88, $p25 + 0.22); $p35 = max(0.08, $p25 - 0.22);
        $pBt = max(0.30, min(0.65, 0.42 + $bal*0.18 - $pd*0.25));
        $ou  = fn($p) => ['over' => round(1/$p*0.92,2), 'under' => round(1/(1-$p)*0.92,2)];
        return [
            'over_under' => $ou($p25), 'over_under_05' => $ou($p05),
            'over_under_15' => $ou($p15), 'over_under_35' => $ou($p35),
            'btts' => ['yes' => round(1/$pBt*0.92,2), 'no' => round(1/(1-$pBt)*0.92,2)],
        ];
    }

    private function pickPrediction(array $markets): array
    {
        $m=$markets['1x2']??[]; $ou25=$markets['over_under']??[]; $ou15=$markets['over_under_15']??[];
        $ou35=$markets['over_under_35']??[]; $bt=$markets['btts']??[];
        $h=$m['home']??99; $d=$m['draw']??99; $a=$m['away']??99; $gap=abs($h-$a);
        $o15=$ou15['over']??null; $u15=$ou15['under']??null;
        $o25=$ou25['over']??null;
        $o35=$ou35['over']??null; $u35=$ou35['under']??null;
        $dc=fn($x,$y)=>$x>0&&$y>0?round(1/(1/$x+1/$y)*0.95,2):99.0;

        if ($h<1.30)                               return ['Home Win',$h];
        if ($a<1.30)                               return ['Away Win',$a];
        if ($h>4.00)                               return ['Away Win',$a];
        if ($a>4.00)                               return ['Home Win',$h];
        if ($h>=1.30&&$h<=1.52&&$o15)             return ['Over 1.5',$o15];
        if ($a>=1.30&&$a<=1.52&&$o15)             return ['Over 1.5',$o15];
        if ($h>1.52&&$h<=1.80&&$o25)              return ['Over 2.5',$o25];
        if ($a>1.52&&$a<=1.80&&$o25)              return ['Over 2.5',$o25];
        if ($gap<=0.40&&$d<3.20)                   return ['Draw',$d];
        if ($gap<=0.30&&isset($bt['yes']))         return ['BTTS - Yes',$bt['yes']];
        if ($gap<=0.60&&$d<3.80)                   return ['Draw',$d];
        if ($gap<0.50&&$o35&&$o35<2.25)           return ['Over 3.5',$o35];
        $dc1x=$dc($h,$d); $dcx2=$dc($d,$a);
        if ($h>1.80&&$h<=2.10&&$dc1x>=1.25)      return ['Double Chance 1X',$dc1x];
        if ($a>1.80&&$a<=2.10&&$dcx2>=1.25)      return ['Double Chance X2',$dcx2];
        if ($d<3.20&&$u15&&$u15<2.00)             return ['Under 1.5',$u15];
        if ($h<=2.50&&$a<=2.50&&isset($bt['yes']))return ['BTTS - Yes',$bt['yes']];
        if ($h>2.30&&$a>2.30&&$u35&&$u35<1.60)   return ['Under 3.5',$u35];
        if ($h>2.30&&$a>2.30)                      return ['Double Chance 12',$dc($h,$a)];
        return $h<=$a ? ['Home Win',$h] : ['Away Win',$a];
    }

    private function calcConfidence(array $markets): int
    {
        $m = $markets['1x2'] ?? [];
        $odds = array_values(array_filter([$m['home']??null,$m['draw']??null,$m['away']??null]));
        sort($odds);
        if (count($odds) < 2) return 3;
        $gap = $odds[1] - $odds[0];
        if ($gap > 1.5) return 5; if ($gap > 1.0) return 4; if ($gap > 0.5) return 3;
        if ($gap > 0.2) return 2; return 1;
    }

    private function selectPremiumThree(array $rows): array
    {
        $candidates = [];
        foreach ($rows as $row) {
            [$pred, $odds] = $this->pickPrediction($row['markets']);
            if ($odds && $odds >= 1.10)
                $candidates[] = ['row'=>$row,'pred'=>$pred,'odds'=>$odds,'conf'=>$row['confidence']];
        }
        if (!$candidates) return [];
        usort($candidates, fn($a,$b)=>$b['conf']<=>$a['conf']?:$b['odds']<=>$a['odds']);
        $pool = array_slice($candidates, 0, 20);
        $best=null; $bestScore=-1;
        for ($i=0;$i<count($pool);$i++) for ($j=$i+1;$j<count($pool);$j++) for ($k=$j+1;$k<count($pool);$k++) {
            $trio=[$pool[$i],$pool[$j],$pool[$k]];
            $combined=$trio[0]['odds']*$trio[1]['odds']*$trio[2]['odds'];
            if ($combined<2.00) continue;
            $score=$trio[0]['conf']+$trio[1]['conf']+$trio[2]['conf'];
            if ($score>$bestScore||($score===$bestScore&&$best&&$combined>$best[1])){$bestScore=$score;$best=[$trio,$combined];}
        }
        if ($best) return array_map(fn($c)=>[$c['row'],$c['pred'],$c['odds']],$best[0]);
        return array_map(fn($c)=>[$c['row'],$c['pred'],$c['odds']],array_slice($candidates,0,3));
    }

    private function evaluateResult(int $gh, int $ga, string $pred): ?string
    {
        $t = $gh + $ga;
        return match ($pred) {
            'Home Win'         => $gh>$ga  ?'won':'lost',
            'Away Win'         => $ga>$gh  ?'won':'lost',
            'Draw'             => $gh===$ga?'won':'lost',
            'Double Chance 1X' => $gh>=$ga ?'won':'lost',
            'Double Chance X2' => $ga>=$gh ?'won':'lost',
            'Double Chance 12' => $gh!==$ga?'won':'lost',
            'Over 0.5'         => $t>0?'won':'lost',
            'Over 1.5'         => $t>1?'won':'lost',
            'Over 2.5'         => $t>2?'won':'lost',
            'Over 3.5'         => $t>3?'won':'lost',
            'Under 0.5'        => $t<1?'won':'lost',
            'Under 1.5'        => $t<2?'won':'lost',
            'Under 2.5'        => $t<3?'won':'lost',
            'Under 3.5'        => $t<4?'won':'lost',
            'BTTS - Yes'       => $gh>0&&$ga>0?'won':'lost',
            'BTTS - No'        => $gh===0||$ga===0?'won':'lost',
            default            => null,
        };
    }

    private function generateAnalysis(string $home, string $away, string $pred,
                                       array $markets, string $league, string $country, string $source): string
    {
        $m=$markets['1x2']??[]; $ou25=$markets['over_under']??[]; $ou15=$markets['over_under_15']??[];
        $ou35=$markets['over_under_35']??[]; $bt=$markets['btts']??[];
        $h=$m['home']??2.0; $d=$m['draw']??3.5; $a=$m['away']??2.0;
        $mg=1/$h+1/$d+1/$a; $ph=round(1/$h/$mg*100); $pd=round(1/$d/$mg*100); $pa=round(1/$a/$mg*100);
        $ctx=$league?" in the {$league}":($country?" in {$country}":'');
        $lines=["{$home} host {$away}{$ctx}."];
        if ($source==='predictions') $lines[]='Win probabilities are sourced from AI-powered match analysis.';
        if ($h<$a-0.05) {
            $s=$ph>=60?'strong':'moderate';
            $lines[]="The market makes {$home} the {$s} favourite at {$h} ({$ph}% implied) — {$away} are priced at {$a} ({$pa}%), with the draw at {$d} ({$pd}%).";
        } elseif ($a<$h-0.05) {
            $s=$pa>=60?'strong':'moderate';
            $lines[]="The market makes {$away} the {$s} favourite at {$a} ({$pa}% implied) — {$home} are priced at {$h} ({$ph}%), with the draw at {$d} ({$pd}%).";
        } else {
            $lines[]="The bookmakers price this as an open contest — {$home} {$ph}% ({$h}), draw {$pd}% ({$d}), {$away} {$pa}% ({$a}).";
        }
        $o25=$ou25['over']??null; $u25=$ou25['under']??null;
        if ($o25&&$u25){$gm=1/$o25+1/$u25;$pv=round(1/$o25/$gm*100);
            if ($pred==='Over 2.5') $lines[]="Over 2.5 is priced at {$o25} ({$pv}% implied), signalling an open, goal-filled match.";
            elseif ($pv>=58) $lines[]="The goals market leans towards a high-scoring affair (O2.5 at {$o25}, {$pv}% implied).";
        }
        $o15=$ou15['over']??null; $u15=$ou15['under']??null;
        $o35=$ou35['over']??null; $u35=$ou35['under']??null;
        $btY=$bt['yes']??null; $btN=$bt['no']??null;
        $lines[]=match($pred){
            'Home Win'         =>"Our selection is {$home} to win — home advantage and market pricing converge on this outcome.",
            'Away Win'         =>"We back {$away} to take all three points — form and market support a confident away selection.",
            'Draw'             =>"With both sides closely matched and draw odds at {$d}, a share of the spoils is the clearest value.",
            'Double Chance 1X' =>"The Double Chance 1X ({$home} or draw) captures the most likely outcomes while protecting against a surprise away win.",
            'Double Chance X2' =>"The Double Chance X2 ({$away} or draw) retains strong value while hedging the full away risk.",
            'Double Chance 12' =>"Draw odds of {$d} suggest a decisive result — Double Chance 12 covers either winner and eliminates the draw risk.",
            'Over 1.5'         =>"With two or more goals firmly expected, Over 1.5 at ".($o15??"current odds")." is a solid, well-supported pick.",
            'Over 2.5'         =>"Both attacks carry a genuine threat — Over 2.5 goals at ".($o25??"current odds")." is the standout pick.",
            'Over 3.5'         =>"The attacking quality on show points to a high-scoring game — Over 3.5 at ".($o35??"current odds")." represents great value.",
            'Under 1.5'        =>"Defensive solidity and a low-scoring market signal back Under 1.5 goals at ".($u15??"current odds").".",
            'Under 2.5'        =>"Tight defensive structures and the goals market point to Under 2.5 as the safest route.",
            'Under 3.5'        =>"While goals are likely, a cricket score is not — Under 3.5 at ".($u35??"current odds")." is the controlled pick.",
            'BTTS - Yes'       =>"With both teams in regular scoring form, Both Teams to Score at ".($btY??"current odds")." is well-supported.",
            'BTTS - No'        =>"Defensive solidity from at least one side — expect a clean sheet and back BTTS No at ".($btN??"current odds").".",
            default            =>"Our analysts back {$pred} based on current market indicators and statistical trends.",
        };
        return implode(' ',$lines);
    }

    private function tipData(string $sport, array $row, string $pred, float $odds, int $conf, int $vip, string $reasoning): array
    {
        return [
            'sport'      => $sport,
            'home_team'  => $row['home'],  'away_team'  => $row['away'],
            'league'     => $row['league'], 'country'    => $row['country'],
            'prediction' => $pred,          'confidence' => $conf,
            'odds'       => $odds,          'home_odds'  => $row['home_odds'],
            'draw_odds'  => $row['draw_odds'] ?? null, 'away_odds' => $row['away_odds'],
            'match_time' => $row['match_time'], 'status' => 'pending',
            'is_premium' => $vip,           'analyst'    => 'OddsAPI',
            'reasoning'  => $reasoning,     'home_form'  => $row['home_form'] ?? null, 'away_form' => $row['away_form'] ?? null,
            'created_at' => now(),          'updated_at' => now(),
        ];
    }

    // ── Fetch fixtures directly from The Odds API ─────────────────────────────

    private function fetchFromOddsApi(): void
    {
        if (!$this->oddsKey) {
            $this->warn('  No ODDS_API_KEY in .env — cannot use this mode');
            return;
        }

        $tzOffset = (int) env('TZ_OFFSET_HOURS', 3);
        $inserted = $skipped = 0;
        $vipCandidates = [];

        // Collect all unique odds_keys from SF_TOURNAMENTS
        $leagues = [];
        foreach (self::SF_TOURNAMENTS as $meta) {
            $key = $meta['odds_key'] ?? null;
            if ($key && !isset($leagues[$key])) {
                $leagues[$key] = $meta;
            }
        }

        foreach ($leagues as $oddsKey => $meta) {
            $resp = Http::withoutVerifying()->timeout(15)->get(
                'https://api.the-odds-api.com/v4/sports/' . $oddsKey . '/odds/',
                [
                    'apiKey'      => $this->oddsKey,
                    'regions'     => 'eu',
                    'markets'     => 'h2h,totals',
                    'oddsFormat'  => 'decimal',
                    'dateFormat'  => 'iso',
                ]
            );

            $remaining = $resp->header('x-requests-remaining') ?? '?';

            if ($resp->status() === 422) continue; // off-season / not available
            if (!$resp->successful()) {
                $this->warn("  [OddsAPI] HTTP {$resp->status()} for {$oddsKey}");
                continue;
            }

            $events = $resp->json() ?? [];
            if (empty($events)) { $this->line("  [OddsAPI] {$meta['name']}: 0 events"); continue; }

            $this->line("  [OddsAPI] {$meta['name']}: " . count($events) . " events (quota left: {$remaining})");

            foreach ($events as $ev) {
                $home = $ev['home_team'] ?? '';
                $away = $ev['away_team'] ?? '';
                if (!$home || !$away) continue;

                // Parse and localise match time
                try {
                    $dt = new \DateTime($ev['commence_time']);
                    $dt->modify("+{$tzOffset} hours");
                    $matchTime = $dt->format('Y-m-d H:i:s');
                } catch (\Exception $e) { continue; }

                // Only accept matches in the next 7 days
                $matchTs = strtotime($matchTime);
                if ($matchTs < time() || $matchTs > time() + 7 * 86400) continue;

                // Duplicate check — VIP tips are selected separately after the loop
                $freeExists = DB::table('betting_tips')
                    ->where('home_team', $home)->where('away_team', $away)
                    ->where('match_time', $matchTime)->where('is_premium', 0)->exists();

                // Collect h2h + all O/U lines across bookmakers
                $hVals = $dVals = $aVals = [];
                $o15Vals = $u15Vals = $o25Vals = $u25Vals = $o35Vals = $u35Vals = [];
                foreach ($ev['bookmakers'] ?? [] as $bm) {
                    foreach ($bm['markets'] ?? [] as $mkt) {
                        if ($mkt['key'] === 'h2h') {
                            $byName = collect($mkt['outcomes'] ?? [])->pluck('price', 'name')->all();
                            if ($byName[$home] ?? null) $hVals[] = $byName[$home];
                            if ($byName[$away] ?? null) $aVals[] = $byName[$away];
                            if ($byName['Draw']  ?? null) $dVals[] = $byName['Draw'];
                        } elseif ($mkt['key'] === 'totals') {
                            foreach ($mkt['outcomes'] ?? [] as $o) {
                                $pt = (float)($o['point'] ?? 0);
                                $pr = (float)($o['price'] ?? 0);
                                $nm = $o['name'] ?? '';
                                if (!$pr || !$nm) continue;
                                if ($pt == 1.5) { $nm === 'Over' ? ($o15Vals[] = $pr) : ($u15Vals[] = $pr); }
                                if ($pt == 2.5) { $nm === 'Over' ? ($o25Vals[] = $pr) : ($u25Vals[] = $pr); }
                                if ($pt == 3.5) { $nm === 'Over' ? ($o35Vals[] = $pr) : ($u35Vals[] = $pr); }
                            }
                        }
                    }
                }

                if (empty($hVals) || empty($aVals)) { $skipped++; continue; }

                $avg = fn($a) => $a ? round(array_sum($a) / count($a), 2) : null;
                $hOdds   = $avg($hVals);
                $aOdds   = $avg($aVals);
                $dOdds   = $avg($dVals);
                $actO15  = $avg($o15Vals); $actU15 = $avg($u15Vals);
                $actO25  = $avg($o25Vals); $actU25 = $avg($u25Vals);
                $actO35  = $avg($o35Vals); $actU35 = $avg($u35Vals);

                $bookCount = count($ev['bookmakers'] ?? []);
                $dOddsCalc = $dOdds ?? 3.5;
                $markets   = $this->buildMarketsFromOdds($hOdds, $dOddsCalc, $aOdds, $actO25, $actU25);

                // Override with actual API data where we have it
                if ($actO15 && $actU15) $markets['over_under_15'] = ['over' => $actO15, 'under' => $actU15];
                if ($actO35 && $actU35) $markets['over_under_35'] = ['over' => $actO35, 'under' => $actU35];

                // True implied probability (3-way, margin-removed)
                $margin3 = 1/$hOdds + 1/$dOddsCalc + 1/$aOdds;
                $ph3     = round((1/$hOdds / $margin3) * 100, 1);
                $pd3     = round((1/$dOddsCalc / $margin3) * 100, 1);
                $pa3     = round((1/$aOdds / $margin3) * 100, 1);

                // Always pick lowest-odds goals market outcome
                $goalsOpts = [];
                foreach ([
                    'Over 1.5'   => $markets['over_under_15']['over']  ?? null,
                    'Under 1.5'  => $markets['over_under_15']['under'] ?? null,
                    'Over 2.5'   => $markets['over_under']['over']     ?? null,
                    'Under 2.5'  => $markets['over_under']['under']    ?? null,
                    'Over 3.5'   => $markets['over_under_35']['over']  ?? null,
                    'Under 3.5'  => $markets['over_under_35']['under'] ?? null,
                    'BTTS - Yes' => $markets['btts']['yes']            ?? null,
                    'BTTS - No'  => $markets['btts']['no']             ?? null,
                ] as $goalsLabel => $goalsOdds) {
                    if ($goalsOdds && $goalsOdds > 1.0) $goalsOpts[$goalsLabel] = $goalsOdds;
                }
                asort($goalsOpts);
                $pred     = array_key_first($goalsOpts) ?? 'Over 1.5';
                $predOdds = $goalsOpts[$pred] ?? ($markets['over_under_15']['over'] ?? 1.5);

                // If selected odds are below 1.20 (too short), fall back to BTTS - No
                if ($predOdds < 1.20 && ($markets['btts']['no'] ?? null)) {
                    $pred     = 'BTTS - No';
                    $predOdds = $markets['btts']['no'];
                }

                // If BTTS - No odds exceed 1.75, use BTTS - Yes instead
                if ($pred === 'BTTS - No' && $predOdds > 1.75 && ($markets['btts']['yes'] ?? null)) {
                    $pred     = 'BTTS - Yes';
                    $predOdds = $markets['btts']['yes'];
                }

                // Margin-removed implied probability for the selected 2-way goals market
                $compOdds = match(true) {
                    $pred === 'Over 1.5'   => $markets['over_under_15']['under'] ?? null,
                    $pred === 'Under 1.5'  => $markets['over_under_15']['over']  ?? null,
                    $pred === 'Over 2.5'   => $markets['over_under']['under']    ?? null,
                    $pred === 'Under 2.5'  => $markets['over_under']['over']     ?? null,
                    $pred === 'Over 3.5'   => $markets['over_under_35']['under'] ?? null,
                    $pred === 'Under 3.5'  => $markets['over_under_35']['over']  ?? null,
                    $pred === 'BTTS - Yes' => $markets['btts']['no']             ?? null,
                    $pred === 'BTTS - No'  => $markets['btts']['yes']            ?? null,
                    default                => null,
                };
                $winProb  = $compOdds
                    ? round(1/$predOdds / (1/$predOdds + 1/$compOdds) * 100, 1)
                    : round(min(99, 1/$predOdds * 100), 1);
                $predLabel = $pred;

                $conf = $this->oddsConfidence($winProb);
                if ($pred === 'BTTS - Yes') $conf = 4;
                if ($pred === 'BTTS - No')  $conf = 3;

                // Resolved odds for display (actual API data preferred over estimates)
                $o15d = $actO15 ?? $markets['over_under_15']['over']  ?? null;
                $u15d = $actU15 ?? $markets['over_under_15']['under'] ?? null;
                $o25d = $actO25 ?? $markets['over_under']['over']     ?? null;
                $u25d = $actU25 ?? $markets['over_under']['under']    ?? null;
                $o35d = $actO35 ?? $markets['over_under_35']['over']  ?? null;
                $u35d = $actU35 ?? $markets['over_under_35']['under'] ?? null;
                $bttsY = $markets['btts']['yes'] ?? null;
                $bttsN = $markets['btts']['no']  ?? null;

                // Implied O/U 2.5 probability
                $pO25str = '';
                if ($o25d && $u25d) {
                    $gm25  = 1/$o25d + 1/$u25d;
                    $pO25  = round(1/$o25d / $gm25 * 100);
                    $pO25str = " ({$pO25}%)";
                }

                // Collect VIP BTTS Yes candidate data (before possible skip)
                if ($bttsY && $bttsN) {
                    $vipGm   = 1/$bttsY + 1/$bttsN;
                    $vipProb = round(1/$bttsY / $vipGm * 100, 1);
                    $vipCandidates[] = [
                        'home'       => $home,          'away'       => $away,
                        'league'     => $meta['name'],  'country'    => $meta['country'],
                        'home_odds'  => $hOdds,         'draw_odds'  => $dOdds,
                        'away_odds'  => $aOdds,         'match_time' => $matchTime,
                        'home_form'  => null,            'away_form'  => null,
                        'bttsY'      => $bttsY,          'bttsN'      => $bttsN,
                        'bttsProb'   => $vipProb,        'ph3'        => $ph3,
                        'pd3'        => $pd3,            'pa3'        => $pa3,
                        'bookCount'  => $bookCount,      'dOddsCalc'  => $dOddsCalc,
                        'o15d'       => $o15d,           'u15d'       => $u15d,
                        'o25d'       => $o25d,           'u25d'       => $u25d,
                        'o35d'       => $o35d,           'u35d'       => $u35d,
                        'actO15'     => $actO15,         'actO25'     => $actO25,  'actO35' => $actO35,
                    ];
                }

                // Skip free tip insert if already exists, but continue to collect VIP data above
                if ($freeExists) { $skipped++; continue; }

                // Build comprehensive reasoning
                $r  = "{$home} host {$away} in the {$meta['name']} ({$meta['country']}).";
                $r .= " Odds aggregated across {$bookCount} bookmaker" . ($bookCount !== 1 ? 's' : '') . ".\n\n";

                $r .= "MATCH RESULT\n";
                $r .= "{$home}: {$hOdds} ({$ph3}%)  |  Draw: {$dOddsCalc} ({$pd3}%)  |  {$away}: {$aOdds} ({$pa3}%)\n\n";

                $r .= "GOALS MARKETS\n";
                if ($o15d && $u15d) $r .= "Over/Under 1.5:  O {$o15d}  /  U {$u15d}" . (!$actO15 ? "  (est.)" : "") . "\n";
                if ($o25d && $u25d) $r .= "Over/Under 2.5:  O {$o25d}{$pO25str}  /  U {$u25d}" . (!$actO25 ? "  (est.)" : "") . "\n";
                if ($o35d && $u35d) $r .= "Over/Under 3.5:  O {$o35d}  /  U {$u35d}" . (!$actO35 ? "  (est.)" : "") . "\n";
                if ($bttsY && $bttsN) $r .= "Both Teams Score:  Yes {$bttsY}  /  No {$bttsN}  (est.)\n";
                $r .= "\n";

                // Multi-paragraph market narrative
                $h3gap    = abs($ph3 - $pa3);
                $favSide3 = $ph3 >= $pa3 ? $home : $away;
                $favProb3 = max($ph3, $pa3);
                $undSide3 = $ph3 >= $pa3 ? $away : $home;
                $undProb3 = min($ph3, $pa3);
                $homeAdv  = $ph3 >= $pa3
                    ? "Playing at home, {$home} carry an additional psychological and crowd advantage."
                    : "{$away} will need to defend well on the road to take anything from this game.";

                $r .= "MARKET ANALYSIS\n";
                if ($h3gap >= 14) {
                    $r .= "{$favSide3} enter as commanding favourites — bookmakers assign them {$favProb3}% implied win probability, with {$undSide3} trailing at just {$undProb3}%. Such a margin ({$h3gap}%) reflects strong market conviction that this match has a likely winner.\n";
                    $r .= "{$homeAdv}";
                    if ($pd3 >= 26) $r .= " The draw ({$pd3}%) is a risk factor, but the market clearly leans towards a decisive result.";
                    $r .= "\n";
                } elseif ($h3gap >= 7) {
                    $r .= "{$favSide3} hold a meaningful market edge ({$favProb3}% vs {$undProb3}%) — a gap that separates a moderate favourite from a genuine contest.\n";
                    $r .= "{$homeAdv}";
                    if ($pd3 >= 28) $r .= " With the draw sitting at {$pd3}%, bettors must account for a stalemate as a realistic third outcome.";
                    $r .= "\n";
                } else {
                    $r .= "A tightly-balanced fixture — {$home} ({$ph3}%) and {$away} ({$pa3}%) are almost evenly priced, separated by just {$h3gap}% in implied probability. The market genuinely does not know who wins this game.\n";
                    $r .= "The draw ({$pd3}%) is firmly in play and may represent the strongest value given the evenly-matched odds.\n";
                }

                if (isset($pO25)) {
                    $pU25 = 100 - $pO25;
                    $r .= "\n";
                    if ($pO25 >= 62) {
                        $r .= "Goals market: Over 2.5 is rated at {$pO25}% implied — both attacks are expected to contribute, pointing to an open, goal-filled match. The O/U 1.5 and O/U 3.5 lines further reinforce this attacking profile.";
                    } elseif ($pO25 <= 38) {
                        $r .= "Goals market: The market leans defensive — Under 2.5 is preferred at {$pU25}% implied. Expect compact play and limited clear chances; clean sheets are a real possibility for at least one side.";
                    } else {
                        $r .= "Goals market: Balanced at {$pO25}% (Over) vs {$pU25}% (Under) for the 2.5 line — neither an all-out attacking game nor a defensive slugfest is expected. Goals could go either way.";
                    }
                    $r .= "\n";
                }

                // BTTS note
                if ($bttsY && $bttsN) {
                    $gmBtts = 1/$bttsY + 1/$bttsN;
                    $pBtts  = round(1/$bttsY / $gmBtts * 100);
                    $r .= "Both Teams to Score is priced at {$pBtts}% implied (Yes {$bttsY} / No {$bttsN} est.) — ";
                    $r .= $pBtts >= 55 ? "scoring from both sides is the more likely outcome.\n" : "a clean sheet from one side is plausible.\n";
                }

                // Prediction reasoning
                $r .= "\nOUR PICK: {$pred} @ {$predOdds}\n";
                $predJustify = match(true) {
                    str_starts_with($pred, 'Home')   => "At {$predOdds}, {$home} offer fair value relative to their {$ph3}% implied probability. The market consensus across {$bookCount} bookmakers supports a home victory.",
                    str_starts_with($pred, 'Away')   => "At {$predOdds}, {$away} represent value as the market-backed away side ({$pa3}% implied). The {$bookCount}-bookmaker consensus points to an away win.",
                    $pred === 'Draw'                 => "With both sides closely matched and draw odds at {$dOddsCalc} ({$pd3}% implied), a share of the spoils is the clearest value from {$bookCount} bookmakers' consensus.",
                    str_starts_with($pred, 'Over')   => "The attacking profile of this match supports {$pred} at {$predOdds} ({$winProb}% implied) — the goals market and probability data align on a higher-scoring outcome.",
                    str_starts_with($pred, 'Under')  => "{$pred} at {$predOdds} ({$winProb}% implied) is backed by the defensive lean of both the market and goals-line data across {$bookCount} bookmakers.",
                    $pred === 'BTTS - Yes'           => "With both sides carrying attacking threat, Both Teams to Score at {$predOdds} ({$winProb}% implied) is the market's preferred outcome for this fixture across {$bookCount} bookmakers.",
                    $pred === 'BTTS - No'            => "Defensive solidity from at least one side is expected — BTTS No at {$predOdds} ({$winProb}% implied) is backed by the market structure across {$bookCount} bookmakers.",
                    str_starts_with($pred, 'Double') => "{$pred} at {$predOdds} captures the two most likely outcomes and offers a safety net against the market's third scenario.",
                    default                          => "Our pick is {$pred} at {$predOdds}, selected from the {$bookCount}-bookmaker consensus based on market-implied probabilities.",
                };
                $r .= "{$predJustify}\n";
                $r .= $this->oddsStrengthPhrase($winProb, $predLabel);

                $reasoning = $r;

                DB::table('betting_tips')->insert($this->tipData('Football', [
                    'home'       => $home,       'away'       => $away,
                    'league'     => $meta['name'], 'country'   => $meta['country'],
                    'home_odds'  => $hOdds,      'draw_odds'  => $dOdds,
                    'away_odds'  => $aOdds,      'match_time' => $matchTime,
                    'home_form'  => null,         'away_form'  => null,
                ], $pred, (float) $predOdds, $conf, 0, $reasoning));

                $inserted++;
                $this->line("    [NEW] {$home} vs {$away} → {$pred} @ {$predOdds}");
            }

            sleep(1);
        }

        // ── Pick 2 best BTTS Yes VIP tips from this scrape run ───────────────────
        if (!empty($vipCandidates)) {
            usort($vipCandidates, fn($a, $b) => $b['bttsProb'] <=> $a['bttsProb']);
            $vipInserted = 0;
            foreach ($vipCandidates as $vc) {
                if ($vipInserted >= 2) break;

                if (DB::table('betting_tips')
                    ->where('home_team', $vc['home'])->where('away_team', $vc['away'])
                    ->where('match_time', $vc['match_time'])->where('is_premium', 1)->exists()) continue;

                $vH = $vc['home']; $vA = $vc['away'];
                $vPh3 = $vc['ph3']; $vPd3 = $vc['pd3']; $vPa3 = $vc['pa3'];
                $vD   = $vc['dOddsCalc']; $vBY = $vc['bttsY']; $vBN = $vc['bttsN']; $vBP = $vc['bttsProb'];

                $vPO25str = '';
                if ($vc['o25d'] && $vc['u25d']) {
                    $gm25v    = 1/$vc['o25d'] + 1/$vc['u25d'];
                    $vPO25    = round(1/$vc['o25d'] / $gm25v * 100);
                    $vPO25str = " ({$vPO25}%)";
                }

                $vGap = abs($vPh3 - $vPa3);
                $vFav = $vPh3 >= $vPa3 ? $vH : $vA;
                $vFavP = max($vPh3, $vPa3);
                $vUnd = $vPh3 >= $vPa3 ? $vA : $vH;
                $vUndP = min($vPh3, $vPa3);
                $vHomeAdv = $vPh3 >= $vPa3
                    ? "Playing at home, {$vH} carry an additional psychological and crowd advantage."
                    : "{$vA} will need to defend well on the road to take anything from this game.";

                if ($vGap >= 14) {
                    $vNar1 = "{$vFav} enter as commanding favourites ({$vFavP}% implied) with {$vUnd} trailing at {$vUndP}%. Despite the result market lean, both sides are expected to contribute goals.";
                } elseif ($vGap >= 7) {
                    $vNar1 = "{$vFav} hold a meaningful edge ({$vFavP}% vs {$vUndP}%) but the goals angle remains strong — the underdog carries enough attacking threat for BTTS.";
                } else {
                    $vNar1 = "A tightly-balanced contest — {$vH} ({$vPh3}%) vs {$vA} ({$vPa3}%) — both sides have clear attacking capability and are expected to score.";
                }

                $vR  = "{$vH} host {$vA} in the {$vc['league']} ({$vc['country']}).\n\n";
                $vR .= "MATCH RESULT\n";
                $vR .= "{$vH}: {$vc['home_odds']} ({$vPh3}%)  |  Draw: {$vD} ({$vPd3}%)  |  {$vA}: {$vc['away_odds']} ({$vPa3}%)\n\n";
                $vR .= "GOALS MARKETS\n";
                if ($vc['o15d'] && $vc['u15d']) $vR .= "Over/Under 1.5:  O {$vc['o15d']}  /  U {$vc['u15d']}" . (!$vc['actO15'] ? "  (est.)" : "") . "\n";
                if ($vc['o25d'] && $vc['u25d']) $vR .= "Over/Under 2.5:  O {$vc['o25d']}{$vPO25str}  /  U {$vc['u25d']}" . (!$vc['actO25'] ? "  (est.)" : "") . "\n";
                if ($vc['o35d'] && $vc['u35d']) $vR .= "Over/Under 3.5:  O {$vc['o35d']}  /  U {$vc['u35d']}" . (!$vc['actO35'] ? "  (est.)" : "") . "\n";
                $vR .= "Both Teams Score:  Yes {$vBY} ({$vBP}%)  /  No {$vBN}  (est.)\n\n";
                $vR .= "MARKET ANALYSIS\n";
                $vR .= "{$vNar1}\n";
                $vR .= "{$vHomeAdv}\n";
                $vR .= "\nBoth Teams to Score is priced at {$vBP}% implied — the {$vc['bookCount']}-bookmaker consensus places a meaningful probability on both sides finding the net. This is our VIP selection based on attacking profiles and market structure.\n\n";
                $vR .= "OUR PICK: BTTS - Yes @ {$vBY}\n";
                $vR .= "With both sides carrying genuine attacking threat, Both Teams to Score at {$vBY} ({$vBP}% implied) is our exclusive VIP selection for this fixture.\n";
                $vR .= $this->oddsStrengthPhrase($vBP, 'BTTS - Yes');

                DB::table('betting_tips')->insert($this->tipData('Football', [
                    'home'      => $vH,              'away'      => $vA,
                    'league'    => $vc['league'],    'country'   => $vc['country'],
                    'home_odds' => $vc['home_odds'], 'draw_odds' => $vc['draw_odds'],
                    'away_odds' => $vc['away_odds'], 'match_time'=> $vc['match_time'],
                    'home_form' => null,              'away_form' => null,
                ], 'BTTS - Yes', (float)$vBY, 4, 1, $vR));

                $vipInserted++;
                $this->line("    [VIP] {$vH} vs {$vA} → BTTS - Yes @ {$vBY} ({$vBP}%)");
            }
            $this->line("  VIP tips inserted: {$vipInserted}");
        }

        $this->line("\n  OddsAPI done — inserted: {$inserted} | skipped: {$skipped}");
    }

    private function buildMarketsFromOdds(float $h, float $d, float $a, ?float $over25, ?float $under25): array
    {
        $ph = 1/$h; $pd = 1/$d; $pa = 1/$a;
        $balance = 1.0 - abs($ph - $pa);
        $p25  = max(0.35, min(0.70, 0.45 + $balance * 0.12 - $pd * 0.40));
        $p05  = min(0.97, $p25 + 0.40);
        $p15  = min(0.88, $p25 + 0.22);
        $p35  = max(0.08, $p25 - 0.22);
        $pBtts = max(0.30, min(0.65, 0.42 + $balance * 0.18 - $pd * 0.25));
        $ou = fn($p) => ['over' => round(1/$p*0.92, 2), 'under' => round(1/(1-$p)*0.92, 2)];
        return [
            '1x2'          => ['home' => $h, 'draw' => $d, 'away' => $a],
            'over_under'   => $over25 && $under25 ? ['over' => $over25, 'under' => $under25] : $ou($p25),
            'over_under_05'=> $ou($p05),
            'over_under_15'=> $ou($p15),
            'over_under_35'=> $ou($p35),
            'btts'         => ['yes' => round(1/$pBtts*0.92, 2), 'no' => round(1/(1-$pBtts)*0.92, 2)],
        ];
    }

    private function pctToOdds(string $pct): ?float
    {
        $p = floatval(str_replace('%', '', $pct)) / 100;
        return $p > 0.01 ? round(1 / $p * 0.90, 2) : null;
    }
}
