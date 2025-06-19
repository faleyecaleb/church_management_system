<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
        'autoload'
    ];

    protected $casts = [
        'value' => 'json',
        'is_public' => 'boolean',
        'autoload' => 'boolean'
    ];

    // Cache keys
    const CACHE_KEY = 'app_settings';
    const CACHE_DURATION = 1440; // 24 hours in minutes

    // Setting types
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';

    // Setting groups
    const GROUP_GENERAL = 'general';
    const GROUP_MAIL = 'mail';
    const GROUP_SMS = 'sms';
    const GROUP_PAYMENT = 'payment';
    const GROUP_NOTIFICATION = 'notification';
    const GROUP_SECURITY = 'security';

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeAutoload($query)
    {
        return $query->where('autoload', true);
    }

    public function scopeInGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    // Helper methods
    public static function get($key, $default = null)
    {
        $setting = Cache::get(self::CACHE_KEY . ':' . $key);

        if (!$setting) {
            $setting = self::where('key', $key)->first();
            if ($setting) {
                Cache::put(self::CACHE_KEY . ':' . $key, $setting, self::CACHE_DURATION);
            }
        }

        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value, $type = null, $group = self::GROUP_GENERAL, $description = null, $isPublic = false, $autoload = true)
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type ?? self::determineType($value),
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic,
                'autoload' => $autoload
            ]
        );

        Cache::put(self::CACHE_KEY . ':' . $key, $setting, self::CACHE_DURATION);

        return $setting;
    }

    public static function remove($key)
    {
        $setting = self::where('key', $key)->first();
        if ($setting) {
            Cache::forget(self::CACHE_KEY . ':' . $key);
            return $setting->delete();
        }
        return false;
    }

    public static function loadAll()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            return self::autoload()->get()->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            })->all();
        });
    }

    public static function clearCache()
    {
        Cache::forget(self::CACHE_KEY);
        $keys = self::pluck('key')->all();
        foreach ($keys as $key) {
            Cache::forget(self::CACHE_KEY . ':' . $key);
        }
    }

    protected static function determineType($value)
    {
        switch (true) {
            case is_bool($value):
                return self::TYPE_BOOLEAN;
            case is_int($value):
                return self::TYPE_INTEGER;
            case is_float($value):
                return self::TYPE_FLOAT;
            case is_array($value):
                return self::TYPE_ARRAY;
            case is_object($value):
                return self::TYPE_OBJECT;
            default:
                return self::TYPE_STRING;
        }
    }

    public static function getByGroup($group)
    {
        return self::where('group', $group)->get();
    }

    public static function getPublicSettings()
    {
        return self::public()->get();
    }

    public function getTypedValue()
    {
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return (bool) $this->value;
            case self::TYPE_INTEGER:
                return (int) $this->value;
            case self::TYPE_FLOAT:
                return (float) $this->value;
            case self::TYPE_ARRAY:
                return (array) $this->value;
            case self::TYPE_OBJECT:
                return (object) $this->value;
            default:
                return (string) $this->value;
        }
    }

    public static function validateValue($value, $type)
    {
        switch ($type) {
            case self::TYPE_BOOLEAN:
                return is_bool($value);
            case self::TYPE_INTEGER:
                return is_int($value);
            case self::TYPE_FLOAT:
                return is_float($value);
            case self::TYPE_ARRAY:
                return is_array($value);
            case self::TYPE_OBJECT:
                return is_object($value);
            case self::TYPE_STRING:
                return is_string($value);
            default:
                return false;
        }
    }

    public static function getAvailableGroups()
    {
        return [
            self::GROUP_GENERAL,
            self::GROUP_MAIL,
            self::GROUP_SMS,
            self::GROUP_PAYMENT,
            self::GROUP_NOTIFICATION,
            self::GROUP_SECURITY
        ];
    }

    public static function getAvailableTypes()
    {
        return [
            self::TYPE_STRING,
            self::TYPE_INTEGER,
            self::TYPE_FLOAT,
            self::TYPE_BOOLEAN,
            self::TYPE_ARRAY,
            self::TYPE_OBJECT
        ];
    }
}