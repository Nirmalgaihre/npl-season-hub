@extends('layouts.admin')
@section('title', 'Add Team | NPL Season Hub')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <a href="{{ route('admin.teams.index') }}" class="text-sm font-semibold text-red-700">← All teams</a>
    <h1 class="mt-4 text-3xl font-black text-slate-900">Add a team</h1>
    <p class="mb-7 mt-2 text-sm text-slate-600">Enter verified team details and season participation.</p>

    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.teams.store') }}" method="POST" enctype="multipart/form-data"
        class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7">
        @include('admin.teams._form')
    </form>
</div>
@endsection