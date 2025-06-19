<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',           // Template name for identification
        'type',           // 'sms', 'prayer', 'internal'
        'subject',        // Template subject/title
        'content',        // Template content with placeholders
        'description',    // Template description
        'category',       // Template category (e.g., 'announcement', 'reminder', 'prayer')
        'variables',      // JSON array of available variables
        'created_by',     // User ID who created the template
        'is_active',     // Boolean flag for template status
        'metadata'        // Additional template metadata
    ];

    protected $casts = [
        'variables' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Methods
    public function compile(array $data = [])
    {
        $content = $this->content;
        $subject = $this->subject;

        // Replace placeholders in content
        foreach ($data as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
            $subject = str_replace('{' . $key . '}', $value, $subject);
        }

        return [
            'subject' => $subject,
            'content' => $content
        ];
    }

    public function validate($data)
    {
        $missing = [];
        $required = $this->variables ?? [];

        foreach ($required as $variable) {
            if (!isset($data[$variable])) {
                $missing[] = $variable;
            }
        }

        return [
            'valid' => empty($missing),
            'missing' => $missing
        ];
    }

    public function toggleStatus()
    {
        $this->update(['is_active' => !$this->is_active]);
        return $this->is_active;
    }

    public function duplicate()
    {
        $clone = $this->replicate();
        $clone->name = $this->name . ' (Copy)';
        $clone->created_by = auth()->id();
        $clone->save();
        
        return $clone;
    }
}