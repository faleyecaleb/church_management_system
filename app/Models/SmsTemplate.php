<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'category',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helper methods
    public static function getAvailableCategories()
    {
        return [
            'general' => 'General Messages',
            'event' => 'Event Notifications',
            'reminder' => 'Reminders',
            'birthday' => 'Birthday Wishes',
            'attendance' => 'Attendance Follow-up',
            'donation' => 'Donation Acknowledgment',
            'prayer' => 'Prayer Requests',
            'emergency' => 'Emergency Notifications'
        ];
    }

    public static function getDefaultTemplates()
    {
        return [
            [
                'name' => 'Welcome Message',
                'content' => 'Welcome {name} to our church family! We're blessed to have you with us.',
                'category' => 'general',
                'is_active' => true
            ],
            [
                'name' => 'Birthday Wish',
                'content' => 'Happy Birthday {name}! May God bless you abundantly on your special day.',
                'category' => 'birthday',
                'is_active' => true
            ],
            [
                'name' => 'Event Reminder',
                'content' => 'Dear {name}, reminder: {event_name} is scheduled for {event_date} at {event_time}.',
                'category' => 'reminder',
                'is_active' => true
            ],
            [
                'name' => 'Donation Thank You',
                'content' => 'Dear {name}, thank you for your generous donation of {amount}. Your support helps our ministry grow.',
                'category' => 'donation',
                'is_active' => true
            ],
            [
                'name' => 'Prayer Request Update',
                'content' => 'Dear {name}, your prayer request has been received and shared with our prayer team.',
                'category' => 'prayer',
                'is_active' => true
            ],
            [
                'name' => 'Missed Attendance Follow-up',
                'content' => 'Dear {name}, we missed you at church this Sunday. Hope everything is well with you.',
                'category' => 'attendance',
                'is_active' => true
            ],
            [
                'name' => 'Emergency Notice',
                'content' => 'IMPORTANT: {emergency_message}. Please check your email for more details.',
                'category' => 'emergency',
                'is_active' => true
            ]
        ];
    }

    public function parseTemplate($data)
    {
        $content = $this->content;

        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $content;
    }

    public static function findByNameAndCategory($name, $category)
    {
        return self::where('name', $name)
                   ->where('category', $category)
                   ->first();
    }

    public function duplicate($newName = null)
    {
        $name = $newName ?? $this->name . ' (Copy)';

        return self::create([
            'name' => $name,
            'content' => $this->content,
            'category' => $this->category,
            'is_active' => true
        ]);
    }

    public static function getTemplateStats()
    {
        $templates = self::all();

        return [
            'total_templates' => $templates->count(),
            'active_templates' => $templates->where('is_active', true)->count(),
            'by_category' => $templates->groupBy('category')
                ->map(fn ($items) => [
                    'total' => $items->count(),
                    'active' => $items->where('is_active', true)->count()
                ])
        ];
    }

    public function validateTemplate()
    {
        // Check for unclosed placeholders
        preg_match_all('/{([^}]*)}/', $this->content, $matches);
        $placeholders = $matches[1];

        // Validate each placeholder
        $validPlaceholders = [
            'name', 'first_name', 'last_name', 'event_name', 'event_date',
            'event_time', 'amount', 'emergency_message'
        ];

        $invalidPlaceholders = array_diff($placeholders, $validPlaceholders);

        return [
            'is_valid' => empty($invalidPlaceholders),
            'invalid_placeholders' => $invalidPlaceholders,
            'valid_placeholders' => array_intersect($placeholders, $validPlaceholders)
        ];
    }
}