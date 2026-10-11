<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'stock', 'cost_price', 'price',
        'image', 'barcode','sku', 'description', 'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('images/product-default.jpg');
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return \Illuminate\Support\Facades\Storage::disk(config('filesystems.default'))->url($this->image);
    }
}
