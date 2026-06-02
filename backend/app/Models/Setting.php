<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'group',
        'value_vi',
        'value_en',
        'type',
        'label',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get value based on locale
     */
    public function getValueAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $locale === 'vi' ? $this->value_vi : ($this->value_en ?? $this->value_vi);
    }

    /**
     * Get setting by key
     */
    public static function get(string $key, ?string $locale = null, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        $locale = $locale ?? app()->getLocale();
        $value = $locale === 'vi' ? $setting->value_vi : ($setting->value_en ?? $setting->value_vi);

        // Handle JSON type
        if ($setting->type === 'json' && $value) {
            return json_decode($value, true) ?? $default;
        }

        return $value ?? $default;
    }

    /**
     * Get setting value (alias for get method)
     */
    public static function getValue(string $key, $default = null)
    {
        return static::get($key, null, $default);
    }

    /**
     * Set setting value
     */
    public static function set(string $key, $valueVi, $valueEn = null, array $options = []): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            array_merge([
                'value_vi' => is_array($valueVi) ? json_encode($valueVi) : $valueVi,
                'value_en' => is_array($valueEn) ? json_encode($valueEn) : $valueEn,
            ], $options)
        );
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        
        return static::where('group', $group)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(function ($setting) use ($locale) {
                $value = $locale === 'vi' ? $setting->value_vi : ($setting->value_en ?? $setting->value_vi);
                
                if ($setting->type === 'json' && $value) {
                    $value = json_decode($value, true);
                }
                
                return [$setting->key => $value];
            })
            ->toArray();
    }

    /**
     * Get all settings formatted for frontend
     */
    public static function getAllForFrontend(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        
        return static::orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->map(function ($settings) use ($locale) {
                return $settings->map(function ($setting) use ($locale) {
                    return [
                        'key' => $setting->key,
                        'value' => $locale === 'vi' 
                            ? $setting->value_vi 
                            : ($setting->value_en ?? $setting->value_vi),
                        'value_vi' => $setting->value_vi,
                        'value_en' => $setting->value_en,
                        'type' => $setting->type,
                    ];
                })->values();
            })
            ->toArray();
    }
}
