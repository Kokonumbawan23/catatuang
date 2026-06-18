<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan', 'icon' => '🍔', 'color' => '#FF6B6B'],
            ['name' => 'Transportasi', 'icon' => '🚗', 'color' => '#4ECDC4'],
            ['name' => 'Tagihan', 'icon' => '📄', 'color' => '#45B7D1'],
            ['name' => 'Hiburan', 'icon' => '🎬', 'color' => '#96CEB4'],
            ['name' => 'Belanja', 'icon' => '🛒', 'color' => '#FFEAA7'],
            ['name' => 'Kesehatan', 'icon' => '💊', 'color' => '#DDA0DD'],
            ['name' => 'Edukasi', 'icon' => '📚', 'color' => '#98D8C8'],
            ['name' => 'Lainnya', 'icon' => '📦', 'color' => '#95A5A6'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
