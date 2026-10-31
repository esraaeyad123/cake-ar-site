        // صور النكهات (كيكة كاملة) والحشوات (قطعة بإسفنج النكهة المختارة)، حسب اسم الصنف من جوماكيك
        const FLAVOR_PICS = "{{ asset('images/flavors') }}";
        const FILLING_PICS = "{{ asset('images/fillings') }}";
        function flavorKey(item) {
            const name = `${item?.name || ''} ${item?.title_en || ''}`;
            if (/شوكول|chocolate/i.test(name)) return 'chocolate';
            if (/فانيل|vanilla/i.test(name)) return 'vanilla';
            return null;
        }
        function fillingKey(item) {
            const name = `${item?.name || ''} ${item?.title_en || ''}`;
            if (/نوتيلا|nutella/i.test(name)) return 'nutella';
            if (/فستق|pistachio/i.test(name)) return 'pistachio';
            // "ويب كريم ساده (وردي/ازرق)" = طبقة كريمة ملونة، و"ويب كريم مع فراوله" = كريمة + مربى فراولة
            if (/ازرق|أزرق|blue/i.test(name)) return 'blue';
            if (/وردي|pink/i.test(name)) return 'pink';
            if (/مربى|jam|فراول|strawberry/i.test(name)) return 'jam';
            if (/ساد|plain/i.test(name)) return 'plain';
            return null;
        }
        const flavorPic = item => flavorKey(item) ? `${FLAVOR_PICS}/${flavorKey(item)}.webp` : (item?.image || null);
        // الحشوة بإسفنج النكهة المختارة (فانيلا إذا النكهة ما لها صورة)
        const fillingPic = (item, flavor) => fillingKey(item) ? `${FILLING_PICS}/${flavorKey(flavor) || 'vanilla'}-${fillingKey(item)}.webp` : (item?.image || null);
