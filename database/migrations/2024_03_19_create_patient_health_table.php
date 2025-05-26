<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('patient_healths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->decimal('berat_badan', 5, 2)->comment('dalam kg');
            $table->integer('tekanan_darah_sistole')->comment('dalam mmHg');
            $table->integer('tekanan_darah_diastole')->comment('dalam mmHg');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patient_healths');
    }
};
