<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing foreign keys to various tables
        
        // Donations table
        if (Schema::hasTable('donations')) {
            Schema::table('donations', function (Blueprint $table) {
                if (!$this->foreignKeyExists('donations', 'donations_member_id_foreign')) {
                    $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
                }
            });
        }

        // Pledges table
        if (Schema::hasTable('pledges')) {
            Schema::table('pledges', function (Blueprint $table) {
                if (!$this->foreignKeyExists('pledges', 'pledges_member_id_foreign')) {
                    $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
                }
            });
        }

        // Prayer requests table
        if (Schema::hasTable('prayer_requests')) {
            Schema::table('prayer_requests', function (Blueprint $table) {
                if (!$this->foreignKeyExists('prayer_requests', 'prayer_requests_member_id_foreign')) {
                    $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
                }
            });
        }

        // Messages table
        if (Schema::hasTable('messages')) {
            Schema::table('messages', function (Blueprint $table) {
                if (!$this->foreignKeyExists('messages', 'messages_sender_id_foreign')) {
                    $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }

        // Notifications table
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                if (!$this->foreignKeyExists('notifications', 'notifications_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                }
            });
        }

        // Audit logs table
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (!$this->foreignKeyExists('audit_logs', 'audit_logs_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove foreign keys
        $tables = ['donations', 'pledges', 'prayer_requests', 'messages', 'notifications', 'audit_logs'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($table) {
                    $foreignKeys = [
                        'donations' => ['donations_member_id_foreign'],
                        'pledges' => ['pledges_member_id_foreign'],
                        'prayer_requests' => ['prayer_requests_member_id_foreign'],
                        'messages' => ['messages_sender_id_foreign'],
                        'notifications' => ['notifications_user_id_foreign'],
                        'audit_logs' => ['audit_logs_user_id_foreign']
                    ];
                    
                    if (isset($foreignKeys[$table])) {
                        foreach ($foreignKeys[$table] as $foreignKey) {
                            if ($this->foreignKeyExists($table, $foreignKey)) {
                                $table->dropForeign($foreignKey);
                            }
                        }
                    }
                });
            }
        }
    }

    /**
     * Check if foreign key exists
     */
    private function foreignKeyExists($table, $name): bool
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME = ?
        ", [$table, $name]);
        
        return count($foreignKeys) > 0;
    }
};