<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutocompleteEntry;
use App\Models\BettingTip;
use App\Services\TipAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BettingTipController extends Controller
{
    public function index(): View
    {
        $tips = BettingTip::latest('match_time')->paginate(15);

        return view('admin.betting-tips.index', compact('tips'));
    }

    public function create(): View
    {
        [$teams, $leagues] = $this->autocompleteData();

        return view('admin.betting-tips.create', compact('teams', 'leagues'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'home_team'  => ['required', 'string', 'max:100'],
            'away_team'  => ['required', 'string', 'max:100'],
            'country'    => ['nullable', 'string', 'max:100'],
            'league'     => ['nullable', 'string', 'max:100'],
            'prediction' => ['required', 'string', 'max:100'],
            'confidence' => ['nullable', 'integer', 'min:1', 'max:5'],
            'odds'       => ['nullable', 'numeric', 'min:1', 'max:1000'],
            'match_time' => ['required', 'date'],
            'status'     => ['required', 'in:pending,won,lost'],
            'is_premium' => ['nullable', 'boolean'],
            'reasoning'  => ['nullable', 'string', 'max:3000'],
        ]);

        $data['sport']      = 'Football';
        $data['is_premium'] = $request->boolean('is_premium');

        $tip = BettingTip::create($data);

        $manualReasoning = trim($request->input('reasoning', ''));
        $tip->update([
            'reasoning' => $manualReasoning ?: (new TipAnalysisService)->generate($tip),
            'analyst'   => auth()->user()->name,
        ]);

        $this->rememberTeamsAndLeague($data['home_team'], $data['away_team'], $data['league'] ?? null);

        return redirect()->route('admin.betting-tips.index')
            ->with('success', 'Tip created and live on the site.');
    }

    public function edit(BettingTip $bettingTip): View
    {
        [$teams, $leagues] = $this->autocompleteData();

        return view('admin.betting-tips.edit', compact('bettingTip', 'teams', 'leagues'));
    }

    public function update(Request $request, BettingTip $bettingTip): RedirectResponse
    {
        $data = $request->validate([
            'home_team'  => ['required', 'string', 'max:100'],
            'away_team'  => ['required', 'string', 'max:100'],
            'country'    => ['nullable', 'string', 'max:100'],
            'league'     => ['nullable', 'string', 'max:100'],
            'prediction' => ['required', 'string', 'max:100'],
            'confidence' => ['nullable', 'integer', 'min:1', 'max:5'],
            'odds'       => ['nullable', 'numeric', 'min:1', 'max:1000'],
            'match_time' => ['required', 'date'],
            'status'     => ['required', 'in:pending,won,lost'],
            'is_premium' => ['nullable', 'boolean'],
            'reasoning'  => ['nullable', 'string', 'max:3000'],
        ]);

        $data['sport']      = 'Football';
        $data['is_premium'] = $request->boolean('is_premium');

        $bettingTip->update($data);

        $manualReasoning = trim($request->input('reasoning', ''));
        $bettingTip->update([
            'reasoning' => $manualReasoning ?: (new TipAnalysisService)->generate($bettingTip),
            'analyst'   => auth()->user()->name,
        ]);

        $this->rememberTeamsAndLeague($data['home_team'], $data['away_team'], $data['league'] ?? null);

        return redirect()->route('admin.betting-tips.index')
            ->with('success', 'Tip updated successfully.');
    }

    public function markStatus(Request $request, BettingTip $bettingTip): RedirectResponse
    {
        $request->validate(['status' => 'required|in:pending,won,lost']);
        $bettingTip->update(['status' => $request->status]);
        return back()->with('success', 'Tip marked as ' . $request->status . '.');
    }

    public function destroy(BettingTip $bettingTip): RedirectResponse
    {
        $bettingTip->delete();

        return redirect()->route('admin.betting-tips.index')
            ->with('success', 'Tip deleted.');
    }

    private function autocompleteData(): array
    {
        $teams   = AutocompleteEntry::where('type', 'team')->orderBy('name')->pluck('name');
        $leagues = AutocompleteEntry::where('type', 'league')->orderBy('name')->pluck('name');

        return [$teams, $leagues];
    }

    private function rememberTeamsAndLeague(string $home, string $away, ?string $league): void
    {
        AutocompleteEntry::remember('team', $home);
        AutocompleteEntry::remember('team', $away);
        if ($league) {
            AutocompleteEntry::remember('league', $league);
        }
    }
}
