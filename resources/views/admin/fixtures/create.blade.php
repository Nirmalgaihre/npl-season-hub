@extends('layouts.admin')
@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">
    <div class="mb-7">
        <a href="{{ route('admin.fixtures.index') }}"
           class="text-sm font-semibold text-red-700 hover:underline">
            ← Back to fixtures
        </a>
        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900">
            Create fixture
        </h1>
        <p class="mt-2 text-slate-500">Schedule a match for an existing season.</p>
    </div>

    <form action="{{ route('admin.fixtures.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.fixtures._form')
    </form>
</div>
@endsection