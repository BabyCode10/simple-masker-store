<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public $incrementing = false;

    public function orderProduct() {
        return $this->hasMany('App\Models\OrderProduct');
    }

    public function orderConfirmation() {
        return $this->hasOne('App\Models\OrderConfirm');
    }
}
