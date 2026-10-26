<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('apartment');
            $table->string('status')->default('ongoing');
            $table->string('location')->nullable();
            $table->decimal('map_lat', 10, 7)->nullable();
            $table->decimal('map_lng', 10, 7)->nullable();
            $table->date('launch_date')->nullable();
            $table->date('handover_date')->nullable();
            $table->text('description_en')->nullable();
            $table->text('description_bn')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('floors_count')->default(1);
            $table->timestamps();

            $table->unique(['project_id', 'name']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('block_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number');
            $table->string('type')->default('apartment');
            $table->decimal('size_sqft', 12, 2);
            $table->string('facing')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('parking_count')->default(0);
            $table->decimal('base_rate', 12, 2)->default(0);
            $table->decimal('floor_premium', 12, 2)->default(0);
            $table->decimal('parking_price', 12, 2)->default(0);
            $table->decimal('utility_charges', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->string('status')->default('available');
            $table->timestamps();

            $table->unique(['project_id', 'number']);
            $table->index(['status', 'project_id']);
        });

        Schema::create('unit_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['unit_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_status_history');
        Schema::dropIfExists('units');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('projects');
    }
};
