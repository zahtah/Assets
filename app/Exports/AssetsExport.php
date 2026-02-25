<?php

namespace App\Exports;

// use App\Models\Asset;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\WithMapping;

// class AssetsExport implements FromCollection, WithHeadings, WithMapping
// {
//     public function collection()
//     {
//         return Asset::with('user')->get();
//     }

//     public function headings(): array
//     {
//         return [
//             'نام کاربر',
//             'نام مال',
//             'شماره اموال',
//             'تاریخ ثبت',
//         ];
//     }

//     public function map($asset): array
//     {
//         return [
//             $asset->user->name ?? '',
//             $asset->title,
//             $asset->asset_number,
//             $asset->created_at,
//         ];
//     }
// }