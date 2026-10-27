<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(Request $request): View
    {
        $players = Player::query()
            ->with('team')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->string('search')->toString());

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nationality', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('team_id'), function ($query) use ($request) {
                $query->where('team_id', $request->integer('team_id'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $teams = Team::query()->orderBy('name')->get();

        return view('admin.players.index', compact('players', 'teams'));
    }

    public function create(): View
    {
        $teams = Team::query()->orderBy('name')->get();

        return view('admin.players.create', compact('teams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:150'],
            'role' => [
                'nullable',
                Rule::in([
                    'batter',
                    'bowler',
                    'all_rounder',
                    'wicket_keeper',
                ]),
            ],
            'nationality' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('players', 'public');
        }

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $suffix = 1;

        while (Player::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        Player::create([
            'team_id' => $validated['team_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'role' => $validated['role'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'photo_path' => $photoPath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.players.index')
            ->with('success', 'Player created successfully.');
    }

    public function edit(Player $player): View
    {
        $teams = Team::query()->orderBy('name')->get();

        return view('admin.players.edit', compact('player', 'teams'));
    }

    public function update(Request $request, Player $player): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:150'],
            'role' => [
                'nullable',
                Rule::in([
                    'batter',
                    'bowler',
                    'all_rounder',
                    'wicket_keeper',
                ]),
            ],
            'nationality' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $photoPath = $player->photo_path;

        if ($request->hasFile('photo')) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            $photoPath = $request->file('photo')->store('players', 'public');
        }

        $player->update([
            'team_id' => $validated['team_id'] ?? null,
            'name' => $validated['name'],
            'role' => $validated['role'] ?? null,
            'nationality' => $validated['nationality'] ?? null,
            'photo_path' => $photoPath,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.players.index')
            ->with('success', 'Player updated successfully.');
    }

    public function destroy(Player $player): RedirectResponse
    {
        if ($player->photo_path) {
            Storage::disk('public')->delete($player->photo_path);
        }

        $player->delete();

        return redirect()
            ->route('admin.players.index')
            ->with('success', 'Player deleted successfully.');
    }
}