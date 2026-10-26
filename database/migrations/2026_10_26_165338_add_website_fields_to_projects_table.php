<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('is_published')->default(false)->after('status');
            $table->timestamp('published_at')->nullable()->after('is_published');
            $table->boolean('is_featured')->default(false)->after('published_at');
            $table->boolean('show_prices')->default(true)->after('is_featured');
            $table->boolean('show_sold_units')->default(false)->after('show_prices');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('show_sold_units');
            $table->string('tagline_en')->nullable()->after('location');
            $table->string('tagline_bn')->nullable()->after('tagline_en');
            $table->string('address')->nullable()->after('tagline_bn');
            $table->string('land_area')->nullable()->after('address');
            $table->unsignedTinyInteger('floors_count')->nullable()->after('land_area');
            $table->string('cover_image')->nullable()->after('description_bn');
            $table->string('video_url')->nullable()->after('cover_image');
            $table->string('brochure_path')->nullable()->after('video_url');
            $table->json('amenities')->nullable()->after('brochure_path');
            $table->json('specifications')->nullable()->after('amenities');
            $table->json('highlights')->nullable()->after('specifications');
            $table->json('nearby')->nullable()->after('highlights');
            $table->string('meta_title_en')->nullable()->after('nearby');
            $table->string('meta_title_bn')->nullable()->after('meta_title_en');
            $table->string('meta_description_en', 320)->nullable()->after('meta_title_bn');
            $table->string('meta_description_bn', 320)->nullable()->after('meta_description_en');

            $table->index(['is_published', 'status']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['is_published', 'status']);
            $table->dropIndex(['is_featured']);
            $table->dropColumn([
                'is_published', 'published_at', 'is_featured', 'show_prices', 'show_sold_units', 'sort_order',
                'tagline_en', 'tagline_bn', 'address', 'land_area', 'floors_count',
                'cover_image', 'video_url', 'brochure_path', 'amenities', 'specifications', 'highlights', 'nearby',
                'meta_title_en', 'meta_title_bn', 'meta_description_en', 'meta_description_bn',
            ]);
        });
    }
};
