<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavMenu extends Model
{
    protected $fillable = ['name', 'label', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Tất cả items (phẳng)
     */
    public function items(): HasMany
    {
        return $this->hasMany(NavMenuItem::class, 'menu_id')->orderBy('sort_order');
    }

    /**
     * Chỉ items gốc (không có parent)
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(NavMenuItem::class, 'menu_id')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with('children');
    }
}
