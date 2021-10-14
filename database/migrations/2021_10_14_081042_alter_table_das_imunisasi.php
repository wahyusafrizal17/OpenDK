<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDasImunisasi extends Migration
{
    public function up()
    {
        Schema::table('das_imunisasi', function($table) {
            $table->dropColumn('kecamatan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('das_imunisasi', function (Blueprint $table) {
            $table->char('kecamatan_id', 8);
        });
    }
}
