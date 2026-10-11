<?php

namespace App\Livewire;

use Filament\Forms;
use App\Models\Product;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Category;
use Filament\Forms\Form;
use App\Models\Transaction;
use Livewire\WithPagination;
use App\Models\PaymentMethod;
use App\Models\TransactionItem;
use App\Helpers\TransactionHelper;
use App\Services\DirectPrintService;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class Pos extends Component implements HasForms
{

    use InteractsWithForms;
    use WithPagination;

    public int | string $perPage = 10;
    public $categories;
    public $selectedCategory;
    public $search = '';
    public $print_via_bluetooth = false;
    public $barcode = '';
    public $name = 'Umum';
    public $payment_method_id;
    public $payment_methods;
    public $order_items = [];
    public $total_price = 0;
    public int $rental_duration = 1;
    public $cash_received;
    public $change;
    public $showConfirmationModal = false;
    public $showCheckoutModal = false;
    public $orderToPrint = null;

    protected $listeners = [
        'scanResult' => 'handleScanResult',
    ];

    public function mount()
    {
        $settings = Setting::first();
        $this->print_via_bluetooth = $settings->print_via_bluetooth ?? $this->print_via_bluetooth = false;

        // Mengambil data kategori dan menambahkan data 'Semua' sebagai pilihan pertama
        $this->categories = collect([['id' => null, 'name' => 'Semua']])->merge(Category::all());

        // Jika session 'orderItems' ada, maka ambil data nya dan simpan ke dalam property $order_items
        if (session()->has('orderItems')) {
            $this->order_items = session('orderItems');
        }

        if (session()->has('rental_duration')) {
            $this->rental_duration = max(1, (int) session('rental_duration'));
        }

        $this->payment_methods = PaymentMethod::all();
    }

    public function getTotalUnits(): int
    {
        return (int) collect($this->order_items)->sum('quantity');
    }

    public function getSubtotalPerDay(): int
    {
        $subtotal = 0;
        foreach ($this->order_items as $item) {
            $subtotal += ($item['quantity'] ?? 1) * ($item['price'] ?? 0);
        }
        return $subtotal;
    }

    public function setDuration(int $days)
    {
        $this->rental_duration = max(1, $days);
        session()->put('rental_duration', $this->rental_duration);
        $this->syncTotals();
    }

    public function increaseDuration()
    {
        $this->rental_duration++;
        session()->put('rental_duration', $this->rental_duration);
        $this->syncTotals();
    }

    public function decreaseDuration()
    {
        if ($this->rental_duration > 1) {
            $this->rental_duration--;
            session()->put('rental_duration', $this->rental_duration);
            $this->syncTotals();
        }
    }

    public function updatedRentalDuration($val = null)
    {
        $this->rental_duration = max(1, (int) $this->rental_duration);
        session()->put('rental_duration', $this->rental_duration);
        $this->syncTotals();
    }

    public function setExactCash()
    {
        $this->cash_received = $this->calculateTotal();
        $this->change = 0;
    }

    public function syncTotals()
    {
        $total = $this->calculateTotal();
        if ($this->payment_method_id) {
            $pm = PaymentMethod::find($this->payment_method_id);
            if ($pm && !$pm->is_cash) {
                $this->cash_received = $total;
                $this->change = 0;
            } elseif ($this->cash_received > 0) {
                $this->change = (int) $this->cash_received - (int) $total;
            }
        }
    }

    public function openCheckout()
    {
        if (empty($this->order_items)) {
            Notification::make()
                ->title('Keranjang masih kosong')
                ->warning()
                ->send();
            return;
        }

        $this->calculateTotal();
        $this->showCheckoutModal = true;
    }

    public function render()
    {
        return view('livewire.pos', [
            'products' => Product::where('stock', '>', 0)->where('is_active', 1)
                ->when($this->selectedCategory !== null, function ($query) {
                    return $query->where('category_id', $this->selectedCategory);
                })
                ->where(function ($query) {
                    return $query->where('name', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('sku', 'LIKE', '%' . $this->search . '%');
                })
                ->paginate($this->perPage)
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pesanan & Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->default(fn() => $this->name)
                            ->label('Nama Penyewa / Customer')
                            ->nullable()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('rental_duration')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->label('Durasi Sewa')
                            ->suffix('Hari')
                            ->default(fn() => $this->rental_duration ?? 1)
                            ->columnSpan(1)
                            ->reactive()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                $duration = max(1, (int) ($state ?? 1));
                                $this->rental_duration = $duration;
                                session()->put('rental_duration', $duration);
                                $total = $this->calculateTotal();
                                $set('total_price', $total);

                                if (!$get('is_cash')) {
                                    $set('cash_received', $total);
                                    $set('change', 0);
                                } else {
                                    $paid = (int) ($get('cash_received') ?? 0);
                                    if ($paid > 0) {
                                        $set('change', $paid - $total);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('total_price')
                            ->hidden()
                            ->reactive()
                            ->default(fn() => $this->total_price ?? 0),

                        Forms\Components\Select::make('payment_method_id')
                            ->required()
                            ->label('Metode Pembayaran')
                            ->placeholder('Pilih metode')
                            ->options($this->payment_methods->pluck('name', 'id'))
                            ->columnSpan(2)
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $paymentMethod = PaymentMethod::find($state);
                                $isCash = $paymentMethod?->is_cash ?? false;
                                $set('is_cash', $isCash);

                                if (!$isCash) {
                                    $set('change', 0);
                                    $set('cash_received', $this->calculateTotal());
                                }
                            })
                            ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                                $paymentMethod = PaymentMethod::find($state);
                                $isCash = $paymentMethod?->is_cash ?? false;

                                if (!$isCash) {
                                    $set('cash_received', $this->calculateTotal());
                                    $set('change', 0);
                                }

                                $set('is_cash', $isCash);
                            }),

                        Forms\Components\TextInput::make('is_cash')->hidden()->dehydrated(),

                        Forms\Components\TextInput::make('cash_received')
                            ->required()
                            ->numeric()
                            ->reactive()
                            ->prefix('Rp')
                            ->label('Nominal Bayar')
                            ->columnSpan(1)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                $paid = (int) ($state ?? 0);
                                $total = (int) ($this->calculateTotal());
                                $set('change', $paid - $total);
                            }),

                        Forms\Components\TextInput::make('change')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Kembalian')
                            ->columnSpan(1)
                            ->readOnly(),
                    ])->columns(2)
            ]);
    }


    public function updatedBarcode($barcode)
    {

        $product = Product::where('barcode', $barcode)
            ->where('is_active', true)->first();

        if ($product) {
            $this->addToOrder($product->id);
        } else {
            Notification::make()
                ->title('Product not found ' . $barcode)
                ->danger()
                ->send();
        }

        // Reset barcode
        $this->barcode = '';
    }

    public function handleScanResult($decodedText)
    {
        $product = Product::where('barcode', $decodedText)
            ->where('is_active', true)->first();

        if ($product) {
            $this->addToOrder($product->id);
        } else {
            Notification::make()
                ->title('Product not found ' . $decodedText)
                ->danger()
                ->send();
        }

        // Reset barcode
        $this->barcode = '';
    }

    public function setCategory($categoryId = null)
    {
        $this->selectedCategory = $categoryId;
        // $this->loadMenus();
    }

    public function addToOrder($productId)
    {
        $product = Product::find($productId);

        if ($product) {

            // Cari apakah item sudah ada di dalam order
            $existingItemKey = array_search($productId, array_column($this->order_items, 'product_id'));

            // Jika item sudah ada, tambahkan 1 quantity
            if ($existingItemKey !== false) {
                if ($this->order_items[$existingItemKey]['quantity'] >= $product->stock) {
                    Notification::make()
                        ->title('Stok barang tidak mencukupi')
                        ->danger()
                        ->send();
                    return;
                } else {
                    $this->order_items[$existingItemKey]['quantity']++;
                }
            }

            // Jika item belum ada, tambahkan item baru ke dalam order
            else {
                $this->order_items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'cost_price' => $product->cost_price,
                    'total_profit' => $product->price - $product->cost_price,
                    'image_url' => $product->image_url,
                    'quantity' => 1,
                ];
            }

            // Simpan perubahan order ke session
            session()->put('orderItems', $this->order_items);
        }
    }

    public function loadOrderItems($orderItems)
    {
        $this->order_items = $orderItems;
        session()->put('orderItems', $orderItems);
    }

    public function increaseQuantity($product_id)
    {
        $product = Product::find($product_id);

        if (!$product) {
            Notification::make()
                ->title('Produk tidak ditemukan')
                ->danger()
                ->send();
            return;
        }

        // Loop setiap item yang ada di cart
        foreach ($this->order_items as $key => $item) {
            // Jika item yang sedang di-loop sama dengan item yang ingin di tambah
            if ($item['product_id'] == $product_id) {
                // Jika quantity item ditambah 1 masih kurang dari atau sama dengan stok produk maka tambah 1 quantity
                if ($item['quantity'] + 1 <= $product->stock) {
                    $this->order_items[$key]['quantity']++;
                }
                // Jika quantity item yang ingin di tambah lebih besar dari stok produk maka tampilkan notifikasi
                else {
                    Notification::make()
                        ->title('Stok barang tidak mencukupi')
                        ->danger()
                        ->send();
                }
                // Berhenti loop karena item yang ingin di tambah sudah di temukan
                break;
            }
        }

        session()->put('orderItems', $this->order_items);
    }

    public function decreaseQuantity($product_id)
    {
        // Loop setiap item yang ada di cart
        foreach ($this->order_items as $key => $item) {
            // Jika item yang sedang di-loop sama dengan item yang ingin di kurangi
            if ($item['product_id'] == $product_id) {
                // Jika quantity item lebih dari 1 maka kurangi 1 quantity
                if ($this->order_items[$key]['quantity'] > 1) {
                    $this->order_items[$key]['quantity']--;
                }
                // Jika quantity item 1 maka hapus item dari cart
                else {
                    unset($this->order_items[$key]);
                    $this->order_items = array_values($this->order_items);
                }
                break;
            }
        }
        // Simpan perubahan cart ke session
        session()->put('orderItems', $this->order_items);
    }

    public function calculateTotal(): int
    {
        $duration = max(1, (int) ($this->rental_duration ?? 1));
        $total = $this->getSubtotalPerDay() * $duration;
        $this->total_price = $total;
        return $total;
    }

    public function resetOrder()
    {
        // Hapus semua session terkait
        session()->forget(['orderItems', 'name', 'payment_method_id', 'rental_duration']);

        // Reset variabel Livewire
        $this->order_items = [];
        $this->payment_method_id = null;
        $this->total_price = 0;
        $this->rental_duration = 1;
        $this->cash_received = 0;
        $this->change = 0;
    }



    public function checkout()
    {
        $this->validate([
            'name' => 'string|max:255',
            'payment_method_id' => 'required'
        ]);

        $payment_method_id_temp = $this->payment_method_id;

        if (session('orderItems') === null || count(session('orderItems')) == 0) {
            Notification::make()
                ->title('Keranjang kosong')
                ->danger()
                ->send();
            
            $this->showCheckoutModal = false;
            return;
        }

        $duration = max(1, (int) ($this->rental_duration ?? 1));
        $totalUnits = $this->getTotalUnits();
        $this->calculateTotal();

        $notes = "Durasi Sewa: {$duration} Hari | Jumlah Unit: {$totalUnits} Unit";

        $order = Transaction::create([
            'payment_method_id' => $payment_method_id_temp,
            'transaction_number' => TransactionHelper::generateUniqueTrxId(),
            'name' => $this->name ?? 'Umum',
            'notes' => $notes,
            'rental_duration' => $duration,
            'total' => $this->total_price,
            'cash_received' => $this->cash_received ?? $this->total_price,
            'change' => $this->change ?? 0,
        ]);

        foreach ($this->order_items as $item) {
            TransactionItem::create([
                'transaction_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'cost_price' => $item['cost_price'],
                'total_profit' => ($item['total_profit'] * $item['quantity']) * $duration,
            ]);
        }

        // Simpan ID order untuk cetak
        $this->orderToPrint = $order->id;

        // Tampilkan modal konfirmasi
        $this->showConfirmationModal = true;
        $this->showCheckoutModal = false;

        Notification::make()
            ->title('Order sewa berhasil disimpan')
            ->success()
            ->send();

        $this->name = 'Umum';
        $this->payment_method_id = null;
        $this->total_price = 0;
        $this->rental_duration = 1;
        $this->cash_received = 0;
        $this->change = 0;
        $this->order_items = [];
        session()->forget(['orderItems', 'rental_duration']);
    }


    public function printLocalKabel()
    {
        $directPrint = app(DirectPrintService::class);

        $directPrint->print($this->orderToPrint);

        $this->showConfirmationModal = false;
        $this->orderToPrint = null;
    }

    public function printBluetooth()
    {
        $order = Transaction::with(['paymentMethod', 'transactionItems.product'])->findOrFail($this->orderToPrint);
        $items = $order->transactionItems;


        $this->dispatch(
            'doPrintReceipt',
            store: Setting::first(),
            order: $order,
            items: $items,
            date: $order->created_at->format('d-m-Y H:i:s')
        );

        $this->showConfirmationModal = false;
        $this->orderToPrint = null;
    }
}
