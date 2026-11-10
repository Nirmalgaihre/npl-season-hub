<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Sponsor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SponsorController extends Controller
{
    private const CATEGORIES = [
        'Title Sponsor',
        'Powered By',
        'Broadcast & Streaming',
        'Official Broadcaster',
        'Digital Streaming',
        'Hydration Partner',
        'Ride-Hailing',
        'Merchandise & Jerseys',
        'Official Payment Partner',
        'Official Sportswear',
        'Official Partner',
        'Other',
    ];

    public function index(Request $request): View
    {
        $seasons = Season::query()
            ->orderByDesc('season_number')
            ->get();

        $selectedSeasonId = $request->integer('season_id');

        if (! $selectedSeasonId) {
            $selectedSeasonId = Season::query()
                ->where('status', 'active')
                ->orderByDesc('season_number')
                ->value('id')
                ?? Season::query()
                    ->orderByDesc('season_number')
                    ->value('id');
        }

        $selectedSeason = $selectedSeasonId
            ? Season::find($selectedSeasonId)
            : null;

        $sponsors = Sponsor::query()
            ->with('season')
            ->when($selectedSeason, fn ($query) =>
                $query->where('season_id', $selectedSeason->id)
            )
            ->when(! $selectedSeason, fn ($query) =>
                $query->whereRaw('1 = 0')
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $editingSponsor = null;

        if ($request->filled('edit')) {
            $editingSponsor = Sponsor::query()
                ->where('season_id', $selectedSeason?->id)
                ->find($request->integer('edit'));
        }

        return view('admin.sponsors.index', [
            'seasons' => $seasons,
            'selectedSeason' => $selectedSeason,
            'sponsors' => $sponsors,
            'editingSponsor' => $editingSponsor,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')
                ->store('sponsors', 'public');
        }

        unset($data['logo']);

        Sponsor::create($data);

        return redirect()
            ->route('admin.sponsors.index', [
                'season_id' => $data['season_id'],
            ])
            ->with('success', 'Sponsor added successfully.');
    }

    public function update(Request $request, Sponsor $sponsor): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('logo')) {
            if ($sponsor->logo_path) {
                Storage::disk('public')->delete($sponsor->logo_path);
            }

            $data['logo_path'] = $request->file('logo')
                ->store('sponsors', 'public');
        } else {
            unset($data['logo']);
        }

        $sponsor->update($data);

        return redirect()
            ->route('admin.sponsors.index', [
                'season_id' => $sponsor->season_id,
            ])
            ->with('success', 'Sponsor updated successfully.');
    }

    public function destroy(Sponsor $sponsor): RedirectResponse
    {
        $seasonId = $sponsor->season_id;

        if ($sponsor->logo_path) {
            Storage::disk('public')->delete($sponsor->logo_path);
        }

        $sponsor->delete();

        return redirect()
            ->route('admin.sponsors.index', [
                'season_id' => $seasonId,
            ])
            ->with('success', 'Sponsor deleted successfully.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'season_id' => [
                'required',
                'integer',
                'exists:seasons,id',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'category' => [
                'required',
                'string',
                Rule::in(self::CATEGORIES),
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:3072',
            ],
            'website_url' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:100000',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
