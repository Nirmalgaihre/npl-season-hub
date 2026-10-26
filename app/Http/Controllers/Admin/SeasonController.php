<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeasonRequest;
use App\Http\Requests\UpdateSeasonRequest;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SeasonController extends Controller
{
    public function index(): View
    {
        $seasons = Season::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderByDesc('season_number')
            ->paginate(10)
            ->withQueryString();

        return view('admin.seasons.index', compact('seasons'));
    }

    public function create(): View
    {
        return view('admin.seasons.create');
    }

    public function store(StoreSeasonRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->makeSlug(
            $data['slug'] ?: $data['name']
        );

        Season::create($data);

        return redirect()
            ->route('admin.seasons.index')
            ->with('success', 'Season created successfully.');
    }

    public function show(Season $season): View
    {
        return view('admin.seasons.show', compact('season'));
    }

    public function edit(Season $season): View
    {
        return view('admin.seasons.edit', compact('season'));
    }

    public function update(
        UpdateSeasonRequest $request,
        Season $season
    ): RedirectResponse {
        $data = $request->validated();
        $data['slug'] = $this->makeSlug(
            $data['slug'] ?: $data['name'],
            $season->id
        );

        $season->update($data);

        return redirect()
            ->route('admin.seasons.index')
            ->with('success', 'Season updated successfully.');
    }

    public function destroy(Season $season): RedirectResponse
    {
        abort_unless(
            auth()->user()?->can('manage-seasons'),
            403
        );

        $season->delete();

        return redirect()
            ->route('admin.seasons.index')
            ->with('success', 'Season deleted successfully.');
    }

    private function makeSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);

        if ($base === '') {
            $base = 'season';
        }

        $slug = $base;
        $counter = 2;

        while (
            Season::where('slug', $slug)
                ->when($ignoreId, fn ($query) =>
                    $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}