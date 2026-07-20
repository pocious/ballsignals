@php
/** @var \App\Models\BettingTip|null $bettingTip */
$bettingTip ??= null;
@endphp

{{-- Row 1: Home Team / Away Team --}}
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Home Team <span class="text-red-500">*</span></label>
        <input type="text" name="home_team" required list="teams-list"
               value="{{ old('home_team', $bettingTip->home_team ?? '') }}"
               placeholder="e.g. Brighton"
               autocomplete="off"
               class="w-full px-3 py-2.5 rounded-lg border @error('home_team') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        @error('home_team')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Away Team <span class="text-red-500">*</span></label>
        <input type="text" name="away_team" required list="teams-list"
               value="{{ old('away_team', $bettingTip->away_team ?? '') }}"
               placeholder="e.g. Chelsea"
               autocomplete="off"
               class="w-full px-3 py-2.5 rounded-lg border @error('away_team') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        @error('away_team')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

{{-- Row 2: League --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">League</label>
    <input type="text" name="league" list="leagues-list"
           value="{{ old('league', $bettingTip->league ?? '') }}"
           placeholder="e.g. Premier League"
           autocomplete="off"
           class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
    @error('league')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>

{{-- Row 3: Prediction + Confidence + Odds + Match Time --}}
<div class="grid grid-cols-4 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Prediction <span class="text-red-500">*</span></label>
        @php
            $predictions = [
                'Match Result'   => ['Home Win', 'Draw', 'Away Win'],
                'Goals'          => ['Over 0.5', 'Over 1.5', 'Over 2.5', 'Over 3.5', 'Under 0.5', 'Under 1.5', 'Under 2.5', 'Under 3.5'],
                'Both Teams'     => ['BTTS - Yes', 'BTTS - No'],
                'Double Chance'  => ['Double Chance 1X', 'Double Chance X2', 'Double Chance 12'],
                'Half Time'      => ['HT Home Win', 'HT Draw', 'HT Away Win'],
                'Cards / Corners'=> ['Over 3.5 Corners', 'Over 9.5 Corners', 'Over 1.5 Cards'],
            ];
            $allOptions = collect($predictions)->flatten()->toArray();
            $current    = old('prediction', $bettingTip->prediction ?? '');
            $isCustom   = $current !== '' && !in_array($current, $allOptions);
        @endphp
        <input type="hidden" id="prediction-value" name="prediction" value="{{ $current }}" required>
        <select id="prediction-select"
                class="w-full px-3 py-2.5 rounded-lg border @error('prediction') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">— Select —</option>
            @foreach($predictions as $group => $options)
                <optgroup label="{{ $group }}">
                    @foreach($options as $opt)
                        <option value="{{ $opt }}" {{ (!$isCustom && $current === $opt) ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </optgroup>
            @endforeach
            <option value="__custom__" {{ $isCustom ? 'selected' : '' }}>✏️ Custom (type manually)</option>
        </select>
        <input type="text" id="custom-prediction-input"
               placeholder="e.g. Asian Handicap -0.5, Win to Nil, etc."
               value="{{ $isCustom ? $current : '' }}"
               class="w-full mt-2 px-3 py-2.5 rounded-lg border border-green-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 {{ $isCustom ? '' : 'hidden' }}">
        @error('prediction')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        <script>
        (function(){
            const sel    = document.getElementById('prediction-select');
            const custom = document.getElementById('custom-prediction-input');
            const hidden = document.getElementById('prediction-value');
            sel.addEventListener('change', function(){
                if (this.value === '__custom__') {
                    custom.classList.remove('hidden');
                    custom.focus();
                    hidden.value = custom.value;
                } else {
                    custom.classList.add('hidden');
                    hidden.value = this.value;
                }
            });
            custom.addEventListener('input', function(){
                hidden.value = this.value;
            });
        })();
        </script>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confidence (1–5)</label>
        <select id="confidence-select" name="confidence"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">— None —</option>
            @foreach([1 => '★ Low', 2 => '★★', 3 => '★★★ Medium', 4 => '★★★★', 5 => '★★★★★ High'] as $val => $label)
                <option value="{{ $val }}" {{ old('confidence', $bettingTip->confidence ?? '') == $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('confidence')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        <p id="conf-hint" class="text-[11px] text-blue-500 mt-1 hidden">Confidence auto-set — adjust if needed.</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Odds</label>
        <input type="number" id="odds-input" name="odds" step="0.01" min="1"
               value="{{ old('odds', $bettingTip->odds ?? '') }}"
               placeholder="e.g. 1.85"
               class="w-full px-3 py-2.5 rounded-lg border @error('odds') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        @error('odds')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        <p id="odds-hint" class="text-[11px] text-blue-500 mt-1 hidden">Default odds set — adjust if needed.</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Match Time <span class="text-red-500">*</span></label>
        <input type="datetime-local" name="match_time" required
               value="{{ old('match_time', isset($bettingTip) ? $bettingTip->match_time->format('Y-m-d\TH:i') : '') }}"
               class="w-full px-3 py-2.5 rounded-lg border @error('match_time') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        @error('match_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>

{{-- Analysis / Reasoning --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">
        Analysis / Reasoning
        <span class="ml-1 text-xs font-normal text-gray-400">(leave blank to auto-generate)</span>
    </label>
    <textarea name="reasoning" rows="5" maxlength="3000"
              placeholder="Write your own analysis here, or leave blank and the system will generate it automatically based on the tip..."
              class="w-full px-3 py-2.5 rounded-lg border @error('reasoning') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none">{{ old('reasoning', $bettingTip->reasoning ?? '') }}</textarea>
    @error('reasoning')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    @if(isset($bettingTip) && $bettingTip->reasoning)
        <p class="text-xs text-gray-400 mt-1">
            @if($bettingTip->analyst)
                Currently showing analysis by <strong>{{ $bettingTip->analyst }}</strong>.
            @endif
            Clear the field and save to reset to auto-generated analysis.
        </p>
    @endif
</div>

{{-- Row 5: Status + Premium --}}
<div class="grid grid-cols-2 gap-4">
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
    <select name="status" required
            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500">
        @foreach(['pending' => 'Pending', 'won' => 'Won', 'lost' => 'Lost'] as $value => $label)
            <option value="{{ $value }}" {{ old('status', $bettingTip->status ?? 'pending') === $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('status')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div class="flex flex-col justify-center">
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Premium / VIP Tip</label>
    <label class="flex items-center gap-2 cursor-pointer">
        @php
            $isPremiumChecked = old('is_premium', $bettingTip->is_premium ?? request()->boolean('vip'));
        @endphp
        <input type="checkbox" name="is_premium" value="1"
               {{ $isPremiumChecked ? 'checked' : '' }}
               class="w-4 h-4 rounded border-gray-300 text-yellow-500 focus:ring-yellow-400">
        <span class="text-sm font-semibold {{ $isPremiumChecked ? 'text-yellow-600' : 'text-gray-600' }}">
            Mark as VIP / Premium
        </span>
    </label>
    @if(request()->boolean('vip'))
        <p class="text-[11px] text-yellow-600 mt-1 font-semibold">Auto-checked — this tip will be shown as a VIP pick.</p>
    @endif
</div>
</div>

{{-- ── Datalists ── --}}

<datalist id="leagues-list">
    {{-- England --}}
    <option value="Premier League">
    <option value="Championship">
    <option value="League One">
    <option value="League Two">
    {{-- Spain --}}
    <option value="La Liga">
    <option value="La Liga 2">
    {{-- Italy --}}
    <option value="Serie A">
    <option value="Serie B">
    {{-- France --}}
    <option value="Ligue 1">
    <option value="Ligue 2">
    {{-- Germany --}}
    <option value="Bundesliga">
    <option value="2. Bundesliga">
    {{-- Brazil --}}
    <option value="Serie A Betano">
    <option value="Serie B Brazil">
    {{-- Netherlands --}}
    <option value="Eredivisie">
    {{-- Portugal --}}
    <option value="Primeira Liga">
    {{-- Scotland --}}
    <option value="Scottish Premiership">
    {{-- Turkey --}}
    <option value="Super Lig">
    {{-- Belgium --}}
    <option value="Belgian Pro League">
    {{-- Argentina --}}
    <option value="Argentine Primera División">
    {{-- Mexico --}}
    <option value="Liga MX">
    {{-- USA --}}
    <option value="MLS">
    {{-- Saudi --}}
    <option value="Saudi Pro League">
    {{-- Greece --}}
    <option value="Super League Greece">
    {{-- Russia --}}
    <option value="Russian Premier League">
    {{-- Europe --}}
    <option value="Champions League">
    <option value="Europa League">
    <option value="Conference League">
    <option value="Nations League">
    <option value="World Cup Qualifiers">
    <option value="AFCON Qualifiers">

    {{-- DB-stored leagues (typed by admin and auto-saved) --}}
    @foreach($leagues ?? [] as $dbLeague)
        <option value="{{ $dbLeague }}">
    @endforeach
</datalist>

<datalist id="teams-list">
    {{-- Premier League --}}
    <option value="Arsenal">
    <option value="Aston Villa">
    <option value="AFC Bournemouth">
    <option value="Brentford">
    <option value="Brighton & Hove Albion">
    <option value="Chelsea">
    <option value="Crystal Palace">
    <option value="Everton">
    <option value="Fulham">
    <option value="Ipswich Town">
    <option value="Leicester City">
    <option value="Liverpool">
    <option value="Manchester City">
    <option value="Manchester United">
    <option value="Newcastle United">
    <option value="Nottingham Forest">
    <option value="Southampton">
    <option value="Tottenham Hotspur">
    <option value="West Ham United">
    <option value="Wolverhampton Wanderers">

    {{-- La Liga --}}
    <option value="Athletic Club">
    <option value="Atlético Madrid">
    <option value="Barcelona">
    <option value="Celta Vigo">
    <option value="Deportivo Alavés">
    <option value="Espanyol">
    <option value="Getafe">
    <option value="Girona">
    <option value="Las Palmas">
    <option value="Leganés">
    <option value="Mallorca">
    <option value="Osasuna">
    <option value="Rayo Vallecano">
    <option value="Real Betis">
    <option value="Real Madrid">
    <option value="Real Sociedad">
    <option value="Real Valladolid">
    <option value="Sevilla">
    <option value="Valencia">
    <option value="Villarreal">

    {{-- Serie A --}}
    <option value="AC Milan">
    <option value="Atalanta">
    <option value="Bologna">
    <option value="Cagliari">
    <option value="Como">
    <option value="Empoli">
    <option value="Fiorentina">
    <option value="Genoa">
    <option value="Hellas Verona">
    <option value="Inter Milan">
    <option value="Juventus">
    <option value="Lazio">
    <option value="Lecce">
    <option value="Monza">
    <option value="Napoli">
    <option value="Parma">
    <option value="Roma">
    <option value="Torino">
    <option value="Udinese">
    <option value="Venezia">

    {{-- Ligue 1 --}}
    <option value="Angers">
    <option value="Auxerre">
    <option value="Brest">
    <option value="Lens">
    <option value="Le Havre">
    <option value="Lille">
    <option value="Lyon">
    <option value="Marseille">
    <option value="Monaco">
    <option value="Montpellier">
    <option value="Nantes">
    <option value="Nice">
    <option value="Paris Saint-Germain">
    <option value="Reims">
    <option value="Rennes">
    <option value="Saint-Étienne">
    <option value="Strasbourg">
    <option value="Toulouse">

    {{-- Championship --}}
    <option value="Blackburn Rovers">
    <option value="Bristol City">
    <option value="Burnley">
    <option value="Cardiff City">
    <option value="Coventry City">
    <option value="Derby County">
    <option value="Hull City">
    <option value="Leeds United">
    <option value="Luton Town">
    <option value="Middlesbrough">
    <option value="Millwall">
    <option value="Norwich City">
    <option value="Oxford United">
    <option value="Plymouth Argyle">
    <option value="Portsmouth">
    <option value="Preston North End">
    <option value="Queens Park Rangers">
    <option value="Sheffield United">
    <option value="Sheffield Wednesday">
    <option value="Stoke City">
    <option value="Sunderland">
    <option value="Swansea City">
    <option value="Watford">
    <option value="West Bromwich Albion">

    {{-- Serie A Betano (Brazil) --}}
    <option value="Athletico Paranaense">
    <option value="Atlético Goianiense">
    <option value="Atlético Mineiro">
    <option value="Bahia">
    <option value="Botafogo">
    <option value="RB Bragantino">
    <option value="Corinthians">
    <option value="Criciúma">
    <option value="Cruzeiro">
    <option value="Cuiabá">
    <option value="Flamengo">
    <option value="Fluminense">
    <option value="Fortaleza">
    <option value="Grêmio">
    <option value="Internacional">
    <option value="Juventude">
    <option value="Mirassol">
    <option value="Palmeiras">
    <option value="São Paulo">
    <option value="Vasco da Gama">
    <option value="Vitória">
    <option value="Sport Recife">

    {{-- Bundesliga --}}
    <option value="Bayern Munich">
    <option value="Borussia Dortmund">
    <option value="Bayer Leverkusen">
    <option value="RB Leipzig">
    <option value="Eintracht Frankfurt">
    <option value="Stuttgart">
    <option value="Wolfsburg">
    <option value="Borussia Mönchengladbach">
    <option value="Freiburg">
    <option value="Hoffenheim">
    <option value="Werder Bremen">
    <option value="Mainz">
    <option value="Augsburg">
    <option value="Union Berlin">
    <option value="St. Pauli">
    <option value="Holstein Kiel">
    <option value="FC Heidenheim">
    <option value="Bochum">

    {{-- Eredivisie --}}
    <option value="Ajax">
    <option value="PSV Eindhoven">
    <option value="Feyenoord">
    <option value="AZ Alkmaar">
    <option value="FC Utrecht">
    <option value="FC Twente">
    <option value="Sparta Rotterdam">
    <option value="Heerenveen">
    <option value="NEC Nijmegen">
    <option value="Heracles">
    <option value="Go Ahead Eagles">
    <option value="PEC Zwolle">
    <option value="RKC Waalwijk">
    <option value="Almere City">
    <option value="NAC Breda">
    <option value="Willem II">
    <option value="Groningen">

    {{-- Primeira Liga (Portugal) --}}
    <option value="Benfica">
    <option value="Porto">
    <option value="Sporting CP">
    <option value="Braga">
    <option value="Vitória SC">
    <option value="Boavista">
    <option value="Estoril Praia">
    <option value="Famalicão">
    <option value="Gil Vicente">
    <option value="Casa Pia">
    <option value="Moreirense">
    <option value="Farense">
    <option value="Rio Ave">
    <option value="Santa Clara">
    <option value="Estrela Amadora">
    <option value="Arouca">
    <option value="Vizela">
    <option value="Nacional">

    {{-- Scottish Premiership --}}
    <option value="Celtic">
    <option value="Rangers">
    <option value="Aberdeen">
    <option value="Hearts">
    <option value="Hibernian">
    <option value="Motherwell">
    <option value="Dundee">
    <option value="St. Mirren">
    <option value="Ross County">
    <option value="St. Johnstone">
    <option value="Kilmarnock">
    <option value="Livingston">
    <option value="Dundee United">
    <option value="Partick Thistle">

    {{-- Super Lig (Turkey) --}}
    <option value="Galatasaray">
    <option value="Fenerbahçe">
    <option value="Beşiktaş">
    <option value="Trabzonspor">
    <option value="Başakşehir">
    <option value="Sivasspor">
    <option value="Kasımpaşa">
    <option value="Konyaspor">
    <option value="Antalyaspor">
    <option value="Kayserispor">
    <option value="Rizespor">
    <option value="Samsunspor">
    <option value="Gaziantep">
    <option value="Alanyaspor">
    <option value="Ankaragücü">
    <option value="Adana Demirspor">
    <option value="Hatayspor">
    <option value="Eyüpspor">

    {{-- Belgian Pro League --}}
    <option value="Club Brugge">
    <option value="Anderlecht">
    <option value="Gent">
    <option value="Union Saint-Gilloise">
    <option value="Antwerp">
    <option value="Standard Liège">
    <option value="Genk">
    <option value="Westerlo">
    <option value="Cercle Brugge">
    <option value="Charleroi">
    <option value="Sint-Truiden">
    <option value="Mechelen">
    <option value="OH Leuven">
    <option value="Kortrijk">

    {{-- Argentine Primera División --}}
    <option value="Boca Juniors">
    <option value="River Plate">
    <option value="Racing Club">
    <option value="Independiente">
    <option value="San Lorenzo">
    <option value="Estudiantes">
    <option value="Vélez Sarsfield">
    <option value="Lanús">
    <option value="Banfield">
    <option value="Talleres">
    <option value="Huracán">
    <option value="Newell's Old Boys">
    <option value="Rosario Central">
    <option value="Belgrano">
    <option value="Godoy Cruz">
    <option value="Defensa y Justicia">
    <option value="Tigre">
    <option value="Platense">
    <option value="Instituto">
    <option value="Sarmiento">

    {{-- Liga MX --}}
    <option value="Club América">
    <option value="Chivas Guadalajara">
    <option value="Cruz Azul">
    <option value="Pumas UNAM">
    <option value="Monterrey">
    <option value="Tigres UNAM">
    <option value="Toluca">
    <option value="Santos Laguna">
    <option value="Atlas">
    <option value="León">
    <option value="Necaxa">
    <option value="Mazatlán">
    <option value="Puebla">
    <option value="Pachuca">
    <option value="Atlético de San Luis">
    <option value="Querétaro">
    <option value value="Club Tijuana">
    <option value="FC Juárez">

    {{-- MLS --}}
    <option value="Inter Miami">
    <option value="LA Galaxy">
    <option value="LAFC">
    <option value="Seattle Sounders">
    <option value="Portland Timbers">
    <option value="Atlanta United">
    <option value="New York City FC">
    <option value="New York Red Bulls">
    <option value="Philadelphia Union">
    <option value="Toronto FC">
    <option value="Columbus Crew">
    <option value="Nashville SC">
    <option value="Austin FC">
    <option value="Real Salt Lake">
    <option value="Houston Dynamo">
    <option value="Sporting Kansas City">
    <option value="Chicago Fire">
    <option value="Orlando City">
    <option value="CF Montréal">
    <option value="Vancouver Whitecaps">
    <option value="Colorado Rapids">
    <option value="FC Dallas">
    <option value="D.C. United">
    <option value="Charlotte FC">
    <option value="St. Louis City">
    <option value="San Jose Earthquakes">

    {{-- Saudi Pro League --}}
    <option value="Al-Hilal">
    <option value="Al-Nassr">
    <option value="Al-Ittihad">
    <option value="Al-Ahli">
    <option value="Al-Shabab">
    <option value="Al-Fateh">
    <option value="Al-Qadsiah">
    <option value="Al-Wehda">
    <option value="Al-Tai">
    <option value="Damac">
    <option value="Al-Okhdood">
    <option value="Al-Hazem">
    <option value="Al-Riyadh">
    <option value="Al-Faisaly">
    <option value="Al-Khaleej">
    <option value="Abha">

    {{-- Super League Greece --}}
    <option value="Olympiacos">
    <option value="Panathinaikos">
    <option value="PAOK">
    <option value="AEK Athens">
    <option value="Aris Thessaloniki">
    <option value="Atromitos">
    <option value="Panionios">
    <option value="Asteras Tripolis">
    <option value="OFI Crete">
    <option value="Volos">

    {{-- Russian Premier League --}}
    <option value="Zenit St. Petersburg">
    <option value="CSKA Moscow">
    <option value="Spartak Moscow">
    <option value="Lokomotiv Moscow">
    <option value="Dynamo Moscow">
    <option value="Krasnodar">
    <option value="Rostov">
    <option value="Akhmat Grozny">
    <option value="Rubin Kazan">
    <option value="FK Sochi">

    {{-- DB-stored teams (typed by admin and auto-saved) --}}
    @foreach($teams ?? [] as $dbTeam)
        <option value="{{ $dbTeam }}">
    @endforeach
</datalist>

<script>
(function () {
    const teamStr = {
        'Manchester City': 91, 'Liverpool': 90, 'Arsenal': 87, 'Chelsea': 83,
        'Newcastle United': 80, 'Aston Villa': 78, 'Tottenham Hotspur': 77,
        'Manchester United': 75, 'Nottingham Forest': 72, 'Brighton & Hove Albion': 73,
        'Fulham': 70, 'Brentford': 68, 'Crystal Palace': 67, 'West Ham United': 66,
        'Everton': 65, 'AFC Bournemouth': 65, 'Wolverhampton Wanderers': 64,
        'Leicester City': 60, 'Ipswich Town': 58, 'Southampton': 55,
        'Real Madrid': 96, 'Barcelona': 92, 'Atlético Madrid': 88, 'Athletic Club': 78,
        'Girona': 68, 'Real Sociedad': 75, 'Villarreal': 74, 'Real Betis': 72,
        'Sevilla': 70, 'Celta Vigo': 63, 'Osasuna': 62, 'Valencia': 65,
        'Inter Milan': 89, 'Atalanta': 84, 'Napoli': 85, 'AC Milan': 83,
        'Juventus': 82, 'Roma': 77, 'Lazio': 75, 'Fiorentina': 72, 'Bologna': 70,
        'Torino': 65, 'Udinese': 62,
        'Bayern Munich': 94, 'Bayer Leverkusen': 88, 'Borussia Dortmund': 84,
        'RB Leipzig': 82, 'Eintracht Frankfurt': 75, 'Stuttgart': 73, 'Wolfsburg': 68,
        'Borussia Mönchengladbach': 66, 'Freiburg': 67, 'Werder Bremen': 65,
        'Paris Saint-Germain': 91, 'Monaco': 80, 'Marseille': 75, 'Lille': 74,
        'Lyon': 72, 'Rennes': 68, 'Nice': 67, 'Lens': 66, 'Brest': 65,
        'Benfica': 83, 'Porto': 82, 'Sporting CP': 81, 'Braga': 72,
        'Celtic': 80, 'Rangers': 77,
        'PSV Eindhoven': 82, 'Ajax': 80, 'Feyenoord': 79, 'AZ Alkmaar': 73,
        'Galatasaray': 76, 'Fenerbahçe': 74, 'Beşiktaş': 68, 'Trabzonspor': 65,
        'Club Brugge': 74, 'Anderlecht': 70, 'Gent': 68, 'Union Saint-Gilloise': 72,
        'Olympiacos': 70, 'Panathinaikos': 72, 'PAOK': 68, 'AEK Athens': 69,
        'Palmeiras': 82, 'Flamengo': 80, 'Atlético Mineiro': 78, 'Fluminense': 75,
        'Botafogo': 74, 'Corinthians': 72, 'São Paulo': 70, 'Fortaleza': 68,
        'Internacional': 70, 'Cruzeiro': 68, 'Grêmio': 67,
        'River Plate': 82, 'Boca Juniors': 80, 'Racing Club': 74, 'Independiente': 68,
        'San Lorenzo': 67, 'Estudiantes': 70,
        'Al-Hilal': 80, 'Al-Nassr': 78, 'Al-Ittihad': 75, 'Al-Ahli': 72,
        'Inter Miami': 72, 'LAFC': 70, 'LA Galaxy': 68, 'Seattle Sounders': 67,
        'Atlanta United': 66, 'New York City FC': 65, 'Portland Timbers': 65,
        'Club América': 76, 'Chivas Guadalajara': 72, 'Cruz Azul': 70,
        'Monterrey': 74, 'Tigres UNAM': 73, 'Pumas UNAM': 68,
    };

    const defaults = {
        'Home Win': 1.85, 'Draw': 3.20, 'Away Win': 2.10,
        'Over 0.5': 1.20, 'Over 1.5': 1.45, 'Over 2.5': 1.85, 'Over 3.5': 2.60,
        'Under 0.5': 5.00, 'Under 1.5': 2.80, 'Under 2.5': 1.75, 'Under 3.5': 1.35,
        'BTTS - Yes': 1.80, 'BTTS - No': 1.90,
        'Double Chance 1X': 1.35, 'Double Chance X2': 1.40, 'Double Chance 12': 1.30,
        'HT Home Win': 2.50, 'HT Draw': 1.80, 'HT Away Win': 3.20,
        'Over 3.5 Corners': 1.75, 'Over 9.5 Corners': 1.85, 'Over 1.5 Cards': 1.70,
    };

    const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));
    const fmt   = v => parseFloat(v).toFixed(2);

    function calcOdds(home, away, prediction) {
        const hs   = teamStr[home] ?? 70;
        const as   = teamStr[away] ?? 70;
        const diff = (hs + 5) - as;
        const hwp  = clamp(0.50 + diff * 0.006, 0.15, 0.82);
        const awp  = clamp(0.35 - diff * 0.005, 0.08, 0.65);
        const dp   = clamp(1 - hwp - awp, 0.10, 0.40);
        const m    = 0.90;
        const avg  = (hs + as) / 2;
        switch (prediction) {
            case 'Home Win':         return fmt(clamp(m / hwp, 1.10, 9.00));
            case 'Away Win':         return fmt(clamp(m / awp, 1.10, 9.00));
            case 'Draw':             return fmt(clamp(m / dp,  2.00, 7.50));
            case 'HT Home Win':      return fmt(clamp((m / hwp) * 1.30, 1.20, 10.00));
            case 'HT Away Win':      return fmt(clamp((m / awp) * 1.30, 1.20, 10.00));
            case 'HT Draw':          return fmt(clamp((m / dp)  * 0.55, 1.20, 3.80));
            case 'Double Chance 1X': return fmt(clamp(m / (hwp + dp), 1.05, 2.50));
            case 'Double Chance X2': return fmt(clamp(m / (awp + dp), 1.05, 2.50));
            case 'Double Chance 12': return fmt(clamp(m / (hwp + awp), 1.05, 2.00));
            case 'Over 2.5':   return fmt(avg > 74 ? 1.70 : avg > 65 ? 1.85 : 2.10);
            case 'Under 2.5':  return fmt(avg > 74 ? 2.10 : avg > 65 ? 1.75 : 1.55);
            case 'Over 1.5':   return fmt(avg > 65 ? 1.35 : 1.55);
            case 'Under 1.5':  return fmt(avg > 74 ? 3.00 : 2.70);
            case 'Over 3.5':   return fmt(avg > 74 ? 2.30 : 2.70);
            case 'Under 3.5':  return fmt(avg > 74 ? 1.25 : 1.40);
            case 'BTTS - Yes': return fmt(avg > 74 ? 1.65 : avg > 65 ? 1.80 : 2.00);
            case 'BTTS - No':  return fmt(avg > 74 ? 2.05 : avg > 65 ? 1.90 : 1.70);
            default:           return defaults[prediction] ? fmt(defaults[prediction]) : null;
        }
    }

    function calcConfidence(home, away, prediction) {
        const hs   = teamStr[home] ?? 70;
        const as   = teamStr[away] ?? 70;
        const diff = Math.abs((hs + 5) - as);
        const avg  = (hs + as) / 2;
        const matchResult = ['Home Win','Away Win','Draw','HT Home Win','HT Away Win','HT Draw',
                             'Double Chance 1X','Double Chance X2','Double Chance 12'];
        if (matchResult.includes(prediction)) {
            if (prediction === 'Draw' || prediction === 'HT Draw') {
                if (diff < 8)  return 4;
                if (diff < 18) return 3;
                if (diff < 30) return 2;
                return 1;
            }
            if (diff >= 35) return 5;
            if (diff >= 25) return 4;
            if (diff >= 15) return 3;
            if (diff >= 8)  return 2;
            return 1;
        }
        if (['Over 0.5','Over 1.5'].includes(prediction)) return avg > 65 ? 5 : 4;
        if (prediction === 'Over 2.5')   return avg > 74 ? 4 : avg > 65 ? 3 : 2;
        if (prediction === 'Under 2.5')  return avg > 74 ? 2 : avg > 65 ? 3 : 4;
        if (prediction === 'Over 3.5')   return avg > 74 ? 3 : 2;
        if (prediction === 'Under 3.5')  return avg > 74 ? 2 : 3;
        if (prediction === 'BTTS - Yes') return avg > 74 ? 4 : avg > 65 ? 3 : 2;
        if (prediction === 'BTTS - No')  return avg > 74 ? 2 : avg > 65 ? 3 : 4;
        return 3;
    }

    const homeInput  = document.querySelector('input[name="home_team"]');
    const awayInput  = document.querySelector('input[name="away_team"]');
    const predSel    = document.getElementById('prediction-select');
    const predHidden = document.getElementById('prediction-value');
    const oddsInput  = document.getElementById('odds-input');
    const oddsHint   = document.getElementById('odds-hint');
    const confSel    = document.getElementById('confidence-select');
    const confHint   = document.getElementById('conf-hint');

    if (!homeInput || !awayInput || !predSel || !oddsInput) return;

    function tryFill() {
        const home = homeInput.value.trim();
        const away = awayInput.value.trim();
        const pred = predHidden ? predHidden.value : predSel.value;
        if (!pred) return;
        if (oddsInput.value.trim() === '') {
            const odds = calcOdds(home, away, pred);
            if (odds) { oddsInput.value = odds; if (oddsHint) oddsHint.classList.remove('hidden'); }
        }
        if (confSel && confSel.value === '') {
            confSel.value = calcConfidence(home, away, pred);
            if (confHint) confHint.classList.remove('hidden');
        }
    }

    predSel.addEventListener('change', tryFill);
    homeInput.addEventListener('change', tryFill);
    awayInput.addEventListener('change', tryFill);
    oddsInput.addEventListener('input', () => { if (oddsHint) oddsHint.classList.add('hidden'); });
    if (confSel) confSel.addEventListener('change', () => { if (confHint) confHint.classList.add('hidden'); });
})();
</script>
