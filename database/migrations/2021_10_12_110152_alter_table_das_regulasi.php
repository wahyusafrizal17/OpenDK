<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDasRegulasi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('das_regulasi', function($table) {
            $table->dropColumn('kecamatan_id');
        });

        Schema::table('das_regulasi', function (Blueprint $table) {
            $table->integer('profil_id')->after('id')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('das_regulasi', function (Blueprint $table) {
            $table->char('kecamatan_id', 8);
        });

        Schema::table('das_regulasi', function($table) {
            $table->dropColumn('profil_id');
        });
    }
}