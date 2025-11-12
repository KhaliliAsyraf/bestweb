<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        // You can add filters if needed
        return Product::query()->select('id', 'name', 'price', 'created_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Price',
            'Created At',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->price,
            $product->created_at->toDateString(),
        ];
    }
}
