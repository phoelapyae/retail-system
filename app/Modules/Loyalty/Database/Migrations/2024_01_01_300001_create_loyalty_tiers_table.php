<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('min_points');
            $table->integer('max_points')->nullable();
            $table->json('benefits')->nullable();
            $table->decimal('multiplier', 3, 2)->default(1.00);
            $table->timestamps();
        });
        
        // Add loyalty_tier_id to customers table
        // Schema::table('customers', function (Blueprint $table) {
        //     $table->foreignId('loyalty_tier_id')->nullable()->constrained()->onDelete('set null');
        // });
    }
    
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['loyalty_tier_id']);
            $table->dropColumn('loyalty_tier_id');
        });
        
        Schema::dropIfExists('loyalty_tiers');
    }
};