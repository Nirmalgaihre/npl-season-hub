@extends('layouts.app')

@section('title', 'Contact Us | NPL Season Hub')
@section('meta_description', 'Contact the NPL Season Hub team with questions, corrections, feedback, or suggestions.')

@section('content')
<div class="container py-5">
    <div class="mx-auto" style="max-width: 760px;">
        <p class="text-uppercase fw-semibold text-danger mb-2">We are listening</p>
        <h1 class="display-5 fw-bold mb-3">Contact Us</h1>
        <p class="text-muted mb-4">
            Have feedback, spotted an error, or want to ask us something?
            Send us a message using the form below.
        </p>

        @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('contact'))
            <div class="alert alert-danger" role="alert">
                {{ $errors->first('contact') }}
            </div>
        @endif

        <form method="POST" action="{{ route('contact.send') }}">
            @csrf

            <div class="position-absolute" style="left:-10000px;" aria-hidden="true">
                <label for="website">Leave this field empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Your name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}"
                    maxlength="100"
                    autocomplete="name"
                    required
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    maxlength="150"
                    autocomplete="email"
                    required
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input
                    type="text"
                    id="subject"
                    name="subject"
                    class="form-control @error('subject') is-invalid @enderror"
                    value="{{ old('subject') }}"
                    maxlength="150"
                    required
                >
                @error('subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea
                    id="message"
                    name="message"
                    class="form-control @error('message') is-invalid @enderror"
                    rows="6"
                    maxlength="5000"
                    required
                >{{ old('message') }}</textarea>
                @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-danger px-4">
                Send Message
            </button>
        </form>

        <p class="small text-muted mt-4">
            Please do not include passwords or other confidential information
            in your message.
        </p>
    </div>
</div>
@endsection
