<?php

use App\Enums\MatchBye;
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
        Schema::create('match_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignUlid('class_id')->constrained('classifications')->cascadeOnDelete();

            $table->unsignedSmallInteger('division')->default(0);
            $table->enum('bye', MatchBye::toArray())->nullable()->comment(
                sprintf('See %s for detail', MatchBye::class)
            );

            $table->json('attr')->nullable();
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable();

            $table->string('label');
            $table->json('attr')->nullable();

            $table->foreign('group_id')->references('id')->on('match_groups')->nullOnDelete();
        });

        Schema::create('division_prizes', function (Blueprint $table) {
            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->foreignUlid('prize_id')->constrained('prize_pools')->cascadeOnDelete();

            $table->string('amount');
            $table->unsignedTinyInteger('medal')->nullable()->comment(
                sprintf('See %s for detail', MedalPrize::class)
            );
        });

        Schema::create('matchups', function (Blueprint $table) {
            $table->ulid('id')->unique();
            $table->foreignId('division_id')->nullable();
            $table->foreignUlid('tournament_id')->nullable();
            $table->foreignUlid('class_id')->nullable();
            $table->foreignUlid('next_id')->nullable();

            $table->enum('next_side', MatchSide::toArray())->nullable();
            $table->unsignedSmallInteger('party_number')->default(0);
            $table->smallInteger('round_number')->default(0);
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_bye')->default(false);
            $table->json('attr')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();

            $table->timestamps();
            $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            $table->foreign('tournament_id')->references('id')->on('tournaments')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('classifications')->nullOnDelete();
        });

        Schema::table('matchups', function (Blueprint $table) {
            $table->foreign('next_id')->references('id')->on('matchups')->nullOnDelete();
        });

        Schema::create('participations', function (Blueprint $table) {
            $table->foreignUlid('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignUlid('participant_id')->constrained('people')->cascadeOnDelete();
            $table->foreignUlid('match_id')->nullable();

            $table->smallInteger('rank_number')->default(0);
            $table->smallInteger('draw_number')->default(0);
            $table->smallInteger('medal')->default(0);
            $table->string('disqualification_reason')->nullable();

            $table->dateTime('disqualified_at')->nullable();
            $table->dateTime('knocked_at')->nullable();
            $table->dateTime('verified_at')->nullable();

            $table->foreign('match_id')->references('id')->on('matchups')->nullOnDelete();
        });

        Schema::create('match_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('match_id')->nullable();

            $table->string('reason');

            $table->timestamp('created_at')->useCurrent();
            $table->foreign('match_id')->references('id')->on('matchups')->nullOnDelete();
        });

        Schema::create('match_parties', function (Blueprint $table) {
            $table->foreignUlid('match_id')->constrained('matchups')->cascadeOnDelete();
            $table->foreignUlid('participant_id')->constrained('people')->cascadeOnDelete();

            $table->enum('side', MatchSide::toArray())->nullable();
            $table->unsignedTinyInteger('status')->default(MatchStatus::Queue)->comment(
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
        Schema::dropIfExists('matchups');
        Schema::dropIfExists('division_prizes');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('match_groups');
    }
};
