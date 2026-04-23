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
        <select name="prediction" required
                class="w-full px-3 py-2.5 rounded-lg border @error('prediction') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">— Select —</option>
            @php
                $predictions = [
                    'Match Result'   => ['Home Win', 'Draw', 'Away Win'],
                    'Goals'          => ['Over 0.5', 'Over 1.5', 'Over 2.5', 'Over 3.5', 'Under 0.5', 'Under 1.5', 'Under 2.5', 'Under 3.5'],
                    'Both Teams'     => ['BTTS - Yes', 'BTTS - No'],
                    'Double Chance'  => ['Double Chance 1X', 'Double Chance X2', 'Double Chance 12'],
                    'Half Time'      => ['HT Home Win', 'HT Draw', 'HT Away Win'],
                    'Cards / Corners'=> ['Over 3.5 Corners', 'Over 9.5 Corners', 'Over 1.5 Cards'],
                ];
                $current = old('prediction', $bettingTip->prediction ?? '');
            @endphp
            @foreach($predictions as $group => $options)
                <optgroup label="{{ $group }}">
                    @foreach($options as $opt)
                        <option value="{{ $opt }}" {{ $current === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('prediction')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Confidence (1–5)</label>
        <select name="confidence"
                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">— None —</option>
            @foreach([1 => '★ Low', 2 => '★★', 3 => '★★★ Medium', 4 => '★★★★', 5 => '★★★★★ High'] as $val => $label)
                <option value="{{ $val }}" {{ old('confidence', $bettingTip->confidence ?? '') == $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('confidence')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Odds</label>
        <input type="number" name="odds" step="0.01" min="1"
               value="{{ old('odds', $bettingTip->odds ?? '') }}"
               placeholder="e.g. 1.85"
               class="w-full px-3 py-2.5 rounded-lg border @error('odds') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        @error('odds')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Match Time <span class="text-red-500">*</span></label>
        <input type="datetime-local" name="match_time" required
               value="{{ old('match_time', isset($bettingTip) ? $bettingTip->match_time->format('Y-m-d\TH:i') : '') }}"
               class="w-full px-3 py-2.5 rounded-lg border @error('match_time') border-red-400 bg-red-50 @else border-gray-300 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        @error('match_time')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
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
    <label class="block text-sm font-medium text-gray-700 mb-1.5">Premium Tip</label>
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_premium" value="1"
               {{ old('is_premium', $bettingTip->is_premium ?? false) ? 'checked' : '' }}
               class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
        <span class="text-sm text-gray-600">Mark as Premium</span>
    </label>
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
</datalist>
