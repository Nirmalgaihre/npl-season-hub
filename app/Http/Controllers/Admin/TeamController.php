<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $teams = Team::with('seasons')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where(
                    'is_active',
                    $request->input('status') === 'active'
                );
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.teams.index', compact('teams'));
    }

    public function create()
    {
        return view('admin.teams.create', [
            'team' => new Team(),
            'seasons' => Season::orderByDesc('season_number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $seasonIds = $data['season_ids'] ?? [];
        unset($data['season_ids']);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')
                ->store('teams', 'public');
        }

        $team = Team::create($data);
        $team->seasons()->sync($seasonIds);

        return redirect()
            ->route('admin.teams.index')
            ->with('success', 'Team created successfully.');
    }

    public function edit(Team $team)
    {
        $team->load('seasons');

        return view('admin.teams.edit', [
            'team' => $team,
            'seasons' => Season::orderByDesc('season_number')->get(),
        ]);
    }

    public function update(Request $request, Team $team)
    {
        $data = $this->validatedData($request, $team);

        $seasonIds = $data['season_ids'] ?? [];
        unset($data['season_ids']);

        $data['slug'] = $this->uniqueSlug(
            $data['slug'] ?: $data['name'],
            $team->id
        );

        if ($request->hasFile('logo')) {
            if ($team->logo) {
                Storage::disk('public')->delete($team->logo);
            }

            $data['logo'] = $request->file('logo')
                ->store('teams', 'public');
        }

        $team->update($data);
        $team->seasons()->sync($seasonIds);

        return redirect()
            ->route('admin.teams.index')
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        if ($team->logo) {
            Storage::disk('public')->delete($team->logo);
        }

        $team->delete();

        return redirect()
            ->route('admin.teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    private function validatedData(
        Request $request,
        ?Team $team = null
    ): array {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'short_name' => ['nullable', 'string', 'max:30'],
            'slug' => [
                'nullable',
                'string',
                'max:170',
                Rule::unique('teams', 'slug')->ignore($team?->id),
            ],
            'home_venue' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'season_ids' => ['nullable', 'array'],
            'season_ids.*' => [
                'integer',
                'distinct',
                'exists:seasons,id',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ]);
    }

    private function uniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug($value) ?: 'team';
        $slug = $base;
        $counter = 2;

        while (
            Team::where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
