@extends('shop.layout')

@section('title', 'المتجر')

@section('styles')
        main { max-width: 900px; margin: 0 auto; padding: 8px 12px 40px; }
        h1 { font-size: 18px; margin: 12px 4px 2px; border-inline-start: 4px solid var(--pink); padding-inline-start: 8px; }
        .sub { font-size: 12px; color: var(--muted); margin: 0 16px 12px; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        @media (min-width: 700px) { .grid { grid-template-columns: repeat(3, 1fr); } }
        .card { display: block; text-decoration: none; border-radius: 16px; overflow: hidden; background: #fff; border: 1px solid #F3E3E9; }
        .card .pic { position: relative; aspect-ratio: 1; background: #FAF3F5; }
        .card .pic img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .card .pic .badge { position: absolute; top: 8px; right: 8px; }
        .card .body { padding: 8px 10px 10px; }
        .card .sn { font-size: 11px; color: var(--muted); font-weight: 700; }
        .card .name { font-size: 13px; font-weight: 800; margin: 1px 0 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .card .price-row { display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; }
        .card .price { font-size: 17px; font-weight: 800; }
        .card .specs { margin-top: 7px; border-top: 1px solid #F5EBEF; padding-top: 7px; }
        .card .spec { font-size: 10px; }
        .empty { text-align: center; color: var(--muted); padding: 40px 10px; font-weight: 700; }
@endsection

@section('top')
    @include('shop.partials.header')
@endsection

@section('content')
    <main>
        <h1>كيك جاهز</h1>
        <p class="sub">اختاري الكيكة، وحددي المقاس والنكهة والحشوة</p>

        @if (! $products)
            <div class="empty">المتجر مو متاح الحين، جربي بعد شوي.</div>
        @endif

        <div class="grid">
            @foreach ($products as $product)
                @php
                    $size = $product['sizes'][0];
                    $final = \App\Services\ShopProducts::finalPrice($size['price'], $size['discount']);
                @endphp
                <a class="card" href="{{ route('shop.show', $product['id']) }}">
                    <div class="pic">
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] ?? 'S.N: '.$product['id'] }}" loading="lazy">
                        @if ($size['discount'] && $size['discount']['type'] === 'percent')
                            <span class="badge">خصم {{ (int) $size['discount']['amount'] }}%</span>
                        @endif
                    </div>
                    <div class="body">
                        <div class="sn">S.N: {{ $product['id'] }}</div>
                        @if ($product['name'])
                            <div class="name">{{ $product['name'] }}</div>
                        @endif
                        <div class="price-row">
                            <span class="price">{{ $final }}@include('shop.partials.riyal')</span>
                            @if ($final < $size['price'])
                                <span class="old">{{ (int) $size['price'] }}</span>
                            @endif
                        </div>
                        <div class="tax">شامل الضريبة</div>
                        @include('shop.partials.specs', ['size' => $size])
                    </div>
                </a>
            @endforeach
        </div>
    </main>
@endsection
