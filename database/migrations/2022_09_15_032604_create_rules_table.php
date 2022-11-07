<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tembang_submission_id');
            $table->string('name');
            $table->string('guru_dingdong'); // ex: "a, i, u, e, o" -> last letter of each lines
            $table->string('guru_wilang'); // ex: "1, 2, 3, 4, 5" -> number of syllables of each lines
            $table->integer('guru_gatra'); // ex: 5 -> number of lines of each stanza (bait)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rules');
    }
}
