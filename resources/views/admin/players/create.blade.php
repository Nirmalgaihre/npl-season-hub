@extends('layouts.admin')

@section('title', 'Add Player')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('admin.players.index') }}"
       class="text-sm font-bold text-[#1d0647] hover:underline">
        ← Back to players
    </a>

    <h1 class="mt-5 text-3xl font-extrabold text-[#1d0647]">Add player</h1>
    <p class="mt-2 text-sm text-slate-500">Create a player profile and upload a photo.</p>

    @include('admin.players._form', [
        'action' => route('admin.players.store'),
        'method' => 'POST',
        'player' => null,
    ])
</div>
@endsection