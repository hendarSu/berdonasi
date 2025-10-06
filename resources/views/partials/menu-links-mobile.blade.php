@php
    use App\Models\Menu;
    $menu = Menu::query()->where('code','main')->first();
    $items = $menu ? $menu->items()->where('active', true)->with('page')->get() : collect();
    $makeUrl = function($it) {
        if (!empty($it->url)) return $it->url;
        if ($it->page) return route('page.show', $it->page->slug);
        return '#';
    };
@endphp

@if ($items->isNotEmpty())
    @foreach ($items as $it)
        @php $url = $makeUrl($it); $isActive = rtrim(url()->current(), '/') === rtrim($url, '/'); @endphp
        <a href="{{ $url }}"
           class="block rounded-md px-3 py-2 text-sm {{ $isActive ? 'bg-sky-50 text-sky-700' : 'text-gray-700 hover:bg-gray-50' }}">{{ $it->title }}</a>
    @endforeach
@else
    <a href="{{ route('program.index') }}"
       class="block rounded-md px-3 py-2 text-sm {{ (request()->routeIs('program.*') || request()->routeIs('home')) ? 'bg-sky-50 text-sky-700' : 'text-gray-700 hover:bg-gray-50' }}">Program</a>
    <a href="{{ route('news.index') }}"
       class="mt-1 block rounded-md px-3 py-2 text-sm {{ request()->routeIs('news.*') ? 'bg-sky-50 text-sky-700' : 'text-gray-700 hover:bg-gray-50' }}">Berita</a>
@endif

