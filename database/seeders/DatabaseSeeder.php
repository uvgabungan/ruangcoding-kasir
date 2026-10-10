<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Pastikan user admin selalu ada dengan password 'admin123'
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Owner Toko',
                'password' => 'admin123',
            ]
        );

        if (Setting::count() === 0) {
            $this->call(SettingSeeder::class);
        }

        if (Category::count() === 0) {
            $this->call(CategorySeeder::class);
        }

        if (Product::count() === 0) {
            $this->call(ProductSeeder::class);
        }
    }
}
