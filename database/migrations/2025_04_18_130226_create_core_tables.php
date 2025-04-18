<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaign_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('specializations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('governments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('volunteer_teams', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('team_name');
            $table->string('license_number')->unique();
            $table->string('phone');
            $table->string('bank_account_number')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('government_id')->nullable()->constrained('governments')->onDelete('set null');
            $table->string('address')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('business_informations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('address');
            $table->foreignId('team_id')->constrained('volunteer_teams')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('national_id')->unique();
            $table->string('nationality');
            $table->string('phone_number');
            $table->string('email')->unique();
            $table->string('password');
            $table->date('birth_date');
            $table->string('image')->nullable();
            $table->integer('total_points')->default(0);
            $table->foreignId('specialization_id')->constrained('specializations')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('volunteer_teams')->onDelete('cascade');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone');
            $table->string('address');
            $table->foreignId('team_id')->constrained('volunteer_teams')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('volunteers');
        Schema::dropIfExists('business_informations');
        Schema::dropIfExists('volunteer_teams');
        Schema::dropIfExists('governments');
        Schema::dropIfExists('specializations');
        Schema::dropIfExists('campaign_types');
    }
};
