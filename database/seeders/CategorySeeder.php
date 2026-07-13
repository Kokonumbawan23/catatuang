<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $expenseCategories = [
            ['name' => 'Makanan', 'icon' => '🍔', 'color' => '#FF6B6B', 'type' => 'expense'],
            ['name' => 'Transportasi', 'icon' => '🚗', 'color' => '#4ECDC4', 'type' => 'expense'],
            ['name' => 'Tagihan', 'icon' => '📄', 'color' => '#45B7D1', 'type' => 'expense'],
            ['name' => 'Hiburan', 'icon' => '🎬', 'color' => '#96CEB4', 'type' => 'expense'],
            ['name' => 'Belanja', 'icon' => '🛒', 'color' => '#FFEAA7', 'type' => 'expense'],
            ['name' => 'Kesehatan', 'icon' => '💊', 'color' => '#DDA0DD', 'type' => 'expense'],
            ['name' => 'Edukasi', 'icon' => '📚', 'color' => '#98D8C8', 'type' => 'expense'],
            ['name' => 'Lainnya', 'icon' => '📦', 'color' => '#95A5A6', 'type' => 'expense'],
        ];

        $incomeCategories = [
            ['name' => 'Pekerjaan', 'icon' => '💼', 'color' => '#10B981', 'type' => 'income'],
            ['name' => 'Freelance', 'icon' => '💻', 'color' => '#8B5CF6', 'type' => 'income'],
            ['name' => 'Jualan', 'icon' => '🛍️', 'color' => '#F59E0B', 'type' => 'income'],
            ['name' => 'Bisnis', 'icon' => '🏪', 'color' => '#06B6D4', 'type' => 'income'],
            ['name' => 'Dividen', 'icon' => '📈', 'color' => '#EC4899', 'type' => 'income'],
            ['name' => 'Lainnya', 'icon' => '📦', 'color' => '#6B7280', 'type' => 'income'],
        ];

        foreach ($expenseCategories as $category) {
            Category::create($category);
        }

        foreach ($incomeCategories as $category) {
            Category::create($category);
        }
    }
}
