<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'member_ids',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'member_ids' => 'array',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(Member::class, 'created_by');
    }

    public function members()
    {
        return Member::whereIn('id', $this->member_ids)->get();
    }

    public function messages()
    {
        return InternalMessage::where('recipient_type', 'group')
            ->whereJsonContains('recipient_ids', $this->id);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForMember($query, $memberId)
    {
        return $query->where(function ($q) use ($memberId) {
            $q->where('created_by', $memberId)
              ->orWhereJsonContains('member_ids', $memberId);
        });
    }

    // Helper methods
    public function addMember($memberId)
    {
        if (!in_array($memberId, $this->member_ids)) {
            $memberIds = $this->member_ids;
            array_push($memberIds, $memberId);
            $this->update(['member_ids' => $memberIds]);

            // TODO: Notify new member
            return true;
        }
        return false;
    }

    public function removeMember($memberId)
    {
        if (in_array($memberId, $this->member_ids)) {
            $memberIds = array_diff($this->member_ids, [$memberId]);
            $this->update(['member_ids' => array_values($memberIds)]);

            // TODO: Notify removed member
            return true;
        }
        return false;
    }

    public function addMembers($memberIds)
    {
        $newMemberIds = array_diff($memberIds, $this->member_ids);
        if (!empty($newMemberIds)) {
            $this->update([
                'member_ids' => array_values(array_unique(array_merge($this->member_ids, $newMemberIds)))
            ]);

            // TODO: Notify new members
            return true;
        }
        return false;
    }

    public function removeMembers($memberIds)
    {
        $remainingMembers = array_diff($this->member_ids, $memberIds);
        if (count($remainingMembers) !== count($this->member_ids)) {
            $this->update(['member_ids' => array_values($remainingMembers)]);

            // TODO: Notify removed members
            return true;
        }
        return false;
    }

    public function getMemberCount()
    {
        return count($this->member_ids);
    }

    public function isGroupMember($memberId)
    {
        return in_array($memberId, $this->member_ids);
    }

    public function canBeModifiedBy($member)
    {
        return $member && ($member->id === $this->created_by || $member->hasRole('admin'));
    }

    public function sendMessage($content, $subject = null, $attachments = null)
    {
        return InternalMessage::create([
            'sender_id' => auth()->id(),
            'recipient_type' => 'group',
            'recipient_ids' => [$this->id],
            'subject' => $subject ?? 'Group Message: ' . $this->name,
            'content' => $content,
            'attachments' => $attachments
        ]);
    }

    public static function getGroupStats($memberId = null)
    {
        $query = self::query();

        if ($memberId) {
            $query->forMember($memberId);
        }

        $groups = $query->get();

        return [
            'total_groups' => $groups->count(),
            'active_groups' => $groups->where('is_active', true)->count(),
            'inactive_groups' => $groups->where('is_active', false)->count(),
            'total_members' => $groups->sum(function ($group) {
                return count($group->member_ids);
            }),
            'average_members_per_group' => $groups->count() > 0
                ? round($groups->sum(function ($group) {
                    return count($group->member_ids);
                }) / $groups->count(), 2)
                : 0,
            'groups_by_size' => [
                'small' => $groups->filter(function ($group) {
                    return count($group->member_ids) <= 10;
                })->count(),
                'medium' => $groups->filter(function ($group) {
                    return count($group->member_ids) > 10 && count($group->member_ids) <= 50;
                })->count(),
                'large' => $groups->filter(function ($group) {
                    return count($group->member_ids) > 50;
                })->count()
            ]
        ];
    }

    public function archive()
    {
        $this->update(['is_active' => false]);
        // TODO: Notify members about group archival
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
        // TODO: Notify members about group activation
    }
}