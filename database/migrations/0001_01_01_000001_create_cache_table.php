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
        if (config('cache.default') !== 'database') {
            return;
        }

        $config = config('cache.stores.database');

        Schema::connection($config['connection'])->create(
            $config['table'],
            function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            }
        );

        Schema::connection($config['lock_connection'])->create(
            $config['lock_table'],
            function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('cache.default') !== 'database') {
            return;
        }

        $config = config('cache.stores.database');

        Schema::connection($config['connection'])->dropIfExists($config['table']);
        Schema::connection($config['lock_connection'])->dropIfExists($config['lock_table']);
    }
};
