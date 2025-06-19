<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'tags' => 'array'
    ];

    // Event types
    const CREATED = 'created';
    const UPDATED = 'updated';
    const DELETED = 'deleted';
    const RESTORED = 'restored';
    const LOGGED_IN = 'logged_in';
    const LOGGED_OUT = 'logged_out';
    const FAILED_LOGIN = 'failed_login';
    const SETTINGS_UPDATED = 'settings_updated';
    const PERMISSION_GRANTED = 'permission_granted';
    const PERMISSION_REVOKED = 'permission_revoked';

    // Relationships
    public function user()
    {
        return $this->belongsTo(Member::class, 'user_id');
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForModel($query, $modelType)
    {
        return $query->where('auditable_type', $modelType);
    }

    public function scopeForEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    public function scopeWithTags($query, array $tags)
    {
        return $query->where(function (Builder $query) use ($tags) {
            foreach ($tags as $tag) {
                $query->orWhereJsonContains('tags', $tag);
            }
        });
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Helper methods
    public static function log($event, $userId, $auditableType = null, $auditableId = null, $oldValues = [], $newValues = [], $tags = [])
    {
        return self::create([
            'user_id' => $userId,
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'tags' => $tags
        ]);
    }

    public function getChanges()
    {
        $changes = [];

        if (empty($this->old_values)) {
            return array_map(function ($value) {
                return [
                    'old' => null,
                    'new' => $value
                ];
            }, $this->new_values);
        }

        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }

    public static function getEventTypes()
    {
        return [
            self::CREATED,
            self::UPDATED,
            self::DELETED,
            self::RESTORED,
            self::LOGGED_IN,
            self::LOGGED_OUT,
            self::FAILED_LOGIN,
            self::SETTINGS_UPDATED,
            self::PERMISSION_GRANTED,
            self::PERMISSION_REVOKED
        ];
    }

    public static function getStats($startDate = null, $endDate = null, $userId = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->inDateRange($startDate, $endDate);
        }

        if ($userId) {
            $query->forUser($userId);
        }

        $logs = $query->get();

        return [
            'total_logs' => $logs->count(),
            'events_count' => $logs->groupBy('event')->map->count(),
            'users_count' => $logs->groupBy('user_id')->count(),
            'models_count' => $logs->groupBy('auditable_type')->map->count(),
            'top_users' => $logs->groupBy('user_id')
                ->map(function ($logs) {
                    return [
                        'count' => $logs->count(),
                        'user' => Member::find($logs->first()->user_id)
                    ];
                })
                ->sortByDesc('count')
                ->take(5),
            'ip_addresses' => $logs->groupBy('ip_address')->map->count(),
            'hourly_distribution' => $logs->groupBy(function ($log) {
                return $log->created_at->format('H');
            })->map->count()
        ];
    }

    public function getEventDescription()
    {
        $modelName = class_basename($this->auditable_type);
        $userName = $this->user ? $this->user->full_name : 'System';

        switch ($this->event) {
            case self::CREATED:
                return "{$userName} created a new {$modelName}";
            case self::UPDATED:
                return "{$userName} updated {$modelName}";
            case self::DELETED:
                return "{$userName} deleted {$modelName}";
            case self::RESTORED:
                return "{$userName} restored {$modelName}";
            case self::LOGGED_IN:
                return "{$userName} logged in";
            case self::LOGGED_OUT:
                return "{$userName} logged out";
            case self::FAILED_LOGIN:
                return "Failed login attempt for {$userName}";
            case self::SETTINGS_UPDATED:
                return "{$userName} updated system settings";
            case self::PERMISSION_GRANTED:
                return "{$userName} was granted new permissions";
            case self::PERMISSION_REVOKED:
                return "{$userName} had permissions revoked";
            default:
                return "{$userName} performed {$this->event} on {$modelName}";
        }
    }

    public function addTags(array $newTags)
    {
        $tags = array_unique(array_merge($this->tags ?? [], $newTags));
        $this->update(['tags' => $tags]);
    }

    public function removeTags(array $tagsToRemove)
    {
        $tags = array_diff($this->tags ?? [], $tagsToRemove);
        $this->update(['tags' => array_values($tags)]);
    }
}