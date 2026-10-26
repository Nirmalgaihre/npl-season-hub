
@csrf

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div>
            <label for="name" class="mb-2 block text-sm font-semibold">Team name *</label>
            <input id="name" name="name" required maxlength="150"
                value="{{ old('name', $team->name) }}"
                class="w-full rounded-lg border border-slate-300 px-4 py-3"
                placeholder="Enter the official team name">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="short_name" class="mb-2 block text-sm font-semibold">Short name</label>
                <input id="short_name" name="short_name" maxlength="30"
                    value="{{ old('short_name', $team->short_name) }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3"
                    placeholder="Official abbreviation">
                @error('short_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="slug" class="mb-2 block text-sm font-semibold">URL slug (optional)</label>
                <input id="slug" name="slug" maxlength="170"
                    value="{{ old('slug', $team->slug) }}"
                    class="w-full rounded-lg border border-slate-300 px-4 py-3"
                    placeholder="Leave blank to generate">
                @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="home_venue" class="mb-2 block text-sm font-semibold">Home venue</label>
            <input id="home_venue" name="home_venue" maxlength="180"
                value="{{ old('home_venue', $team->home_venue) }}"
                class="w-full rounded-lg border border-slate-300 px-4 py-3"
                placeholder="Enter the verified home venue">
            @error('home_venue') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="website_url" class="mb-2 block text-sm font-semibold">Official website</label>
            <input id="website_url" name="website_url" type="url" maxlength="255"
                value="{{ old('website_url', $team->website_url) }}"
                class="w-full rounded-lg border border-slate-300 px-4 py-3"
                placeholder="https://example.com">
            @error('website_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="mb-2 block text-sm font-semibold">Team description</label>
            <textarea id="description" name="description" rows="6" maxlength="5000"
                class="w-full rounded-lg border border-slate-300 px-4 py-3"
                placeholder="Enter a verified team profile">{{ old('description', $team->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <aside class="space-y-5">
        <div class="rounded-xl border border-slate-200 p-4">
            <label for="logo" class="mb-3 block font-bold">Team logo</label>

            @if ($team->logo)
                <img src="{{ asset('storage/'.$team->logo) }}"
                    alt="Current {{ $team->name }} logo"
                    class="mb-4 h-28 w-28 rounded-xl border border-slate-200 bg-white object-contain p-2">
            @endif

            <input id="logo" name="logo" type="file"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                class="w-full text-sm">

            <p class="mt-2 text-xs leading-5 text-slate-500">
                JPG, PNG, or WebP. Maximum 4 MB.
            </p>
            @error('logo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-xl border border-slate-200 p-4">
            <label for="is_active" class="mb-2 block font-bold">Visibility</label>
            <select id="is_active" name="is_active"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
                <option value="1" @selected((string) old('is_active', (int) ($team->exists ? $team->is_active : true)) === '1')>Active — visible publicly</option>
                <option value="0" @selected((string) old('is_active', (int) ($team->exists ? $team->is_active : true)) === '0')>Inactive — hidden publicly</option>
            </select>
            @error('is_active') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-xl border border-slate-200 p-4">
            <p class="mb-3 font-bold">Participating seasons</p>
            <p class="mb-3 text-xs leading-5 text-slate-500">
                Select the seasons in which this team participates.
            </p>

            @forelse ($seasons as $season)
                <label class="mb-3 flex items-start gap-2 text-sm">
                    <input type="checkbox" name="season_ids[]" value="{{ $season->id }}"
                        @checked(in_array($season->id, array_map('intval', old('season_ids', $team->exists ? $team->seasons->modelKeys() : []))))
                        class="mt-1 rounded border-slate-300 text-red-700">
                    <span>
                        <span class="block font-medium">{{ $season->name }}</span>
                        <span class="text-xs text-slate-500">Season {{ $season->season_number }}</span>
                    </span>
                </label>
            @empty
                <p class="text-sm text-slate-500">Create a season before assigning teams.</p>
            @endforelse

            @error('season_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('season_ids.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </aside>
</div>

<div class="mt-7 flex flex-wrap gap-3 border-t border-slate-200 pt-5">
    <button class="rounded-lg bg-red-700 px-5 py-3 font-semibold text-white hover:bg-red-800">
        {{ $team->exists ? 'Save changes' : 'Create team' }}
    </button>
    <a href="{{ route('admin.teams.index') }}"
        class="rounded-lg border border-slate-300 px-5 py-3 font-semibold text-slate-700">
        Cancel
    </a>
</div>
