<?php

use App\Enums\Gender;
use App\Enums\MatchSide;
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
            $table->json('attr')->nullable();
            $table->date('start_date')->nullable();
            $table->date('finish_date')->nullable();

            $table->timestamps();
        });

        Schema::create('rewards', function (Blueprint $table) {
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
            $table->enum('gender', Gender::toArray())->nullable();
            $table->unsignedSmallInteger('order')->nullable();

            $table->timestamps();
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->ulid('id')->unique();
            $table->ulid('continent_id')->nullable();
            $table->ulid('class_id')->nullable();

            $table->string('name');
            $table->unsignedTinyInteger('type')->nullable()->comment('0=contestant; 1=pic');
            $table->enum('gender', Gender::toArray())->nullable();

            $table->timestamps();
            $table->foreign('continent_id')->references('id')->on('continents')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('classifications')->nullOnDelete();
        });

        Schema::create('match_ups', function (Blueprint $table) {
            $table->ulid('id')->unique();
            $table->ulid('tournament_id')->nullable();
            $table->ulid('class_id')->nullable();
            $table->ulid('next_id')->nullable();

            $table->enum('next_side', MatchSide::toArray())->nullable();
            $table->smallInteger('round')->default(0);
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_bye')->default(false);
            $table->json('attr')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();

            $table->timestamps();
            $table->foreign('tournament_id')->references('id')->on('tournaments')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('classifications')->nullOnDelete();
        });

        Schema::table('match_ups', function (Blueprint $table) {
            $table->foreign('next_id')->references('id')->on('match_ups')->nullOnDelete();
        });

        Schema::create('participations', function (Blueprint $table) {
            $table->foreignUlid('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignUlid('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->ulid('match_id')->nullable();
            $table->ulid('class_id')->nullable();
            $table->ulid('reward_id')->nullable();

            $table->smallInteger('rank_number')->nullable();
            $table->smallInteger('draw_number')->default(0);
            $table->smallInteger('medal')->default(0);
            $table->string('disqualification_reason')->nullable();

            $table->dateTime('disqualified_at')->nullable();
            $table->dateTime('knocked_at')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->foreign('match_id')->references('id')->on('match_ups')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('classifications')->nullOnDelete();
            $table->foreign('reward_id')->references('id')->on('rewards')->nullOnDelete();
        });

        Schema::create('match_revisions', function (Blueprint $table) {
            $table->id();
            $table->ulid('match_id')->nullable();

            $table->string('reason');

            $table->timestamp('created_at')->useCurrent();
            $table->foreign('match_id')->references('id')->on('match_ups')->nullOnDelete();
        });

        Schema::create('match_histories', function (Blueprint $table) {
            $table->foreignUlid('match_id')->constrained('match_ups')->cascadeOnDelete();
            $table->foreignUlid('participant_id')->constrained('participants')->cascadeOnDelete();

            $table->enum('side', MatchSide::toArray())->nullable();
            $table->smallInteger('round')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->comment('0=queue; 1=win; 2=lose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_histories');
        Schema::dropIfExists('match_revisions');
        Schema::dropIfExists('participations');
        Schema::dropIfExists('match_ups');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('classifications');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('tournaments');
        Schema::dropIfExists('continents');
    }
};
