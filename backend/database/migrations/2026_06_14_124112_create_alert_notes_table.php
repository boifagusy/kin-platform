<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alert_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alert_id');
            $table->unsignedBigInteger('admin_id');
            $table->text('note');
            $table->timestamps();
            
            $table->foreign('alert_id')->references('id')->on('emergency_escalations')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admin_users')->onDelete('cascade');
            
            $table->index('alert_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('alert_notes');
    }
};
