@php
    $home = $match->homeTeam;
    $away = $match->awayTeam;
    $verified = (bool) $match->is_result_verified;

    $statusStyles = [
        'live' => 'bg-red-50 text-red-700',
        'scheduled' => 'bg-blue-50 text-blue-700',
        'completed' => 'bg-emerald-50 text-emerald-700',
        'postponed' => 'bg-orange-50 text-orange-700',
        'abandoned' => 'bg-slate-100 text-slate-600',
    ];
@endphp

<article class="overflow-hidden border border-slate-200 bg-white transition hover:border-[#202766]/40">

    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-[#F06445]">
                {{ $match->season->name }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                Match {{ $match->match_number }}
                · {{ ucfirst($match->match_type) }}
            </p>
        </div>

        <span class="shrink-0 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider {{ $statusStyles[$match->status] ?? 'bg-slate-100 text-slate-600' }}">
            {{ $match->status === 'scheduled' ? 'Not Started Yet' : ucfirst($match->status) }}
        </span>
    </div>

    <div class="p-5">

        {{-- Home side --}}
        <div class="flex items-center gap-3">
            @if ($home->logo)
                <img src="{{ asset('storage/' . $home->logo) }}"
                     alt="{{ $home->name }}"
                     class="h-10 w-10 shrink-0 object-contain">
            @else
                <div class="flex h-10 w-10 shrink-0 items-center justify-center bg-[#F6F8FB] text-xs font-extrabold text-[#202766]">
                    {{ strtoupper(substr($home->short_name ?: $home->name, 0, 3)) }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <p class="break-words text-sm font-bold text-[#202766]">
                    {{ $home->name }}
                </p>
            </div>

            @if ($verified)
                <p class="shrink-0 text-base font-extrabold text-[#202766]">
                    {{ $match->home_runs ?? 0 }}/{{ $match->home_wickets ?? 0 }}
                </p>
            @endif
        </div>

        <div class="my-4 border-t border-dashed border-slate-200"></div>

        {{-- Away side --}}
        <div class="flex items-center gap-3">
            @if ($away->logo)
                <img src="{{ asset('storage/' . $away->logo) }}"
                     alt="{{ $away->name }}"
                     class="h-10 w-10 shrink-0 object-contain">
            @else
                <div class="flex h-10 w-10 shrink-0 items-center justify-center bg-[#F6F8FB] text-xs font-extrabold text-[#202766]">
                    {{ strtoupper(substr($away->short_name ?: $away->name, 0, 3)) }}
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <p class="break-words text-sm font-bold text-[#202766]">
                    {{ $away->name }}
                </p>
            </div>

            @if ($verified)
                <p class="shrink-0 text-base font-extrabold text-[#202766]">
                    {{ $match->away_runs ?? 0 }}/{{ $match->away_wickets ?? 0 }}
                </p>
            @endif
        </div>

        {{-- Match result or schedule --}}
        @if ($verified && $match->winner)
            <div class="mt-5 border-l-4 border-[#F06445] bg-[#F6F8FB] px-4 py-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Winner
                </p>
                <p class="mt-1 text-sm font-extrabold text-[#202766]">
                    {{ $match->winner->name }}
                </p>
                @if ($match->result_summary)
                    <p class="mt-1 text-xs leading-5 text-slate-600">
                        {{ $match->result_summary }}
                    </p>
                @endif
            </div>
        @elseif ($match->status === 'scheduled')
            <p class="mt-5 text-sm text-slate-500">
                {{ $match->scheduled_at?->format('D, d M Y · h:i A') ?? 'Date and time to be announced' }}
            </p>
        @elseif ($match->status === 'live' && !$verified)
            <p class="mt-5 text-sm font-semibold text-red-600">
                Match in progress · Official score unavailable
            </p>
        @elseif ($match->status === 'completed' && !$verified)
            <p class="mt-5 text-sm text-slate-500">
                Result awaiting verification
            </p>
        @else
            <p class="mt-5 text-sm text-slate-500">
                {{ $match->scheduled_at?->format('D, d M Y · h:i A') ?? 'Schedule information unavailable' }}
            </p>
        @endif

        <div class="mt-5 border-t border-slate-100 pt-4">
            <a href="{{ route('fixtures.show', $match) }}"
               class="inline-flex items-center gap-2 text-sm font-bold text-[#202766] hover:text-[#F06445]">
                View match details <span aria-hidden="true">→</span>
            </a>
        </div>

    </div>
</article>