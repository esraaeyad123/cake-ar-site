{{-- زر تبديل اللغة (ع / EN): يرجع لنفس الصفحة باللغة الثانية --}}
@php($other = app()->getLocale() === 'en' ? 'ar' : 'en')
<style>
    .header-end { display: flex; align-items: center; gap: 10px; }
    .lang-switch { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 30px; padding: 0 8px; border-radius: 15px;
        border: 1.5px solid #F4C0D1; color: #993556; background: #fff; font-size: 13px; font-weight: 800; text-decoration: none; font-family: inherit; }
    .lang-switch:hover { background: #FBEAF0; }
</style>
<a href="{{ request()->fullUrlWithQuery(['lang' => $other]) }}" class="lang-switch" hreflang="{{ $other }}" lang="{{ $other }}" title="{{ $other === 'en' ? 'English' : 'العربية' }}">{{ $other === 'en' ? 'EN' : 'ع' }}</a>
