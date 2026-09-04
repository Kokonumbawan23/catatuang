<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_cached_returns_a_plain_collection_of_arrays_not_eloquent_models(): void
    {
        Category::factory()->create(['type' => 'expense']);

        $cached = Category::cached();

        // Sengaja bukan Eloquent Collection: kalau kita cache koleksi model utuh,
        // cache lama dari deploy sebelumnya bisa gagal di-unserialize begitu
        // struktur class model berubah (pernah kejadian: __PHP_Incomplete_Class
        // di production). Cache berupa array polos tidak punya masalah ini sama
        // sekali karena tidak menyimpan referensi ke definisi class apa pun.
        $this->assertInstanceOf(Collection::class, $cached);
        $this->assertNotInstanceOf(EloquentCollection::class, $cached);
        $this->assertIsArray($cached->first());
    }

    public function test_cached_value_survives_being_read_back_from_a_fresh_serialization(): void
    {
        Category::factory()->create(['name' => 'Makanan', 'type' => 'expense']);

        // Simulasikan proses baca-tulis cache sungguhan (bukan cuma cache in-memory
        // di request yang sama), supaya benar-benar melewati serialize/unserialize.
        $firstRead = Category::cached();
        $serialized = serialize($firstRead);
        $roundTripped = unserialize($serialized);

        $this->assertSame($firstRead->first()['name'], $roundTripped->first()['name']);
    }
}
