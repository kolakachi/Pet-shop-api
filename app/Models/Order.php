<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'user_id', 'order_status_id', 'payment_id',
        'products', 'address', 'delivery_fee', 'amount', 'shipped_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
