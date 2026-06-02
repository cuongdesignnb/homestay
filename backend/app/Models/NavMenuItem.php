<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavMenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'label_en',
        'url',
        'route_name',
        'icon',
        'target',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
        'parent_id' => 'integer',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(NavMenu::class, 'menu_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavMenuItem::class, 'parent_id');
    }

    /**
     * Children (1 cấp con) — dùng with('children.children') để load 2 cấp
     */
    public function children(): HasMany
    {
        return $this->hasMany(NavMenuItem::class, 'parent_id')
            ->orderBy('sort_order')
            ->with('children');
    }
}
