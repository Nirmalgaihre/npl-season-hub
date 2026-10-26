@extends('layouts.admin')

@section('title', 'Edit Season | NPL Season Hub')

@section('content')
<div class="mx-auto max-w-4xl">
    <a href="{{ route('admin.seasons.index') }}"
       class="text-sm font-semibold text-slate-500 hover:text-red-600">
        ← Back to seasons
    </a>

    <h1 class="editorial-title mt-5 text-4xl font-black">Edit season</h1>
    <p class="mb-8 mt-2 text-slate-500">
        Update {{ $season->name }}.
    </p>

    <form method="POST" action="{{ route('admin.seasons.update', $season) }}"
          class="rounded-xl border border-slate-200 bg-white p-6 md:p-8">
        @csrf
        @method('PUT')
        @include('admin.seasons._form', [
            'season' => $season,
            'buttonText' => 'Save changes'
        ])
    </form>
</div>
@endsection