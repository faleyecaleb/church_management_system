<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            if (!Schema::hasColumn('members', 'other_names')) {
                $table->string('other_names')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('members', 'birth_day')) {
                $table->string('birth_day')->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('members', 'birth_month')) {
                $table->string('birth_month')->nullable()->after('birth_day');
            }
            if (!Schema::hasColumn('members', 'marital_status')) {
                $table->string('marital_status')->nullable();
            }
            if (!Schema::hasColumn('members', 'partner_name')) {
                $table->string('partner_name')->nullable();
            }
            if (!Schema::hasColumn('members', 'state_of_origin')) {
                $table->string('state_of_origin')->nullable();
            }
            if (!Schema::hasColumn('members', 'lga_of_origin')) {
                $table->string('lga_of_origin')->nullable();
            }
            if (!Schema::hasColumn('members', 'state_of_residence')) {
                $table->string('state_of_residence')->nullable();
            }
            if (!Schema::hasColumn('members', 'city_of_residence')) {
                $table->string('city_of_residence')->nullable();
            }
            if (!Schema::hasColumn('members', 'profession')) {
                $table->string('profession')->nullable();
            }
            if (!Schema::hasColumn('members', 'church_group')) {
                $table->string('church_group')->nullable();
            }
            if (!Schema::hasColumn('members', 'is_baptized')) {
                $table->string('is_baptized')->nullable();
            }
            if (!Schema::hasColumn('members', 'baptism_year_and_place')) {
                $table->string('baptism_year_and_place')->nullable();
            }
            if (!Schema::hasColumn('members', 'baptism_church_name')) {
                $table->string('baptism_church_name')->nullable();
            }
            if (!Schema::hasColumn('members', 'spiritual_gifts')) {
                $table->text('spiritual_gifts')->nullable();
            }
            if (!Schema::hasColumn('members', 'emergency_contact_details')) {
                $table->text('emergency_contact_details')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'other_names', 'birth_day', 'birth_month', 'marital_status', 
                'partner_name', 'state_of_origin', 'lga_of_origin', 
                'state_of_residence', 'city_of_residence', 'profession', 
                'church_group', 'is_baptized', 'baptism_year_and_place', 
                'baptism_church_name', 'spiritual_gifts', 'emergency_contact_details'
            ]);
        });
    }
};
