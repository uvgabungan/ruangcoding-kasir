<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OutdoorProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoriesWithProducts = [
            'Tenda' => [
                ['name' => 'Tenda Family Kap 4-5 Double Layer', 'price' => 130000],
                ['name' => 'Tenda Kap 6-7 Double Layer', 'price' => 80000],
                ['name' => 'Tenda Kap 4-5 Double Layer', 'price' => 50000],
                ['name' => 'Tenda Kap 3-4 Double Layer', 'price' => 45000],
                ['name' => 'Tenda Kap 2 Double Layer', 'price' => 40000],
                ['name' => 'Tenda Kap 2 Single Layer', 'price' => 25000],
            ],
            'Flysheet' => [
                ['name' => 'Paket Flysheet', 'price' => 45000],
                ['name' => 'Flysheet 3x3m', 'price' => 15000],
                ['name' => 'Flysheet 3x4m', 'price' => 20000],
                ['name' => 'Flysheet 4x6m', 'price' => 25000],
            ],
            'Backpack' => [
                ['name' => 'Raincover', 'price' => 5000],
                ['name' => 'Hydropack Frostop', 'price' => 15000],
                ['name' => 'Hydropack Monte', 'price' => 20000],
                ['name' => 'Daypack', 'price' => 25000],
                ['name' => 'Tas Carrier 45L', 'price' => 30000],
                ['name' => 'Tas Carrier 60L', 'price' => 35000],
                ['name' => 'Tas Baby Carrier', 'price' => 125000],
            ],
            'Pakaian' => [
                ['name' => 'Topi Rimba/Running', 'price' => 5000],
                ['name' => 'Kupluk Outdoor', 'price' => 10000],
                ['name' => 'Sarung Tangan', 'price' => 10000],
                ['name' => 'Jaket Anti UV Slimfit', 'price' => 20000],
                ['name' => 'Jaket Anti UV', 'price' => 15000],
                ['name' => 'Jaket Outdoor/Gorpcore/Puffer', 'price' => 30000],
                ['name' => 'Rompi Outdoor', 'price' => 25000],
                ['name' => 'Celana Cargo/Trackpants', 'price' => 20000],
                ['name' => 'Gaiter', 'price' => 15000],
                ['name' => 'Ikat Kepala', 'price' => 10000],
                ['name' => 'Kacamata Outdoor', 'price' => 5000],
                ['name' => 'Kacamata Outdoor + Wadah', 'price' => 10000],
            ],
            'Sepatu' => [
                ['name' => 'Sepatu Gunung', 'price' => 30000],
                ['name' => 'Sepatu Trail Running', 'price' => 35000],
            ],
            'Alat Tidur' => [
                ['name' => 'Tikar Piknik', 'price' => 15000],
                ['name' => 'Matras Spon', 'price' => 5000],
                ['name' => 'Matras Foil', 'price' => 10000],
                ['name' => 'Kasur Lipat', 'price' => 25000],
                ['name' => 'Bantal Tiup', 'price' => 5000],
            ],
            'Alat Masak' => [
                ['name' => 'Windshield', 'price' => 5000],
                ['name' => 'Jerigen Lipat 5L', 'price' => 5000],
                ['name' => 'Jerigen Lipat 10L', 'price' => 10000],
                ['name' => 'Egg Holder', 'price' => 5000],
                ['name' => 'Nesting Kecil', 'price' => 10000],
                ['name' => 'Nesting Besar', 'price' => 15000],
                ['name' => 'Kompor Kotak', 'price' => 10000],
                ['name' => 'Kompor Bunga', 'price' => 15000],
                ['name' => 'Kompor Portable', 'price' => 25000],
                ['name' => 'Gas Isi Ulang', 'price' => 8000],
                ['name' => 'Gas Sewa', 'price' => 10000],
                ['name' => 'Gas Baru', 'price' => 20000],
            ],
            'Alat Makan' => [
                ['name' => 'Sendok Set (Isi 3)', 'price' => 5000],
                ['name' => 'Gelas Set (Isi 4)', 'price' => 10000],
                ['name' => 'Gelas Gagang Lipat', 'price' => 5000],
                ['name' => 'Mangkok Plastik (Isi 4)', 'price' => 10000],
                ['name' => 'Piring Plastik (Isi 4)', 'price' => 10000],
                ['name' => 'Tumbler Termos', 'price' => 15000],
            ],
            'Alat Grill' => [
                ['name' => 'Sumpit Kayu', 'price' => 1000],
                ['name' => 'Sumpit Plastik', 'price' => 2000],
                ['name' => 'Capitan', 'price' => 5000],
                ['name' => 'Suki Pan + Centong', 'price' => 25000],
                ['name' => 'Grillpan A', 'price' => 20000],
                ['name' => 'Grillpan B', 'price' => 25000],
                ['name' => 'Grillpan C', 'price' => 30000],
                ['name' => 'Paket Sukipan', 'price' => 55000],
                ['name' => 'Paket Grill A', 'price' => 65000],
                ['name' => 'Paket Grill B', 'price' => 75000],
                ['name' => 'Paket Grill C', 'price' => 85000],
            ],
            'Alat Elektronik' => [
                ['name' => 'Powerbank 12000mAh', 'price' => 15000],
                ['name' => 'Powerbank 24000mAh', 'price' => 25000],
                ['name' => 'Headlamp Baterai', 'price' => 5000],
                ['name' => 'Headlamp Baterai + Baterai', 'price' => 15000],
                ['name' => 'Headlamp Cas', 'price' => 10000],
                ['name' => 'Headlamp Cas Sensor', 'price' => 15000],
                ['name' => 'Tripod Bluetooth', 'price' => 15000],
                ['name' => 'Handy Talky', 'price' => 25000],
                ['name' => 'Senter', 'price' => 10000],
                ['name' => 'Lampu Tenda Cas', 'price' => 10000],
                ['name' => 'Lampu Tenda Bapau', 'price' => 5000],
                ['name' => 'Lampu Tenda Bapau + Baterai', 'price' => 15000],
                ['name' => 'Lampu Darurat', 'price' => 20000],
                ['name' => 'Terminal Colokan 15m', 'price' => 25000],
                ['name' => 'Terminal Colokan 10m', 'price' => 20000],
            ],
            'Lainnya' => [
                ['name' => 'Cangkul Lipat', 'price' => 5000],
                ['name' => 'Tracking Pole Karet', 'price' => 10000],
                ['name' => 'Tracking Pole Busa', 'price' => 15000],
                ['name' => 'Kursi Lipat (Hitam, Navy, Cream)', 'price' => 15000],
                ['name' => 'Meja Lipat', 'price' => 20000],
            ],
            'Sale Item' => [
                ['name' => 'Jas Hujan Ponco', 'price' => 5000],
                ['name' => 'Emergency Blanket', 'price' => 10000],
                ['name' => 'Handwarmer/Footwarmer (Isi 2)', 'price' => 15000],
                ['name' => 'Kricket', 'price' => 5000],
                ['name' => 'Sticker Pack', 'price' => 10000],
                ['name' => 'Ganci Akrilik', 'price' => 10000],
                ['name' => 'Kaos Kaki', 'price' => 10000],
                ['name' => 'Spandex/Manset Tangan', 'price' => 10000],
                ['name' => 'Balaclava', 'price' => 10000],
            ],
        ];

        foreach ($categoriesWithProducts as $categoryName => $products) {
            $category = Category::firstOrCreate(['name' => $categoryName]);

            foreach ($products as $item) {
                Product::updateOrCreate(
                    [
                        'name' => $item['name'],
                        'category_id' => $category->id,
                    ],
                    [
                        'price' => $item['price'],
                        'cost_price' => 0,
                        'stock' => 10,
                        'is_active' => 1,
                        'description' => 'Sewa / Produk Outdoor: ' . $item['name'],
                    ]
                );
            }
        }
    }
}
