<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = [
        'shipping',
        'tax',
        'governorate_id',
        'estimated_days',
    ];

    protected $casts = [
        'shipping' => 'float',
        'tax' => 'float',
    ];
    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}