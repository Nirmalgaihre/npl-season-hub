@extends('layouts.admin')

@section('title', 'Players')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#f06445]">
                Cricket management
            </p>
            <h1 class="mt-2 text-3xl font-extrabold text-[#1d0647]">
                Players
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Manage player profiles, teams and photos.
            </p>
        </div>

        <a href="{{ route('admin.players.create') }}"
           class="inline-flex items-center justify-center bg-[#1d0647] px-5 py-3 text-sm font-bold text-white hover:bg-[#2d0b68]">
            + Add player
        </a>
    </div>

    @if (session('success'))
        <div class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.players.index') }}"
          class="grid gap-3 border border-slate-200 bg-white p-4 md:grid-cols-[1fr_220px_auto]">
        <input
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Search player name or nationality..."
            class="w-full border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#1d0647]"
        >

        <select name="team_id"
                class="w-full border border-slate-300 px-4 py-3 text-sm outline-none focus:border-[#1d0647]">
            <option value="">All teams</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected((string) request('team_id') === (string) $team->id)>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>

        <button class="bg-[#1d0647] px-5 py-3 text-sm font-bold text-white">
            Filter
        </button>
    </form>

    <div class="overflow-hidden border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Player</th>
                        <th class="px-5 py-4">Team</th>
                        <th class="px-5 py-4">Role</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($players as $player)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <img
                                        src="{{ $player->photo_url }}"
                                        alt="{{ $player->name }}"
                                        class="h-12 w-12 rounded-full border border-slate-200 object-cover"
                                    >
                                    <div>
                                        <p class="font-bold text-[#1d0647]">{{ $player->name }}</p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $player->nationality ?: 'Nationality not set' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                {{ $player->team?->name ?? 'Unassigned' }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $player->role
                                    ? ucwords(str_replace('_', ' ', $player->role))
                                    : 'Not set' }}
                            </td>

                            <td class="px-5 py-4">
                                @if ($player->is_active)
                                    <span class="bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                        Active
                                    </span>
                                @else
                                    <span class="bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.players.edit', $player) }}"
                                       class="font-bold text-[#1d0647] hover:underline">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.players.destroy', $player) }}"
                                          onsubmit="return confirm('Delete this player profile?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="font-bold text-red-600 hover:underline">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <p class="font-bold text-slate-700">No players found</p>
                                <p class="mt-2 text-sm text-slate-500">
                                    Add a player or change your search filters.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-5 py-4">
            {{ $players->links() }}
        </div>
    </div>
</div>
@endsection