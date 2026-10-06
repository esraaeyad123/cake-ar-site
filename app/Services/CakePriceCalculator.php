<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * يحسب سعر الكيكة من مفاتيح الاختيارات فقط — لا يقبل أي سعر قادم من المتصفح.
 */
class CakePriceCalculator
{
    /**
     * @param  array{size?: string, flavor?: string, filling?: string, layers?: int|string, color?: string}  $selection
     * @return array{items: list<array{group: string, label: string, price: int}>, total: int}
     */
    public function quote(array $selection): array
    {
        $size = $this->find('sizes', 'key', $selection['size'] ?? null, 'size');
        $flavor = $this->find('flavors', 'key', $selection['flavor'] ?? null, 'flavor');
        $filling = $this->find('fillings', 'key', $selection['filling'] ?? null, 'filling');
        $layers = $this->find('layers', 'count', (int) ($selection['layers'] ?? 0), 'layers');
        $color = $this->find('colors', 'key', $selection['color'] ?? null, 'color');

        $items = [
            ['group' => 'الحجم', 'label' => $size['label'].' إنش', 'price' => $size['price']],
            ['group' => 'النكهة', 'label' => $flavor['name'], 'price' => $flavor['price']],
            ['group' => 'الحشوة', 'label' => $filling['name'], 'price' => $filling['price']],
            ['group' => 'الطبقات', 'label' => $layers['count'] === 2 ? 'طبقتين' : $layers['count'].' طبقات', 'price' => $layers['price']],
            ['group' => 'اللون', 'label' => $color['name'], 'price' => $color['price']],
        ];

        return [
            'items' => $items,
            'total' => array_sum(array_column($items, 'price')),
        ];
    }

    private function find(string $list, string $field, mixed $value, string $input): array
    {
        $option = Arr::first(
            config("cake_customizer.$list"),
            fn (array $option) => $option[$field] === $value
        );

        if ($option === null) {
            throw ValidationException::withMessages([$input => 'اختيار غير صالح']);
        }

        return $option;
    }
}
