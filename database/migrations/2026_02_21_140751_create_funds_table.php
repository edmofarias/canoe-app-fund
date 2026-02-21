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
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('start_year');
            $table->foreignId('fund_manager_id')->constrained('fund_managers');
            $table->softDeletes();
            $table->timestamps();

            $table->index('name', 'idx_funds_name');
            $table->index('fund_manager_id', 'idx_funds_manager');
            $table->index('start_year', 'idx_funds_year');
            $table->index('deleted_at', 'idx_funds_deleted');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('funds');
    }
};
