<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'NPL Season Hub')</title>

    <meta
        name="description"
        content="@yield('meta_description', 'Follow Nepal Premier League fixtures, teams, match results and cricket news.')"
    >

    <meta name="theme-color" content="#202766">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    >

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        npl: {
                            navy: '#202766',
                            dark: '#171D50',
                            orange: '#F06445',
                            background: '#F6F8FB',
                            muted: '#64748B',
                        }
                    },
                    boxShadow: {
                        soft: '0 8px 30px rgba(32, 39, 102, 0.08)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                }
            }
        };
    </script>

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        a {
            transition:
                color .18s ease,
                background-color .18s ease,
                border-color .18s ease;
        }

        summary {
            list-style: none;
        }

        summary::-webkit-details-marker {
            display: none;
        }

        .npl-brand-logo {
            display: block;
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .npl-brand-logo-footer {
            display: block;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .npl-nav-link {
            position: relative;
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            white-space: nowrap;
        }

        .npl-nav-link-active::after {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            height: 3px;
            border-radius: 3px 3px 0 0;
            background: #F06445;
            content: "";
        }

        .npl-mobile-link {
            display: block;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
            font-weight: 600;
        }

        .npl-mobile-link:hover {
            background: #F6F8FB;
        }

        .npl-mobile-link-active {
            background: #FFF0EC;
            color: #D94D31;
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen bg-npl-background text-slate-800 antialiased">

    {{-- Public navigation --}}
    <header class="sticky top-0 z-40 border-b border-white/10 bg-npl-navy text-white shadow-sm">
        <div class="mx-auto flex min-h-[76px] max-w-7xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-8">

            {{-- Brand with real logo image --}}
            <a
                href="{{ route('home') }}"
                class="flex shrink-0 items-center gap-3"
                aria-label="NPL Season Hub home"
            >
                <img
                    src="{{ asset('images/npl.png') }}"
                    alt="NPL logo"
                    class="npl-brand-logo"
                    width="58"
                    height="58"
                    fetchpriority="high"
                >

    
            </a>

            {{-- Desktop navigation --}}
            <nav
                class="hidden items-center gap-5 lg:flex xl:gap-8"
                aria-label="Main navigation"
            >
                <a
                    href="{{ route('home') }}"
                    @class([
                        'npl-nav-link text-sm font-semibold',
                        'npl-nav-link-active text-white' => request()->routeIs('home'),
                        'text-white/70 hover:text-white' => !request()->routeIs('home'),
                    ])
                    @if(request()->routeIs('home')) aria-current="page" @endif
                >
                    Home
                </a>

                <a
                    href="{{ route('fixtures.index') }}"
                    @class([
                        'npl-nav-link text-sm font-semibold',
                        'npl-nav-link-active text-white' => request()->routeIs('fixtures.*'),
                        'text-white/70 hover:text-white' => !request()->routeIs('fixtures.*'),
                    ])
                    @if(request()->routeIs('fixtures.*')) aria-current="page" @endif
                >
                    Fixtures
                </a>

                <a
                    href="{{ route('teams.index') }}"
                    @class([
                        'npl-nav-link text-sm font-semibold',
                        'npl-nav-link-active text-white' => request()->routeIs('teams.*'),
                        'text-white/70 hover:text-white' => !request()->routeIs('teams.*'),
                    ])
                    @if(request()->routeIs('teams.*')) aria-current="page" @endif
                >
                    Teams
                </a>

                <a
                    href="{{ route('seasons.index') }}"
                    @class([
                        'npl-nav-link text-sm font-semibold',
                        'npl-nav-link-active text-white' => request()->routeIs('seasons.*'),
                        'text-white/70 hover:text-white' => !request()->routeIs('seasons.*'),
                    ])
                    @if(request()->routeIs('seasons.*')) aria-current="page" @endif
                >
                    Seasons
                </a>

                <a
                    href="{{ route('news.index') }}"
                    @class([
                        'npl-nav-link text-sm font-semibold',
                        'npl-nav-link-active text-white' => request()->routeIs('news.*'),
                        'text-white/70 hover:text-white' => !request()->routeIs('news.*'),
                    ])
                    @if(request()->routeIs('news.*')) aria-current="page" @endif
                >
                    News
                </a>
            </nav>

            {{-- Mobile navigation --}}
            <details class="relative lg:hidden">
                <summary
                    class="flex h-11 w-11 cursor-pointer items-center justify-center rounded-xl border border-white/20 text-white hover:bg-white/10"
                    aria-label="Open navigation menu"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 7h16M4 12h16M4 17h16"
                        />
                    </svg>
                </summary>

                <nav
                    class="absolute right-0 top-14 z-50 w-64 rounded-xl border border-slate-200 bg-white p-2 text-npl-navy shadow-soft"
                    aria-label="Mobile navigation"
                >
                    <a
                        href="{{ route('home') }}"
                        @class([
                            'npl-mobile-link',
                            'npl-mobile-link-active' => request()->routeIs('home'),
                        ])
                        @if(request()->routeIs('home')) aria-current="page" @endif
                    >
                        Home
                    </a>

                    <a
                        href="{{ route('fixtures.index') }}"
                        @class([
                            'npl-mobile-link',
                            'npl-mobile-link-active' => request()->routeIs('fixtures.*'),
                        ])
                        @if(request()->routeIs('fixtures.*')) aria-current="page" @endif
                    >
                        Fixtures
                    </a>

                    <a
                        href="{{ route('teams.index') }}"
                        @class([
                            'npl-mobile-link',
                            'npl-mobile-link-active' => request()->routeIs('teams.*'),
                        ])
                        @if(request()->routeIs('teams.*')) aria-current="page" @endif
                    >
                        Teams
                    </a>

                    <a
                        href="{{ route('seasons.index') }}"
                        @class([
                            'npl-mobile-link',
                            'npl-mobile-link-active' => request()->routeIs('seasons.*'),
                        ])
                        @if(request()->routeIs('seasons.*')) aria-current="page" @endif
                    >
                        Seasons
                    </a>

                    <a
                        href="{{ route('news.index') }}"
                        @class([
                            'npl-mobile-link',
                            'npl-mobile-link-active' => request()->routeIs('news.*'),
                        ])
                        @if(request()->routeIs('news.*')) aria-current="page" @endif
                    >
                        News
                    </a>
                </nav>
            </details>

        </div>
    </header>

    {{-- Main page content --}}
    <main class="min-h-[65vh]">
        @yield('content')
    </main>

    {{-- Public footer --}}
    <footer class="mt-12 bg-npl-navy text-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-10 sm:px-8 md:grid-cols-3">

            {{-- Footer brand --}}
            <div>
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center gap-3"
                    aria-label="NPL Season Hub home"
                >
                    <img
                        src="{{ asset('images/npl.png') }}"
                        alt="NPL logo"
                        class="npl-brand-logo-footer"
                        width="42"
                        height="42"
                        loading="lazy"
                    >

                    <span class="text-lg font-extrabold tracking-tight">
                        Season Hub
                    </span>
                </a>

                <p class="mt-4 max-w-sm text-sm leading-6 text-white/65">
                    Your place to follow NPL teams, fixtures, verified match
                    results and cricket news.
                </p>
            </div>

            {{-- Footer navigation --}}
            <div>
                <h2 class="text-sm font-extrabold uppercase tracking-wider">
                    Explore
                </h2>

                <div class="mt-4 grid gap-3 text-sm text-white/70">
                    <a
                        href="{{ route('fixtures.index') }}"
                        class="hover:text-white"
                    >
                        Fixtures &amp; Results
                    </a>

                    <a
                        href="{{ route('teams.index') }}"
                        class="hover:text-white"
                    >
                        Teams
                    </a>

                    <a
                        href="{{ route('seasons.index') }}"
                        class="hover:text-white"
                    >
                        Season Archive
                    </a>

                    <a
                        href="{{ route('news.index') }}"
                        class="hover:text-white"
                    >
                        Latest News
                    </a>
                </div>
            </div>

            {{-- Match information --}}
            <div>
                <h2 class="text-sm font-extrabold uppercase tracking-wider">
                    Match Information
                </h2>

                <p class="mt-4 text-sm leading-6 text-white/65">
                    Match scores and results are displayed according to the
                    information published and verified by the site's
                    administrators.
                </p>
            </div>

        </div>

        {{-- Copyright --}}
        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-5 py-5 text-xs text-white/55 sm:px-8 md:flex-row md:items-center md:justify-between">
                <p>
                    &copy; {{ date('Y') }} NPL Season Hub. All rights reserved.
                </p>

                <p>Made for Nepal cricket fans.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')

</body>
</html>