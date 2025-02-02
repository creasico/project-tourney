<?php

use App\Enums\Gender;
use App\Enums\ParticipantRole;
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
        Schema::create('continents', function (Blueprint $table) {
            $table->ulid('id')->unique();

            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->json('attr')->nullable();

            $table->timestamps();
        });

        Schema::create('tournaments', function (Blueprint $table) {
            $table->ulid('id')->unique();

            $table->string('title');
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('level')->nullable();
            $table->json('attr')->nullable();
            $table->date('start_date')->nullable();
            $table->date('finish_date')->nullable();
            $table->dateTime('published_at')->nullable();

            $table->timestamps();
        });

        Schema::create('prize_pools', function (Blueprint $table) {
            $table->ulid('id')->unique();

            $table->string('label');
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('order')->nullable();

            $table->timestamps();
        });

        Schema::create('classifications', function (Blueprint $table) {
            $table->ulid('id')->unique();

            $table->string('label');
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('term')->nullable();
            $table->unsignedSmallInteger('order')->nullable();

            $table->timestamps();
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->ulid('id')->unique();
            $table->ulid('continent_id')->nullable();
            $table->ulid('class_age_id')->nullable();
            $table->ulid('class_weight_id')->nullable();

            $table->string('name');
            $table->enum('gender', Gender::toArray())->nullable();
            $table->unsignedTinyInteger('role')->nullable()->comment(
                sprintf('See %s for detail', ParticipantRole::class)
            );

            $table->timestamps();
            $table->foreign('continent_id')->references('id')->on('continents')->nullOnDelete();
            $table->foreign('class_age_id')->references('id')->on('classifications')->nullOnDelete();
            $table->foreign('class_weight_id')->references('id')->on('classifications')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
        Schema::dropIfExists('classifications');
        Schema::dropIfExists('prize_pools');
        Schema::dropIfExists('tournaments');
        Schema::dropIfExists('continents');
    }
};
