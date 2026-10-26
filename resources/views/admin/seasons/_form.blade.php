@php
    $season = $season ?? null;
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        <p class="font-bold">Please fix the following errors:</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="name" class="mb-2 block text-sm font-semibold">Season name *</label>
        <input id="name" name="name" required maxlength="100"
               value="{{ old('name', $season?->name) }}"
               placeholder="e.g. Nepal Premier League Season 3"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none focus:border-red-600 focus:ring-2 focus:ring-red-100">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="season_number" class="mb-2 block text-sm font-semibold">Season number *</label>
        <input id="season_number" name="season_number" type="number"
               min="1" max="65535" required
               value="{{ old('season_number', $season?->season_number) }}"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none focus:border-red-600 focus:ring-2 focus:ring-red-100">
        @error('season_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="slug" class="mb-2 block text-sm font-semibold">URL slug</label>
        <input id="slug" name="slug" maxlength="120"
               value="{{ old('slug', $season?->slug) }}"
               placeholder="Leave blank to generate automatically"
               pattern="[a-z0-9]+(-[a-z0-9]+)*"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none focus:border-red-600 focus:ring-2 focus:ring-red-100">
        @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-slate-500">Use lowercase letters, numbers and hyphens.</p>
    </div>

    <div>
        <label for="status" class="mb-2 block text-sm font-semibold">Status *</label>
        <select id="status" name="status" required
                class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 outline-none focus:border-red-600">
            @foreach (['upcoming' => 'Upcoming', 'active' => 'Active', 'completed' => 'Completed'] as $value => $label)
                <option value="{{ $value }}"
                    @selected(old('status', $season?->status ?? 'upcoming') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="start_date" class="mb-2 block text-sm font-semibold">Start date</label>
        <input id="start_date" name="start_date" type="date"
               value="{{ old('start_date', $season?->start_date?->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3">
        @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="end_date" class="mb-2 block text-sm font-semibold">End date</label>
        <input id="end_date" name="end_date" type="date"
               value="{{ old('end_date', $season?->end_date?->format('Y-m-d')) }}"
               class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3">
        @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="mb-2 block text-sm font-semibold">Description</label>
        <textarea id="description" name="description" rows="5" maxlength="5000"
                  placeholder="Enter verified information about this season."
                  class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3">{{ old('description', $season?->description) }}</textarea>
        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-8 flex flex-wrap gap-3">
    <button type="submit"
            class="rounded-lg bg-red-600 px-5 py-3 text-sm font-bold text-white hover:bg-red-700">
        {{ $buttonText ?? 'Save season' }}
    </button>

    <a href="{{ route('admin.seasons.index') }}"
       class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold hover:bg-slate-100">
        Cancel
    </a>
</div>