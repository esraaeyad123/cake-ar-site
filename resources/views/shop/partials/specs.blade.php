{{-- المواصفات بنفس ترتيب موقع جوماكيك: طبقات، إنش، دور، أشخاص --}}
<div class="specs" @isset($id) id="{{ $id }}" @endisset>
    <span class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M12 3 3 8l9 5 9-5z"/><path d="m3 13 9 5 9-5"/></svg><span data-spec="layers">{{ $size['min_layers'] }} طبقة</span></span>
    <span class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><path d="M3 17 17 3l4 4L7 21z"/><path d="m7 13 2 2M10 10l2 2M13 7l2 2"/></svg><span data-spec="inch">{{ $size['inch'] }} إنش</span></span>
    <span class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"><rect x="7" y="4" width="10" height="6" rx="1"/><rect x="4" y="10" width="16" height="9" rx="1"/></svg><span>1 دور</span></span>
    <span class="spec"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="8" r="3"/><path d="M3 20c0-3 3-5 6-5s6 2 6 5"/><circle cx="17" cy="9" r="2.4"/><path d="M16 15c3 0 5 2 5 5"/></svg><span data-spec="people">{{ $size['people'] }}</span></span>
</div>
