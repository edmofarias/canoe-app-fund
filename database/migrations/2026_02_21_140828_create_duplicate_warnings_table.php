<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('duplicate_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fund_id_1')->constrained('funds');
            $table->foreignId('fund_id_2')->constrained('funds');
            $table->boolean('resolved')->default(false);
            $table->timestamps();

            $table->index('resolved', 'idx_duplicate_warnings_resolved');
            $table->index(['fund_id_1', 'fund_id_2'], 'idx_duplicate_warnings_funds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('duplicate_warnings');
    }
};
