<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>
        {{ env('APP_NAME') }} — Kampanye</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-white text-gray-900">
    <header class="bg-white border-b border-gray-200">
        <div class="mx-auto max-w-7xl px-4 py-3">
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="shrink-0">
                    @if (!empty($org?->logo_url))
                        <img src="{{ $org->logo_url }}" alt="{{ $org->name }}" class="h-8 w-auto" />
                    @else
                        <span class="text-xl font-bold text-sky-600">{{ strtoupper(substr(env('APP_NAME', 'BN'),0,2)) }}</span>
                    @endif
                </a>
                <form method="get" action="{{ route('home') }}" class="flex-1">
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari Program" class="w-full rounded-full border border-gray-300 bg-white px-5 py-2.5 text-sm focus:border-sky-400 focus:outline-none focus:ring-1 focus:ring-sky-400" />
                </form>
            </div>
        </div>
    </header>

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
                            <a href="{{ $slug ? route('campaign.show', $slug) : '#' }}" class="w-full flex-shrink-0 snap-start block">
                                @if ($img)
                                    <img src="{{ $img }}" class="h-full w-full object-cover" alt="{{ $title }}" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gray-100 text-gray-400">{{ $title }}</div>
                                @endif
                            </a>
                        @endforeach
                    @else
                        <div class="flex h-56 w-full items-center justify-center bg-gray-100 text-gray-400 md:h-72">Banner</div>
                    @endif
                </div>
                @if ($hasHero && $heroes->count() > 1)
                    @php $totalHeroes = $heroes->count(); @endphp
                    <div id="hero-dots" class="pointer-events-auto absolute bottom-2 left-0 right-0 flex justify-center gap-2">
                        @for ($i = 0; $i < $totalHeroes; $i++)
                            <button type="button" class="h-2 w-2 rounded-full bg-white/60 hover:bg-white ring-1 ring-black/10" data-hero-dot="{{ $i }}" aria-label="Slide {{ $i + 1 }}"></button>
                        @endfor
                    </div>
                    <button type="button" onclick="heroPrev()" class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white" aria-label="Sebelumnya">‹</button>
                    <button type="button" onclick="heroNext()" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-2 shadow hover:bg-white" aria-label="Berikutnya">›</button>
                @endif
            </div>

            <!-- Category filters (wrap to next line, no horizontal scroll) -->
            <div class="mt-6">
                <div class="rounded-md bg-white p-3 shadow">
                    <div class="flex flex-wrap items-center gap-2">
                        @php $isAll = empty($activeCategory); @endphp
                        <a href="{{ route('home', ['q' => $q]) }}" class="inline-flex items-center rounded-md px-3 py-2 text-sm ring-1 {{ $isAll ? 'ring-sky-400 text-sky-700 bg-sky-50' : 'ring-gray-200 text-gray-700 hover:ring-sky-300 hover:text-sky-700' }}">
                            Semua
                        </a>
                        @foreach ($categories as $cat)
                            @php $active = $activeCategory === $cat->slug; @endphp
                            <a href="{{ route('home', ['category' => $cat->slug, 'q' => $q]) }}" class="inline-flex items-center rounded-md px-3 py-2 text-sm ring-1 {{ $active ? 'ring-sky-400 text-sky-700 bg-sky-50' : 'ring-gray-200 text-gray-700 hover:ring-sky-300 hover:text-sky-700' }}">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        @if ($campaigns->count() === 0)
            <div class="rounded-md border border-gray-200 bg-white p-6 text-center text-gray-600">Belum ada kampanye.</div>
        @else
            <div id="campaign-grid" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @include('partials.campaign-cards', ['campaigns' => $campaigns])
            </div>

            <div class="mt-8 flex justify-center">
                <button id="load-more-btn" data-next-page="{{ $campaigns->hasMorePages() ? ($campaigns->currentPage() + 1) : '' }}" data-has-more="{{ $campaigns->hasMorePages() ? '1' : '0' }}" class="{{ $campaigns->hasMorePages() ? '' : 'hidden' }} inline-flex items-center rounded-full bg-sky-600 px-5 py-2.5 text-sm font-medium text-white shadow hover:bg-sky-700">Muat Lebih Banyak</button>
            </div>
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
                    <a href="{{ $soc['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram" class="hover:text-sky-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M7.5 2.25A5.25 5.25 0 002.25 7.5v9A5.25 5.25 0 007.5 21.75h9a5.25 5.25 0 005.25-5.25v-9A5.25 5.25 0 0016.5 2.25h-9zM12 8.25a3.75 3.75 0 110 7.5 3.75 3.75 0 010-7.5zm6-1.5a.75.75 0 110 1.5.75.75 0 010-1.5z"/></svg>
                    </a>
                    @endif
                    @if (!empty($soc['youtube']))
                    <a href="{{ $soc['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube" class="hover:text-sky-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M10.788 3.21c.38-.987 1.734-.987 2.114 0a1.724 1.724 0 002.623.878c.914-.593 2.1.293 1.868 1.34a1.724 1.724 0 001.243 2.06c1.04.278 1.227 1.665.332 2.33a1.724 1.724 0 000 2.804c.895.665.708 2.052-.332 2.33a1.724 1.724 0 00-1.243 2.06c.232 1.047-.954 1.933-1.868 1.34a1.724 1.724 0 00-2.623.878c-.38.987-1.734.987-2.114 0a1.724 1.724 0 00-2.623-.878c-.914.593-2.1-.293-1.868-1.34a1.724 1.724 0 00-1.243-2.06c-1.04-.278-1.227-1.665-.332-2.33a1.724 1.724 0 000-2.804c-.895-.665-.708-2.052.332-2.33A1.724 1.724 0 005.297 5.43c.232-1.047 1.418-1.933 2.332-1.34.89.585 2.07-.288 2.623-.88z"/></svg>
                    </a>
                    @endif
                    @if (!empty($soc['facebook']))
                    <a href="{{ $soc['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook" class="hover:text-sky-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M13.5 2.25a6.75 6.75 0 00-6.75 6.75v2.25H4.5a.75.75 0 00-.75.75v3a.75.75 0 00.75.75h2.25V21a.75.75 0 00.75.75h3a.75.75 0 00.75-.75v-5.25H14.6a.75.75 0 00.74-.63l.38-3a.75.75 0 00-.74-.87H11.25V9a2.25 2.25 0 012.25-2.25H15a.75.75 0 00.75-.75V3a.75.75 0 00-.75-.75h-1.5z"/></svg>
                    </a>
                    @endif
                </div>
            </div>
            <div class="text-sm text-gray-600">
                <h3 class="mb-2 text-base font-semibold text-gray-800">Alamat</h3>
                <p class="mb-2">{{ $org->address ?? '-' }}</p>
                @if (!empty($org?->lat) && !empty($org?->lng))
                    <a href="https://maps.google.com/?q={{ $org->lat }},{{ $org->lng }}" target="_blank" rel="noopener" class="text-sky-600 hover:underline">Lihat peta lokasi</a>
                @endif
            </div>
        </div>
        <div class="border-t border-gray-200 py-6 text-center text-sm text-gray-500">&copy; {{ date('Y') }} Platform Donasi Online</div>
    </footer>

    <a href="https://wa.me/628123456789" target="_blank" class="fixed bottom-5 right-5 inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-500 text-white shadow-lg hover:bg-green-600" aria-label="WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="h-6 w-6"><path d="M20.52 3.48A11.93 11.93 0 0012.05 0C5.53.02.25 5.29.27 11.81a11.76 11.76 0 001.64 6.07L0 24l6.3-1.82a11.86 11.86 0 005.73 1.49h.01c6.52 0 11.8-5.27 11.82-11.79a11.8 11.8 0 00-3.34-8.4zM12.04 21.2h-.01a9.38 9.38 0 01-4.78-1.3l-.34-.2-3.74 1.08 1-3.64-.22-.37a9.36 9.36 0 01-1.44-4.98c-.02-5.16 4.18-9.38 9.34-9.4 2.5 0 4.85.97 6.62 2.74a9.28 9.28 0 012.75 6.64c-.02 5.15-4.23 9.37-9.38 9.37zm5.14-7.02c-.28-.14-1.68-.83-1.94-.92-.26-.1-.45-.14-.65.14-.2.27-.75.92-.92 1.11-.17.2-.34.22-.62.08-.28-.15-1.18-.43-2.25-1.37-.83-.74-1.39-1.65-1.56-1.93-.16-.27-.02-.41.12-.55.12-.12.27-.31.4-.46.13-.15.17-.26.26-.43.09-.17.04-.32-.02-.46-.06-.14-.65-1.57-.89-2.15-.23-.56-.47-.49-.65-.5l-.55-.01c-.19 0-.5.07-.76.33-.26.27-1 1-1 2.43 0 1.43 1.03 2.81 1.18 3 .14.2 2.03 3.1 4.93 4.35.69.3 1.22.48 1.64.62.69.22 1.32.19 1.82.12.55-.08 1.68-.69 1.92-1.36.24-.67.24-1.24.17-1.36-.07-.12-.25-.19-.53-.33z"/></svg>
    </a>

    <script>
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
        async function loadMore(){
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
</body>
</html>
