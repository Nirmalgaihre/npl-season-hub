<form method="POST"
      action="{{ $action }}"
      enctype="multipart/form-data"
      class="mt-7 space-y-6 border border-slate-200 bg-white p-5 sm:p-8">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div class="border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            Please correct the errors below.
        </div>
    @endif

    <div>
        <label for="name" class="mb-2 block text-sm font-bold text-slate-700">
            Player name *
        </label>
        <input id="name" name="name" required maxlength="150"
               value="{{ old('name', $player?->name) }}"
               class="w-full border border-slate-300 px-4 py-3 outline-none focus:border-[#1d0647]">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="team_id" class="mb-2 block text-sm font-bold text-slate-700">Team</label>
        <select id="team_id" name="team_id"
                class="w-full border border-slate-300 px-4 py-3 outline-none focus:border-[#1d0647]">
            <option value="">No team assigned</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}"
                    @selected((string) old('team_id', $player?->team_id) === (string) $team->id)>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>
        @error('team_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="role" class="mb-2 block text-sm font-bold text-slate-700">Playing role</label>
            <select id="role" name="role"
                    class="w-full border border-slate-300 px-4 py-3 outline-none focus:border-[#1d0647]">
                <option value="">Select role</option>
                @foreach ([
                    'batter' => 'Batter',
                    'bowler' => 'Bowler',
                    'all_rounder' => 'All-rounder',
                    'wicket_keeper' => 'Wicket-keeper',
                ] as $value => $label)
                    <option value="{{ $value }}"
                        @selected(old('role', $player?->role) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="nationality" class="mb-2 block text-sm font-bold text-slate-700">
                Nationality
            </label>
            <input id="nationality" name="nationality" maxlength="100"
                   value="{{ old('nationality', $player?->nationality) }}"
                   placeholder="e.g. Nepal"
                   class="w-full border border-slate-300 px-4 py-3 outline-none focus:border-[#1d0647]">
            @error('nationality') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    @if ($player?->photo_path)
        <div>
            <p class="mb-2 text-sm font-bold text-slate-700">Current photo</p>
            <img src="{{ $player->photo_url }}" alt="{{ $player->name }}"
                 class="h-24 w-24 rounded-full border border-slate-200 object-cover">
        </div>
    @endif

    <div>
        <label for="photo" class="mb-2 block text-sm font-bold text-slate-700">
            Player photo
        </label>
        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"
               class="block w-full border border-slate-300 p-3 text-sm">
        <p class="mt-2 text-xs text-slate-500">JPG, PNG or WebP. Maximum 3 MB.</p>
        @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-center gap-3 border border-slate-200 bg-slate-50 p-4">
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $player?->is_active ?? true))>
        <span>
            <span class="block text-sm font-bold">Active player</span>
            <span class="mt-1 block text-xs text-slate-500">
                Active profiles can be matched to scorecard names.
            </span>
        </span>
    </label>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.players.index') }}"
           class="border border-slate-300 px-5 py-3 text-center text-sm font-bold">
            Cancel
        </a>
        <button type="submit"
                class="bg-[#1d0647] px-6 py-3 text-sm font-bold text-white hover:bg-[#2d0b68]">
            {{ $player ? 'Save changes' : 'Create player' }}
        </button>
    </div>
</form>