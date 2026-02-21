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
        Schema::create('aliases', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('fund_id')->constrained('funds')->onDelete('cascade');
            $table->timestamps();

            $table->index('fund_id', 'idx_aliases_fund');
            $table->index('name', 'idx_aliases_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aliases');
    }
};
