<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarRentalCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'name_vi',
        'slug',
        'description',
        'description_en',
        'description_vi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function carRentals()
    {
        return $this->hasMany(CarRental::class);
    }
}
