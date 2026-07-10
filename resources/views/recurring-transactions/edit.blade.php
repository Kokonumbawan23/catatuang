<x-app-layout>
    <div class="py-10 bg-gray-100 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200/60">
                <div class="mb-6">
                    <h2 class="font-bold text-xl text-gray-900">Edit Transaksi Berulang</h2>
                    <p class="text-xs text-gray-500 mt-1">Perbarui transaksi otomatis.</p>
                </div>

                <form method="POST" action="{{ route('recurring-transactions.update', $recurring) }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="wallet_id" :value="__('Dompet')" />
                        <x-select id="wallet_id" name="wallet_id" :options="$wallets->map(fn($w) => ['value' => $w->id, 'label' => $w->name])" value="{{ $recurring->wallet_id }}" class="mt-1 w-full" placeholder="Pilih Dompet" />
                        @error('wallet_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-input-label for="title" :value="__('Judul')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 w-full" placeholder="Contoh: Langganan Netflix" required :value="old('title', $recurring->title)" />
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="type" :value="__('Tipe')" />
                            <x-select id="type" name="type" :options="[['value' => 'expense', 'label' => 'Pengeluaran'], ['value' => 'income', 'label' => 'Pemasukan']]" value="{{ $recurring->type }}" class="mt-1 w-full" placeholder="Pilih Tipe" />
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-input-label for="amount" :value="__('Jumlah (Rp)')" />
                            <x-text-input id="amount" name="amount" type="text" inputmode="numeric" class="mt-1 w-full idr-input" placeholder="Rp 0" required :value="old('amount', $recurring->amount)" />
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <x-input-label for="category_id" :value="__('Kategori (Opsional)')" />
                        <x-select id="category_id" name="category_id" :options="$categories->map(fn($c) => ['value' => $c->id, 'label' => $c->name])" value="{{ $recurring->category_id }}" class="mt-1 w-full" placeholder="Pilih Kategori" />
                        @error('category_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="frequency" :value="__('Frekuensi')" />
                            <x-select id="frequency" name="frequency" :options="[['value' => 'daily', 'label' => 'Harian'], ['value' => 'weekly', 'label' => 'Mingguan'], ['value' => 'monthly', 'label' => 'Bulanan'], ['value' => 'yearly', 'label' => 'Tahunan']]" value="{{ $recurring->frequency }}" class="mt-1 w-full" placeholder="Pilih Frekuensi" />
                            @error('frequency')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-input-label for="schedule_config" :value="__('Konfigurasi Jadwal')" />
                            <x-text-input id="schedule_config" name="schedule_config" type="text" class="mt-1 w-full" placeholder="JSON sesuai frekuensi" :value="old('schedule_config', json_encode($recurring->schedule_config))" />
                            <p class="text-xs text-gray-400 mt-1">Harian: {"interval_days":1}, Mingguan: {"day_of_week":[1,5]}, Bulanan: {"day_of_month":15}, Tahunan: {"day_of_month":1,"month_of_year":1}</p>
                            @error('schedule_config')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="start_date" :value="__('Tanggal Mulai')" />
                            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 w-full" required :value="old('start_date', $recurring->start_date?->format('Y-m-d'))" />
                            @error('start_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <x-input-label for="end_date" :value="__('Tanggal Selesai (Opsional)')" />
                            <x-text-input id="end_date" name="end_date" type="date" class="mt-1 w-full" :value="old('end_date', $recurring->end_date?->format('Y-m-d'))" />
                            @error('end_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ $recurring->is_active ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-600">Aktif</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <a href="{{ route('recurring-transactions.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</a>
                        <x-primary-button class="px-6 py-2">Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
