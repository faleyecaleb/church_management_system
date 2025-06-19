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
        Schema::create('analytics_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('category');  // membership, attendance, financial, communication, growth
            $table->string('metric_key');
            $table->string('metric_name');
            $table->string('data_type');  // number, percentage, currency, text
            $table->text('description')->nullable();
            $table->json('calculation_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category', 'metric_key']);
            $table->index('is_active');
        });

        Schema::create('analytics_data_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metric_id')->constrained('analytics_metrics');
            $table->string('dimension')->nullable();  // Optional grouping dimension
            $table->string('dimension_value')->nullable();
            $table->decimal('value', 15, 2);
            $table->timestamp('recorded_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['metric_id', 'recorded_at']);
            $table->index(['dimension', 'dimension_value']);
        });

        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');  // standard, custom
            $table->text('description')->nullable();
            $table->json('metrics');  // Array of metric IDs to include
            $table->json('layout')->nullable();  // Report layout configuration
            $table->json('filters')->nullable();  // Default filter settings
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
        });

        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('report_templates');
            $table->string('frequency');  // daily, weekly, monthly
            $table->json('schedule_config');  // Detailed schedule configuration
            $table->json('recipients');  // Array of user IDs or email addresses
            $table->string('format');  // pdf, excel, csv
            $table->json('filters')->nullable();  // Report-specific filters
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('frequency');
            $table->index('next_run_at');
            $table->index('is_active');
        });

        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('report_templates');
            $table->foreignId('scheduled_report_id')->nullable()->constrained('scheduled_reports');
            $table->foreignId('generated_by')->constrained('users');
            $table->string('format');  // pdf, excel, csv
            $table->string('file_path');
            $table->unsignedInteger('file_size')->nullable();
            $table->json('filters_used')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('format');
            $table->index('expires_at');
        });

        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');  // chart, metric, table
            $table->json('configuration');  // Widget-specific configuration
            $table->json('data_source');  // Metrics and calculation rules
            $table->json('display_options')->nullable();  // Visual customization options
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });

        Schema::create('user_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('widget_id')->constrained('dashboard_widgets')->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->json('custom_configuration')->nullable();  // User-specific widget settings
            $table->timestamps();

            $table->unique(['user_id', 'widget_id']);
            $table->index('position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_widgets');
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('scheduled_reports');
        Schema::dropIfExists('report_templates');
        Schema::dropIfExists('analytics_data_points');
        Schema::dropIfExists('analytics_metrics');
    }
};