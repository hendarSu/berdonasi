@php
    use App\Models\Menu;
    $menu = Menu::query()->where('code','main')->first();
    $items = $menu ? $menu->items()->where('active', true)->with('page')->get() : collect();
    $makeUrl = function($it) {
        if (!empty($it->url)) return $it->url;
        if ($it->page) return route('page.show', $it->page->slug);
        return '#';
    };
    $renderItems = function($items, $tabBase, $tabActive) use ($makeUrl) {
        foreach ($items as $it) {
            $url = $makeUrl($it);
            $isActive = rtrim(url()->current(), '/') === rtrim($url, '/');
            echo '<a href="' . e($url) . '" class="' . e($isActive ? $tabActive : $tabBase) . '">'
                . e($it->title) . '</a>';
        }
    };
@endphp

@if ($items->isNotEmpty())
    {!! $renderItems($items, $tabBase ?? '', $tabActive ?? '') !!}
@else
    {{-- Fallback to default links when no menu configured --}}
    @php
        $def = [
            ['title' => 'Program', 'url' => route('program.index'), 'active' => (request()->routeIs('program.*') || request()->routeIs('home'))],
            ['title' => 'Berita', 'url' => route('news.index'), 'active' => request()->routeIs('news.*')],
        ];
    @endphp
    @foreach ($def as $it)
        <a href="{{ $it['url'] }}" class="{{ $it['active'] ? ($tabActive ?? '') : ($tabBase ?? '') }}">{{ $it['title'] }}</a>
    @endforeach
@endif

