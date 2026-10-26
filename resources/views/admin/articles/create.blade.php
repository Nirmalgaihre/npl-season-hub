@extends('layouts.admin')

@section('title', 'Write Article | NPL Season Hub')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <a href="{{ route('admin.articles.index') }}" class="text-sm font-semibold text-red-700">← All articles</a>
    <h1 class="mb-2 mt-4 text-3xl font-black text-slate-900">Write an article</h1>
    <p class="mb-7 text-sm text-slate-600">Write accurate, original NPL news and match coverage.</p>

    @if ($errors->any())
        <div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-800">
            <p class="font-bold">Please correct the following:</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" class="rounded-xl border border-slate-200 bg-white p-5 sm:p-7">
        @include('admin.articles._form')
    </form>
</div>
@endsection