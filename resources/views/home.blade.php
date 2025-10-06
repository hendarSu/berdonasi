<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        {{ env('APP_NAME') }} — Beranda
    </title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $analytics = $org?->meta_json['analytics'] ?? [];
        $fbPixelId = $analytics['facebook_pixel_id'] ?? null;
        $gtmId = $analytics['gtm_id'] ?? null;
    @endphp
    @if (!empty($gtmId))
        <script>(function (w, d, s, l, i) {
                w[l] = w[l] || []; w[l].push({
                    'gtm.start':
                        new Date().getTime(), event: 'gtm.js'
                }); var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                        'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '{{ $gtmId }}');</script>
    @endif
    @if (!empty($fbPixelId))
        <script>
            !function (f, b, e, v, n, t, s) {
                if (f.fbq) return; n = f.fbq = function () {
                    n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                }; if (!f._fbq) f._fbq = n;
                n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0;
                t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s)
            }(window, document, 'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $fbPixelId }}');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
                 src="https://www.facebook.com/tr?id={{ $fbPixelId }}&ev=PageView&noscript=1" /></noscript>
    @endif
</head>

<body class="bg-white text-gray-900">
    @if (!empty($gtmId))
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0"
                    style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    <header class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="shrink-0">
                        @if (!empty($org?->logo_url))
                            <img src="{{ $org->logo_url }}" alt="{{ $org->name }}" class="h-8 w-auto" />
                        @else
                            <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7 8L3 11.6923L7 16M17 8L21 11.6923L17 16M14 4L10 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        @endif
                    </a>

                    <!-- Desktop Navigation -->
                    <nav class="hidden lg:flex items-center gap-1">
                        @php
                            $tabBase = 'px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors';
                            $tabActive = 'px-4 py-2 text-sm font-semibold text-gray-900 bg-gray-50 rounded-lg';
                        @endphp
                        @include('partials.menu-links', ['tabBase' => $tabBase, 'tabActive' => $tabActive])
                    </nav>
                </div>

                <!-- Search & Mobile Menu -->
                <div class="flex items-center gap-3">
                    <!-- Desktop Search -->
                    <form method="get" action="{{ route('home') }}" class="hidden sm:block">
                        <div class="relative">
                            <input
                                type="text"
                                name="q"
                                value="{{ $q ?? '' }}"
                                placeholder="Cari Program"
                                class="w-64 lg:w-80 rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 transition-colors"
                                style="padding-left: 10%"
                            />
                        </div>
                    </form>

                    <!-- Mobile Menu Button -->
                    <button
                        id="mobile-nav-toggle"
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 hover:text-gray-900 lg:hidden transition-colors"
                        aria-label="Toggle menu"
                        aria-expanded="false"
                    >
                        <svg id="menu-icon" class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="close-icon" class="h-6 w-6 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-200">
            <div class="px-4 py-3 space-y-1">
                @php
                    $mobileTabBase = 'block px-4 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors';
                    $mobileTabActive = 'block px-4 py-3 text-sm font-semibold text-gray-900 bg-gray-50 rounded-lg';
                @endphp
                <a href="{{ route('news.index') }}" class="{{ request()->routeIs('news.index') ? $mobileTabActive : $mobileTabBase }}">
                    Berita
                </a>
            </div>

            <!-- Mobile Search -->
            <div class="px-4 pb-4 sm:hidden">
                <form method="get" action="{{ route('home') }}">
                    <div class="relative">

                        <input
                            type="text"
                            name="q"
                            value="{{ $q ?? '' }}"
                            placeholder="Cari Program"
                            class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2 text-sm text-gray-900 placeholder-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 mb-2"
                            style="padding-left: 5%"
                        />
                    </div>
                </form>
            </div>
        </div>
    </header>

    <!-- Mobile nav overlay -->
    <div id="mobile-nav" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div id="mobile-nav-backdrop" class="absolute inset-0 bg-black/30"></div>
        <div class="absolute inset-y-0 right-0 w-72 max-w-[85%] translate-x-0 bg-white shadow-lg">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                <div class="text-sm font-semibold text-gray-800">Navigasi</div>
                <button id="mobile-nav-close"
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 p-1.5 text-gray-700 hover:bg-gray-50"
                        aria-label="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                        <path
                              d="M6.225 4.811L4.81 6.225 10.586 12l-5.775 5.775 1.414 1.414L12 13.414l5.775 5.775 1.414-1.414L13.414 12l5.775-5.775-1.414-1.414L12 10.586 6.225 4.811z" />
                    </svg>
                </button>
            </div>
            <nav class="px-4 py-3">
                @include('partials.menu-links-mobile')
            </nav>
        </div>
    </div>

    <main class="mx-auto max-w-7xl px-4 py-8">
        <section class="mb-6">
            <!-- Hero slider -->
            <div class="relative rounded-md shadow">
                <div id="hero-track" class="flex snap-x snap-mandatory overflow-x-auto overflow-hidden scroll-smooth">
                    @php
                        $hasHero = isset($heroes) && $heroes->count() > 0;
                    @endphp
                    @if ($hasHero)
                        @foreach ($heroes as $h)
                            @php
                                $img = $h->image_url;
                                $title = $h->campaign->title ?? 'Campaign';
                                $slug = $h->campaign->slug ?? null;
                            @endphp
                            <a href="{{ $slug ? route('campaign.show', $slug) : '#' }}"
                               class="w-full flex-shrink-0 snap-start block">
                                @if ($img)
                                    <img src="{{ $img }}" class="h-full w-full object-cover" alt="{{ $title }}" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gray-100 text-gray-400">
                                        {{ $title }}</div>
                                @endif
                            </a>
                        @endforeach
                    @else
                        <div class="flex h-56 w-full items-center justify-center bg-gray-100 text-gray-400 md:h-72">Banner
                        </div>
                    @endif
                </div>
                @if ($hasHero && $heroes->count() > 1)
                    @php $totalHeroes = $heroes->count(); @endphp
                    <div id="hero-dots"
                         class="pointer-events-auto absolute bottom-2 left-0 right-0 flex justify-center gap-2">
                        @for ($i = 0; $i < $totalHeroes; $i++)
                            <button type="button" class="h-2 w-2 rounded-full bg-white/60 hover:bg-white ring-1 ring-black/10"
                                    data-hero-dot="{{ $i }}" aria-label="Slide {{ $i + 1 }}"></button>
                        @endfor
                    </div>
                    <button type="button" onclick="heroPrev()"
                            class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white"
                            aria-label="Sebelumnya">‹</button>
                    <button type="button" onclick="heroNext()"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white"
                            aria-label="Berikutnya">›</button>
                @endif
            </div>

            <!-- Category filters (wrap to next line, no horizontal scroll) -->
            <div class="mt-6">
                <div class="rounded-md bg-white p-3 shadow">
                    <div class="flex flex-wrap items-center gap-2">
                        @php $isAll = empty($activeCategory); @endphp
                        <a href="{{ route('home', ['q' => $q]) }}"
                           class="inline-flex items-center rounded-md px-3 py-2 text-sm ring-1 {{ $isAll ? 'ring-sky-400 text-sky-700 bg-sky-50' : 'ring-gray-200 text-gray-700 hover:ring-sky-300 hover:text-sky-700' }}">
                            Semua
                        </a>
                        @foreach ($categories as $cat)
                            @php $active = $activeCategory === $cat->slug; @endphp
                            <a href="{{ route('home', ['category' => $cat->slug, 'q' => $q]) }}"
                               class="inline-flex items-center rounded-md px-3 py-2 text-sm ring-1 {{ $active ? 'ring-sky-400 text-sky-700 bg-sky-50' : 'ring-gray-200 text-gray-700 hover:ring-sky-300 hover:text-sky-700' }}">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @if ($campaigns->count() === 0)
            <div class="rounded-md border border-gray-200 bg-white p-6 text-center text-gray-600 mb-5">Belum ada kampanye.</div>
        @else
            <div id="campaign-grid" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-5">
                @include('partials.campaign-cards', ['campaigns' => $campaigns])
            </div>

            <div class="mt-5 flex justify-center mb-5">
                <button id="load-more-btn"
                        data-next-page="{{ $campaigns->hasMorePages() ? ($campaigns->currentPage() + 1) : '' }}"
                        data-has-more="{{ $campaigns->hasMorePages() ? '1' : '0' }}"
                        class="{{ $campaigns->hasMorePages() ? '' : 'hidden' }} inline-flex items-center rounded-full bg-sky-600 px-5 py-2.5 text-sm font-medium text-white shadow hover:bg-sky-700">Muat
                    Lebih Banyak</button>
            </div>
        @endif

        @if (($latestNews ?? collect())->isNotEmpty())
            <section class="mt-10">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Berita Terbaru</h2>
                    <a href="{{ route('news.index') }}" class="text-sm text-sky-600 hover:underline">Lihat semua</a>
                </div>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($latestNews as $n)
                        <a href="{{ route('news.show', $n->slug) }}"
                           class="group block overflow-hidden rounded-md bg-white shadow hover:ring-1 hover:ring-sky-200">
                            <article>
                                @if ($n->cover_url)
                                    <img src="{{ $n->cover_url }}" alt="{{ $n->title }}" class="w-full object-cover" />
                                @endif
                                <div class="p-4">
                                    <h3 class="line-clamp-2 text-base font-semibold text-gray-900 group-hover:text-sky-700">
                                        {{ $n->title }}</h3>
                                    <div class="mt-1 text-xs text-gray-500">{{ optional($n->published_at)->format('d M Y') }}
                                    </div>
                                    @if ($n->excerpt)
                                        <p class="mt-2 line-clamp-3 text-sm text-gray-700">{{ $n->excerpt }}</p>
                                    @endif
                                </div>
                            </article>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <footer class="mt-12 border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 grid gap-8 md:grid-cols-3">
            <div class="text-sm text-gray-600">
                <h3 class="mb-2 text-base font-semibold text-gray-800">{{ $org->name ?? 'Organisasi' }}</h3>
                <p>{{ $org->summary ?? 'Platform donasi.' }}</p>
            </div>
            <div class="text-sm text-gray-600">
                <h3 class="mb-2 text-base font-semibold text-gray-800">Komitmen Kami</h3>
                <p>{{ $org->commitment ?? 'Transparansi dan akuntabilitas program.' }}</p>
                <div class="mt-3 flex items-center gap-3 text-gray-500">
                    @php $soc = $org->social_json ?? []; @endphp
                    @if (!empty($soc['instagram']))
                        <a href="{{ $soc['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"
                           class="hover:text-sky-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                <path
                                      d="M7.5 2.25A5.25 5.25 0 002.25 7.5v9A5.25 5.25 0 007.5 21.75h9a5.25 5.25 0 005.25-5.25v-9A5.25 5.25 0 0016.5 2.25h-9zM12 8.25a3.75 3.75 0 110 7.5 3.75 3.75 0 010-7.5zm6-1.5a.75.75 0 110 1.5.75.75 0 010-1.5z" />
                            </svg>
                        </a>
                    @endif
                    @if (!empty($soc['youtube']))
                        <a href="{{ $soc['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube"
                           class="hover:text-sky-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                <path
                                      d="M10.788 3.21c.38-.987 1.734-.987 2.114 0a1.724 1.724 0 002.623.878c.914-.593 2.1.293 1.868 1.34a1.724 1.724 0 001.243 2.06c1.04.278 1.227 1.665.332 2.33a1.724 1.724 0 000 2.804c.895.665.708 2.052-.332 2.33a1.724 1.724 0 00-1.243 2.06c.232 1.047-.954 1.933-1.868 1.34a1.724 1.724 0 00-2.623.878c-.38.987-1.734.987-2.114 0a1.724 1.724 0 00-2.623-.878c-.914.593-2.1-.293-1.868-1.34a1.724 1.724 0 00-1.243-2.06c-1.04-.278-1.227-1.665-.332-2.33a1.724 1.724 0 000-2.804c-.895-.665-.708-2.052.332-2.33A1.724 1.724 0 005.297 5.43c.232-1.047 1.418-1.933 2.332-1.34.89.585 2.07-.288 2.623-.88z" />
                            </svg>
                        </a>
                    @endif
                    @if (!empty($soc['facebook']))
                        <a href="{{ $soc['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"
                           class="hover:text-sky-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                <path
                                      d="M13.5 2.25a6.75 6.75 0 00-6.75 6.75v2.25H4.5a.75.75 0 00-.75.75v3a.75.75 0 00.75.75h2.25V21a.75.75 0 00.75.75h3a.75.75 0 00.75-.75v-5.25H14.6a.75.75 0 00.74-.63l.38-3a.75.75 0 00-.74-.87H11.25V9a2.25 2.25 0 012.25-2.25H15a.75.75 0 00.75-.75V3a.75.75 0 00-.75-.75h-1.5z" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
            <div class="text-sm text-gray-600">
                <h3 class="mb-2 text-base font-semibold text-gray-800">Alamat</h3>
                <p class="mb-2">{{ $org->address ?? '-' }}</p>
                @if (!empty($org?->lat) && !empty($org?->lng))
                    <a href="https://maps.google.com/?q={{ $org->lat }},{{ $org->lng }}" target="_blank" rel="noopener"
                       class="text-sky-600 hover:underline">Lihat peta lokasi</a>
                @endif
            </div>
        </div>
        <div class="border-t border-gray-200 py-6 text-center text-sm text-gray-500">&copy; {{ date('Y') }} Platform
            Donasi Online</div>
    </footer>

    <a href="https://wa.me/628123456789" target="_blank"
       class="fixed bottom-5 right-5 inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-500 text-white shadow-lg hover:bg-green-600"
       aria-label="WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="h-6 w-6">
            <path
                  d="M20.52 3.48A11.93 11.93 0 0012.05 0C5.53.02.25 5.29.27 11.81a11.76 11.76 0 001.64 6.07L0 24l6.3-1.82a11.86 11.86 0 005.73 1.49h.01c6.52 0 11.8-5.27 11.82-11.79a11.8 11.8 0 00-3.34-8.4zM12.04 21.2h-.01a9.38 9.38 0 01-4.78-1.3l-.34-.2-3.74 1.08 1-3.64-.22-.37a9.36 9.36 0 01-1.44-4.98c-.02-5.16 4.18-9.38 9.34-9.4 2.5 0 4.85.97 6.62 2.74a9.28 9.28 0 012.75 6.64c-.02 5.15-4.23 9.37-9.38 9.37zm5.14-7.02c-.28-.14-1.68-.83-1.94-.92-.26-.1-.45-.14-.65.14-.2.27-.75.92-.92 1.11-.17.2-.34.22-.62.08-.28-.15-1.18-.43-2.25-1.37-.83-.74-1.39-1.65-1.56-1.93-.16-.27-.02-.41.12-.55.12-.12.27-.31.4-.46.13-.15.17-.26.26-.43.09-.17.04-.32-.02-.46-.06-.14-.65-1.57-.89-2.15-.23-.56-.47-.49-.65-.5l-.55-.01c-.19 0-.5.07-.76.33-.26.27-1 1-1 2.43 0 1.43 1.03 2.81 1.18 3 .14.2 2.03 3.1 4.93 4.35.69.3 1.22.48 1.64.62.69.22 1.32.19 1.82.12.55-.08 1.68-.69 1.92-1.36.24-.67.24-1.24.17-1.36-.07-.12-.25-.19-.53-.33z" />
        </svg>
    </a>

    <script>
        // Mobile nav toggle
        (function () {
            const openBtn = document.getElementById('mobile-nav-open');
            const closeBtn = document.getElementById('mobile-nav-close');
            const panel = document.getElementById('mobile-nav');
            const backdrop = document.getElementById('mobile-nav-backdrop');
            const open = () => { if (!panel) return; panel.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); };
            const close = () => { if (!panel) return; panel.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); };
            openBtn && openBtn.addEventListener('click', (e) => { e.preventDefault(); open(); });
            closeBtn && closeBtn.addEventListener('click', (e) => { e.preventDefault(); close(); });
            backdrop && backdrop.addEventListener('click', close);
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
        })();
        // Hero carousel
        const heroTrack = document.getElementById('hero-track');
        let heroSlides = heroTrack ? Array.from(heroTrack.querySelectorAll('a')) : [];
        let heroIndex = 0;
        let heroTimer = null;
        const heroIntervalMs = 5000;

        function heroUpdateDots() {
            const dots = document.querySelectorAll('[data-hero-dot]');
            dots.forEach((d, i) => {
                if (i === heroIndex) {
                    d.classList.add('bg-white');
                    d.classList.remove('bg-white/60');
                } else {
                    d.classList.remove('bg-white');
                    d.classList.add('bg-white/60');
                }
            });
        }

        function heroGo(i) {
            if (!heroTrack || heroSlides.length === 0) return;
            heroIndex = (i + heroSlides.length) % heroSlides.length;
            const target = heroSlides[heroIndex];
            heroTrack.scrollTo({ left: target.offsetLeft, behavior: 'smooth' });
            heroUpdateDots();
        }

        function heroNext() { heroGo(heroIndex + 1); }
        function heroPrev() { heroGo(heroIndex - 1); }

        function heroStart() {
            if (heroTimer || heroSlides.length <= 1) return;
            heroTimer = setInterval(heroNext, heroIntervalMs);
        }
        function heroStop() {
            if (heroTimer) {
                clearInterval(heroTimer);
                heroTimer = null;
            }
        }

        // Init
        if (heroTrack && heroSlides.length > 0) {
            heroUpdateDots();
            heroStart();
            heroTrack.addEventListener('mouseenter', heroStop);
            heroTrack.addEventListener('mouseleave', heroStart);
            window.addEventListener('resize', () => heroGo(heroIndex));
            document.addEventListener('visibilitychange', () => document.hidden ? heroStop() : heroStart());
            // Dots
            document.querySelectorAll('[data-hero-dot]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const i = parseInt(btn.getAttribute('data-hero-dot') || '0', 10);
                    heroStop();
                    heroGo(i);
                    heroStart();
                });
            });
        }

        // Load more campaigns
        const loadBtn = document.getElementById('load-more-btn');
        const grid = document.getElementById('campaign-grid');
        async function loadMore() {
            if (!loadBtn || loadBtn.dataset.hasMore !== '1') return;
            const nextPage = loadBtn.dataset.nextPage;
            const params = new URLSearchParams({
                page: nextPage,
                perPage: '{{ $perPage ?? 6 }}',
                category: '{{ $activeCategory ?? '' }}',
                q: '{{ $q ?? '' }}',
            });
            loadBtn.disabled = true;
            loadBtn.textContent = 'Memuat...';
            try {
                const res = await fetch(`{{ route('home.chunk') }}?${params.toString()}`);
                const data = await res.json();
                if (data?.html) {
                    const tmp = document.createElement('div');
                    tmp.innerHTML = data.html;
                    tmp.childNodes.forEach(n => grid.appendChild(n));
                }
                if (data?.hasMore) {
                    loadBtn.dataset.nextPage = data.nextPage;
                    loadBtn.dataset.hasMore = '1';
                    loadBtn.disabled = false;
                    loadBtn.textContent = 'Muat Lebih Banyak';
                } else {
                    loadBtn.classList.add('hidden');
                }
            } catch (e) {
                console.error(e);
                loadBtn.disabled = false;
                loadBtn.textContent = 'Muat Lebih Banyak';
            }
        }
        loadBtn && loadBtn.addEventListener('click', loadMore);
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButton = document.getElementById('mobile-nav-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');

        toggleButton.addEventListener('click', function() {
            const isExpanded = mobileMenu.classList.contains('hidden');

            if (isExpanded) {
                mobileMenu.classList.remove('hidden');
                menuIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
                toggleButton.setAttribute('aria-expanded', 'true');
            } else {
                mobileMenu.classList.add('hidden');
                menuIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
                toggleButton.setAttribute('aria-expanded', 'false');
            }
        });
    });
    </script>
</body>

</html>
