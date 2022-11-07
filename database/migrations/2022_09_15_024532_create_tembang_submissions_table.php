<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTembangSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tembang_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('uid');
            $table->string('title');
            $table->string('category');
            $table->string('sub_category')->nullable();
            $table->text('lyrics');
            $table->text('lyrics_idn')->nullable();
            $table->string('meaning')->nullable();
            $table->string('mood')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('cover_source')->nullable();
            $table->string('status')->default('pending')->comment('pending/accepted/rejected');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tembang_submissions');
    }
}
