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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->nullable()->unique();
            $table->string('gender',50)->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('country_code')->nullable();
            $table->string('device_type',10)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('account_verified')->nullable();
            $table->timestamp('account_blocked')->nullable();
            $table->string('password')->default(bcrypt('password'));
            $table->longText('fcm_id')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('language',30)->default('en');
            $table->date('dob')->nullable();
            $table->string('database_path')->nullable();
			$table->string('database_name', 50)->nullable();
			$table->string('database_username', 50)->nullable();
			$table->string('database_password', 100)->nullable();
            $table->string('company_name', 100)->nullable();
            $table->string('otp', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('employement_type')->nullable();
            //$table->string('gender')->nullable();
			//$table->timestamps('lastlogin');
            $table->unsignedInteger('status')->default(1)->comment('1-Active, 2-Inactive');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

         Schema::create('role_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('user_id');
        });

         Schema::table('role_user', function($table) {
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('user_id')->references('id')->on('users');
        });

         
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('role_user');
         
    }
 
};
