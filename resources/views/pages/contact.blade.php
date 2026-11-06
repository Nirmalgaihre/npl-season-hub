@extends('layouts.app')

@section('title', 'Contact Us | NPL Season Hub')
@section('meta_description', 'Contact the NPL Season Hub team with questions, corrections, feedback, or suggestions.')

@section('content')
<div class="bg-[#f8f9fa] text-[#202124] font-sans min-h-screen">

    {{-- ============================================================
         PREMIUM PAGE HEADER
    ============================================================ --}}
    <section class="relative overflow-hidden bg-npl-navy text-white">
        <div class="absolute inset-0 opacity-[0.04]"
            style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 24px 24px;">
        </div>

        <div class="absolute -top-24 -right-24 h-[420px] w-[420px] rounded-full bg-npl-red/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 h-[320px] w-[320px] rounded-full bg-npl-gold/10 blur-3xl pointer-events-none"></div>

        <div class="npl-container relative py-12 sm:py-16">
            <div class="max-w-3xl">
                <a href="{{ url('/') }}"
                    class="mb-4 inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-widest text-white/60 hover:text-white transition">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Home
                </a>

                <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-[#d93025]">
                    We Are Listening
                </p>

                <h1 class="text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-5xl">
                    Contact Us<span class="text-[#d93025]">.</span>
                </h1>

                <p class="mt-4 max-w-2xl text-base font-medium text-white/70">
                    Have feedback, spotted an error, or want to ask us something? Send us a message and the NPL Season Hub team will get back to you.
                </p>
            </div>
        </div>
    </section>

    {{-- ============================================================
         MAIN CONTENT
    ============================================================ --}}
    <div class="max-w-[760px] mx-auto px-4 py-10 sm:py-14">

        {{-- Success message (shown after Formspree redirect) --}}
        @if (request()->query('success'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-emerald-800">Message sent successfully</p>
                    <p class="mt-0.5 text-sm text-emerald-700">Thank you! We’ll get back to you as soon as possible.</p>
                </div>
            </div>
        @endif

        {{-- Contact Form Card --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 bg-[#f8f9fa] px-6 py-5 sm:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-npl-navy text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-[#202124]">Send us a message</h2>
                        <p class="text-xs text-gray-500">We usually respond within 24–48 hours</p>
                    </div>
                </div>
            </div>

            {{-- ==================== FORMSPREE FORM ==================== --}}
            <form action="https://formspree.io/f/mwllzjpn"
                  method="POST"
                  class="px-6 py-7 sm:px-8 space-y-5">

                {{-- Redirect back to this page with success flag --}}
                <input type="hidden" name="_next" value="{{ url()->current() }}?success=1">
                <input type="hidden" name="_subject" value="New message from NPL Season Hub">

                {{-- Honeypot (spam protection) --}}
                <input type="text" name="_gotcha" class="hidden" style="display:none">

                {{-- Name --}}
                <div>
                    <label for="name" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500">
                        Your Name
                    </label>
                    <input type="text" id="name" name="name" required maxlength="100"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-[#202124] outline-none transition focus:border-[#d93025] focus:ring-2 focus:ring-[#d93025]/10"
                        placeholder="Enter your full name">
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500">
                        Email Address
                    </label>
                    <input type="email" id="email" name="email" required maxlength="150"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-[#202124] outline-none transition focus:border-[#d93025] focus:ring-2 focus:ring-[#d93025]/10"
                        placeholder="you@example.com">
                </div>

                {{-- Subject --}}
                <div>
                    <label for="subject" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500">
                        Subject
                    </label>
                    <input type="text" id="subject" name="subject" required maxlength="150"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-[#202124] outline-none transition focus:border-[#d93025] focus:ring-2 focus:ring-[#d93025]/10"
                        placeholder="What is this about?">
                </div>

                {{-- Message --}}
                <div>
                    <label for="message" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500">
                        Message
                    </label>
                    <textarea id="message" name="message" rows="6" required maxlength="5000"
                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium text-[#202124] outline-none transition focus:border-[#d93025] focus:ring-2 focus:ring-[#d93025]/10 resize-y"
                        placeholder="Write your message here..."></textarea>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#d93025] px-6 py-3.5 text-sm font-bold text-white transition hover:bg-[#b71c1c] focus:outline-none focus:ring-2 focus:ring-[#d93025]/30 sm:w-auto">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Send Message
                    </button>
                </div>
            </form>
        </div>

        {{-- Privacy Note --}}
        <div class="mt-6 flex items-start gap-3 rounded-xl border border-gray-200 bg-white px-5 py-4">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">
                Please do not include passwords or other confidential information in your message. Your details will only be used to respond to your enquiry.
            </p>
        </div>

    </div>
</div>
@endsection