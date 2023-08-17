<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParallelBatchs extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::getConnection()->statement('CREATE SEQUENCE parallel_batch_seq');
        Schema::create('parallel_batches', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('master_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::getConnection()->statement('DROP SEQUENCE IF EXISTS parallel_batch_seq');
        Schema::dropIfExists('parallel_batches');
    }
};
