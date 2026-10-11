<div class="grid grid-cols-1 md:grid-cols-3 gap-4" style="font-family : poppins;">
    <div class="md:col-span-2">
        <div class="flex flex-col md:flex-row  items-center justify-between mb-10">
            <input wire:model.live.debounce.300ms='search' type="text" placeholder="Cari nama atau sku produk ..."
                class="w-full p-2 border rounded-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
            <input wire:model.live='barcode' type="text" placeholder="Scan dengan alat scanner ..." autofocus
                id="barcode"
                class="w-full p-2 border rounded-full mt-2 md:mt-0 border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white  md:ml-2">
            <x-filament::button x-data="" x-on:click="$dispatch('toggle-scanner')"
                class="px-2 md:w-20 mt-2 md:mt-0 w-full h-12 bg-yellow-400 rounded-full md:ml-2">
                <img src="{{ asset('images/qrcode-scan.svg') }}" class="w-8" />
            </x-filament::button>

            {{-- MODAL SCAN CAMERA --}}
            <livewire:scanner-modal-component>
        </div>

        <div class="mt-5 px-2.5 overflow-x-auto hide-scrollbar">
            <div class="flex gap-2.5 pb-2.5 whitespace-nowrap">
                @foreach ($categories as $item)
                    <button wire:click="setCategory({{ $item['id'] ?? null }})"
                        class="category-btn px-6 py-2 mb-4 border-2 border-primary-600 rounded-lg transition-colors duration-300 {{ $selectedCategory === $item['id'] ? 'bg-primary-600 text-white' : 'dark:bg-gray-600 dark:text-white text-primary-600' }} hover:bg-primary-100">
                        {{ $item['name'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mt-5 px-2.5 overflow-x-auto hide-scrollbar">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach ($products as $item)
                    <div wire:click="addToOrder({{ $item->id }})"
                        class="bg-white dark:bg-gray-700 p-2 rounded-lg border dark:border-none shadow cursor-pointer">
                        <img src="{{ $item->image_url }}" alt="Product Image"
                            class="w-full h-24 object-cover shadow  border rounded-lg mb-2">
                        <h3 class="text-sm font-semibold">{{ $item->name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Rp.
                            {{ number_format($item->price, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="py-4">
            <x-filament::pagination :paginator="$products" :page-options="[5, 10, 20, 50, 100, 'all']" current-page-option-property="perPage" />
        </div>
    </div>

    <div class="md:col-span-1 bg-white max-h-[650px] dark:bg-gray-800 shadow-md rounded-lg p-6 block">
        <button wire:click="resetOrder"
            class="w-full h-12 bg-red-500 mt-2 text-white py-2 rounded-lg mb-4">Reset</button>
        @if (count($order_items) === 0)
            <div class="flex flex-col justify-center items-center text-center text-gray-500 dark:text-gray-300 py-30">
                <img src="{{ asset('images/cart-empty.png') }}" alt="Empty Cart" class="w-32 h-32 mb-4">
                <p class="text-lg font-semibold">Keranjang Kosong</p>
            </div>
        @else
            @if (count($order_items) >= 5)
                <div style="height: 400px; overflow-y: auto;" class="mb-4">
                @else
                <div class="mb-4">
            @endif

            @foreach ($order_items as $item)
                <div class="mb-4 ">
                    <div class="flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <div class="flex items-center">
                            <img src="{{ $item['image_url'] }}" alt="Product Image"
                                class="w-10 h-10 object-cover rounded-lg mr-2">
                            <div class="px-2">
                                <h3 class="text-xs line-clamp-2 font-semibold">{{ $item['name'] }}</h3>
                                <p class="text-gray-600 dark:text-gray-400 text-xs">Rp
                                    {{ number_format($item['price'], 0, ',', '.') }} / hari</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <x-filament::button color="warning"
                                wire:click="decreaseQuantity({{ $item['product_id'] }})">-
                            </x-filament::button>
                            <span class="px-2 text-xs font-bold text-gray-800 dark:text-gray-100 whitespace-nowrap">{{ $item['quantity'] }} Unit</span>
                            <x-filament::button color="success"
                                wire:click="increaseQuantity({{ $item['product_id'] }})">+
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @endforeach
                </div>
        @endif

        @if (count($order_items) > 0)
            <!-- Box Durasi Sewa & Info Unit di Keranjang -->
            <div class="p-3 mb-3 bg-gray-50 dark:bg-gray-700/60 rounded-xl border border-gray-200 dark:border-gray-600">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-1">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Durasi Sewa:
                    </span>
                    <div class="flex items-center space-x-1">
                        <button type="button" wire:click="decreaseDuration" class="w-7 h-7 bg-white dark:bg-gray-800 border rounded font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 flex items-center justify-center text-sm shadow-sm">-</button>
                        <span class="font-bold text-sm text-primary-600 dark:text-primary-400 px-2 min-w-16 text-center">{{ $rental_duration }} Hari</span>
                        <button type="button" wire:click="increaseDuration" class="w-7 h-7 bg-white dark:bg-gray-800 border rounded font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 flex items-center justify-center text-sm shadow-sm">+</button>
                    </div>
                </div>
                <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-gray-600">
                    <span>Jumlah Unit: <strong class="text-gray-800 dark:text-gray-200 font-semibold">{{ $this->getTotalUnits() }} Unit</strong></span>
                    <span>Tarif / Hari: <strong class="text-gray-800 dark:text-gray-200 font-semibold">Rp {{ number_format($this->getSubtotalPerDay(), 0, ',', '.') }}</strong></span>
                </div>
            </div>

            <div class="py-3 px-4 border-t border-gray-100 bg-gray-50 dark:bg-gray-700 rounded-lg mb-3">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Total ({{ $rental_duration }} Hari):</span>
                    <h3 class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($this->calculateTotal(), 0, ',', '.') }}</h3>
                </div>
            </div>

            <div class="mt-2">
                <button type="button" wire:click="openCheckout"
                    class="w-full h-12 bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded-lg mb-4 flex items-center justify-center shadow-md transition-colors gap-2">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>Checkout ({{ $this->getTotalUnits() }} Unit • {{ $rental_duration }} Hari)</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Modal Checkout -->
    @if ($showCheckoutModal)
        <div wire:ignore.self
            class="fixed antialiased inset-0 bg-black bg-opacity-75 flex justify-center items-center p-3 md:p-6 transition-opacity duration-300 ease-out z-[9999] overflow-y-auto">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] flex flex-col overflow-hidden border border-gray-200 dark:border-gray-700">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-teal-600 to-teal-700 text-white flex justify-between items-center shrink-0">
                    <div>
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            <span>Checkout Penyewaan</span>
                        </h2>
                        <p class="text-xs text-teal-100">Periksa durasi sewa, jumlah unit dan lakukan pembayaran</p>
                    </div>
                    <button type="button" wire:click="$set('showCheckoutModal', false)" class="text-white hover:text-gray-200 text-2xl font-bold p-1 leading-none">&times;</button>
                </div>

                <div class="p-5 md:p-6 overflow-y-auto space-y-4">
                    <!-- Ringkasan Sewa: Durasi & Jumlah Unit Cards -->
                    <div class="grid grid-cols-2 gap-3 md:gap-4">
                        <!-- Card 1: Jumlah Unit -->
                        <div class="bg-teal-50 dark:bg-gray-700/60 border border-teal-200 dark:border-teal-800 rounded-xl p-3 md:p-4 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-semibold text-teal-700 dark:text-teal-300 uppercase tracking-wide">Jumlah Unit</span>
                                <span class="px-2 py-0.5 bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 rounded-md text-[11px] font-bold">
                                    {{ count($order_items) }} Jenis
                                </span>
                            </div>
                            <div class="flex items-baseline gap-2 my-1">
                                <span class="text-2xl md:text-3xl font-extrabold text-teal-700 dark:text-teal-300">{{ $this->getTotalUnits() }}</span>
                                <span class="text-xs md:text-sm font-semibold text-teal-600 dark:text-teal-400">Unit Peralatan</span>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Total seluruh unit disewa</span>
                        </div>

                        <!-- Card 2: Durasi Sewa -->
                        <div class="bg-blue-50 dark:bg-gray-700/60 border border-blue-200 dark:border-blue-800 rounded-xl p-3 md:p-4 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wide">Durasi Sewa</span>
                                <div class="flex items-center space-x-1">
                                    <button type="button" wire:click="decreaseDuration" class="w-6 h-6 bg-white dark:bg-gray-800 border border-blue-300 rounded font-bold text-blue-700 dark:text-blue-300 hover:bg-blue-100 flex items-center justify-center text-xs shadow-sm">-</button>
                                    <button type="button" wire:click="increaseDuration" class="w-6 h-6 bg-white dark:bg-gray-800 border border-blue-300 rounded font-bold text-blue-700 dark:text-blue-300 hover:bg-blue-100 flex items-center justify-center text-xs shadow-sm">+</button>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2 my-1">
                                <span class="text-2xl md:text-3xl font-extrabold text-blue-700 dark:text-blue-300">{{ $rental_duration }}</span>
                                <span class="text-xs md:text-sm font-semibold text-blue-600 dark:text-blue-400">Hari Sewa</span>
                            </div>
                            <!-- Pilihan Cepat Hari -->
                            <div class="flex items-center gap-1 mt-1">
                                @foreach ([1, 2, 3, 5, 7] as $day)
                                    <button type="button" wire:click="setDuration({{ $day }})"
                                        class="px-2 py-0.5 text-[11px] rounded font-semibold transition-colors {{ $rental_duration == $day ? 'bg-blue-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-blue-700 dark:text-blue-300 border border-blue-200 hover:bg-blue-100' }}">
                                        {{ $day }}h
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Rincian Produk & Unit yang Disewa -->
                    <div class="bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600 rounded-xl p-3">
                        <div class="flex justify-between items-center mb-2 px-1">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Rincian Barang & Unit:</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Tarif / Hari: <strong class="text-gray-700 dark:text-gray-200">Rp {{ number_format($this->getSubtotalPerDay(), 0, ',', '.') }}</strong></span>
                        </div>
                        <div class="max-h-32 overflow-y-auto space-y-1.5 pr-1">
                            @foreach ($order_items as $item)
                                <div class="flex justify-between items-center text-xs bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                                    <div class="flex items-center space-x-2">
                                        <img src="{{ $item['image_url'] }}" alt="img" class="w-7 h-7 rounded object-cover">
                                        <div>
                                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $item['name'] }}</p>
                                            <p class="text-[11px] text-gray-500">Rp {{ number_format($item['price'], 0, ',', '.') }} / hari</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="px-2 py-0.5 bg-teal-50 dark:bg-teal-900/50 text-teal-700 dark:text-teal-300 font-bold rounded text-[11px]">{{ $item['quantity'] }} Unit</span>
                                        <p class="text-[11px] text-gray-600 dark:text-gray-400 font-semibold mt-0.5">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} / hari</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Banner Total Perhitungan Sewa -->
                    <div class="bg-gradient-to-r from-teal-50 to-blue-50 dark:from-gray-700 dark:to-gray-700 border border-teal-200 dark:border-teal-700 rounded-xl p-3 flex justify-between items-center">
                        <div>
                            <span class="text-xs text-gray-600 dark:text-gray-300 block">Total Biaya Sewa ({{ $this->getTotalUnits() }} Unit • {{ $rental_duration }} Hari):</span>
                            <span class="text-xs font-semibold text-teal-700 dark:text-teal-300">Rp {{ number_format($this->getSubtotalPerDay(), 0, ',', '.') }} × {{ $rental_duration }} Hari</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xl md:text-2xl font-black text-teal-700 dark:text-teal-300">Rp {{ number_format($this->calculateTotal(), 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Form Pembayaran Filament -->
                    <form wire:submit="checkout" id="checkout-form">
                        {{ $this->form }}

                        <!-- Tombol Cepat Bayar Pas -->
                        <div class="mt-3 flex justify-end">
                            <button type="button" wire:click="setExactCash"
                                class="text-xs font-semibold text-teal-700 dark:text-teal-300 bg-teal-100 dark:bg-teal-900/50 hover:bg-teal-200 px-3 py-1.5 rounded-lg border border-teal-300 dark:border-teal-700 transition">
                                ⚡ Bayar Uang Pas (Rp {{ number_format($this->calculateTotal(), 0, ',', '.') }})
                            </button>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex justify-between items-center gap-3 mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" wire:click="$set('showCheckoutModal', false)"
                                class="w-1/3 h-11 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 text-gray-700 dark:text-gray-200 font-semibold rounded-xl transition text-sm">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-2/3 h-11 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2 text-sm">
                                <span>Konfirmasi & Simpan Order</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showConfirmationModal)
        <div class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50">
            <!-- Modal Content -->
            <div class="bg-white rounded-lg shadow-lg w-11/12 sm:w-96">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-purple-500 text-white rounded-t-lg">
                    <h2 class="text-xl text-center font-semibold">PRINT STRUK</h2>
                </div>
                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <p class="text-gray-800">
                        Apakah Anda ingin mencetak struk untuk pesanan ini?
                    </p>
                </div>
                <!-- Modal Footer -->
                <div class="px-6 py-4 flex justify-center space-x-4">
                    <button wire:click="$set('showConfirmationModal', false)"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-full hover:bg-gray-400 focus:ring-2 focus:ring-gray-500">
                        Tidak
                    </button>
                    @if ($print_via_bluetooth == true)
                        <button wire:click="printBluetooth"
                            class="px-4 py-2 bg-purple-500 text-white rounded-full hover:bg-blue-600 focus:ring-2 focus:ring-blue-400">
                            Cetak
                        </button>
                    @else
                        <button wire:click="printLocalKabel"
                            class="px-4 py-2 bg-purple-500 text-white rounded-full hover:bg-blue-600 focus:ring-2 focus:ring-blue-400">
                            Cetak
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
