<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('volunteer_teams', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
        });
    }

    public function down()
    {
        Schema::table('volunteer_teams', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}; 