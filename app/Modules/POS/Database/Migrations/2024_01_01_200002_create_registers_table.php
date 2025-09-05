<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('registers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->enum('status', ['open', 'closed'])->default('closed');
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->timestamps();
        });
        
        // Add register_id foreign key to pos_sales
        // Schema::table('pos_sales', function (Blueprint $table) {
        //     $table->foreign('register_id')->references('id')->on('registers')->onDelete('set null');
        // });
    }
    
    public function down()
    {
        // Schema::table('pos_sales', function (Blueprint $table) {
        //     $table->dropForeign(['register_id']);
        // });
        
        Schema::dropIfExists('registers');
    }
};