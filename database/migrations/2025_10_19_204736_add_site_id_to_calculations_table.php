<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('calculations', function (Blueprint $table) {
       // $table->foreignId('site_visit_id')->constrained()->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('calculations', function (Blueprint $table) {
        $table->dropForeign(['site_visit_id']);
        $table->dropColumn('site_visit_id');
    });
}

};
