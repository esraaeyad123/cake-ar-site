<header class="site-header">
    <a href="https://jomacake.com"><img src="{{ asset('images/icons/logo.png') }}" alt="JOMACAKE" class="logo"></a>
    <nav>
        <a href="https://jomacake.com">{{ __('cake_ar.common.nav_home') }}</a>
        <a href="{{ route('shop.index') }}" class="active">{{ __('cake_ar.common.nav_shop') }}</a>
        <a href="{{ route('special-order.show') }}">{{ __('cake_ar.common.nav_custom_cake') }}</a>
    </nav>
    <div class="header-end">
    @include('partials.lang-switch')
    <a href="https://jomacake.com/cart" class="cart" aria-label="{{ __('cake_ar.common.cart') }}">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><path d="M6 6 5 3H2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg>
    </a>
    </div>
</header>
<div class="sample-tag">{{ __('cake_ar.common.demo_tag') }}</div>
