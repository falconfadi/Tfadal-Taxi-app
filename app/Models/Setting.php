<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    protected $table = 'settings';
    use HasFactory;
    protected $fillable = [
        'phone', 'email', 'facebook', 'whatsapp_number','price_min_stop','price_min','price_open',
        'discount_driver','company_percentage'
    ];
    public function getSetting()
    {
        $e = Setting::find(1);
        return $e;
    }

}
