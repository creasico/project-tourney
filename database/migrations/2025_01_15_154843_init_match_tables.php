<?php

use App\Enums\Gender;
use App\Enums\MatchSide;
use App\Enums\MatchStatus;
use App\Enums\MedalPrize;
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
        Schema::create('tournament_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignUlid('class_id')->constrained('classifications')->cascadeOnDelete();

            $table->unsignedSmallInteger('division')->default(0);
            $table->json('attr')->nullable();
        });

        Schema::create('division_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('tournament_divisions')->cascadeOnDelete();

            $table->string('label');
            $table->enum('gender', Gender::toArray())->nullable();
            $table->json('attr')->nullable();
        });

        Schema::create('division_prizes', function (Blueprint $table) {
            $table->foreignId('division_id')->constrained('division_matches')->cascadeOnDelete();
            $table->foreignUlid('prize_id')->constrained('prize_pools')->cascadeOnDelete();

            $table->string('amount');
            $table->unsignedTinyInteger('medal')->nullable()->comment(
                sprintf('See %s for detail', MedalPrize::class)
            );
        });

        Schema::create('match_ups', function (Blueprint $table) {
            $table->ulid('id')->unique();
            $table->foreignId('division_id')->constrained('division_matches')->cascadeOnDelete();
            $table->ulid('tournament_id')->nullable();
            $table->ulid('class_id')->nullable();
            $table->ulid('next_id')->nullable();

            $table->enum('next_side', MatchSide::toArray())->nullable();
            $table->unsignedSmallInteger('party')->default(0);
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
            $table->foreignUlid('participant_id')->constrained('people')->cascadeOnDelete();
            $table->ulid('match_id')->nullable();
            $table->ulid('class_age_id')->nullable();
            $table->ulid('class_weight_id')->nullable();
            $table->ulid('reward_id')->nullable();

            $table->smallInteger('rank_number')->nullable();
            $table->smallInteger('draw_number')->default(0);
            $table->smallInteger('medal')->default(0);
            $table->string('disqualification_reason')->nullable();

            $table->dateTime('disqualified_at')->nullable();
            $table->dateTime('knocked_at')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->foreign('match_id')->references('id')->on('match_ups')->nullOnDelete();
            $table->foreign('class_age_id')->references('id')->on('classifications')->nullOnDelete();
            $table->foreign('class_weight_id')->references('id')->on('classifications')->nullOnDelete();
            $table->foreign('reward_id')->references('id')->on('prize_pools')->nullOnDelete();
        });

        Schema::create('match_revisions', function (Blueprint $table) {
            $table->id();
            $table->ulid('match_id')->nullable();

            $table->string('reason');

            $table->timestamp('created_at')->useCurrent();
            $table->foreign('match_id')->references('id')->on('match_ups')->nullOnDelete();
        });

        Schema::create('match_parties', function (Blueprint $table) {
            $table->foreignUlid('match_id')->constrained('match_ups')->cascadeOnDelete();
            $table->foreignUlid('participant_id')->constrained('people')->cascadeOnDelete();

            $table->enum('side', MatchSide::toArray())->nullable();
            $table->smallInteger('round')->nullable();
            $table->unsignedTinyInteger('status')->default(0)->comment(
                sprintf('See %s for detail', MatchStatus::class)
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_parties');
        Schema::dropIfExists('match_revisions');
        Schema::dropIfExists('participations');
        Schema::dropIfExists('match_ups');
        Schema::dropIfExists('division_prizes');
        Schema::dropIfExists('division_matches');
        Schema::dropIfExists('tournament_divisions');
    }
};
