@extends('layouts.admin')

@section('title', 'Add Season | NPL Season Hub')

@section('content')
<div class="mx-auto max-w-4xl">
    <a href="{{ route('admin.seasons.index') }}"
       class="text-sm font-semibold text-slate-500 hover:text-red-600">
        ← Back to seasons
    </a>

    <h1 class="editorial-title mt-5 text-4xl font-black">Add a season</h1>
    <p class="mb-8 mt-2 text-slate-500">
        Enter verified tournament information.
    </p>

    <form method="POST" action="{{ route('admin.seasons.store') }}"
          class="rounded-xl border border-slate-200 bg-white p-6 md:p-8">
        @csrf
        @include('admin.seasons._form', ['buttonText' => 'Create season'])
    </form>
</div>
@endsection