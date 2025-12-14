<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('department_citations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('fullname');
            $table->string('unit');
            $table->string('designation');
            $table->string('kingschat');
            $table->string('phone');
            $table->string('department'); // department name
            $table->string('period')->nullable();
            $table->text('citation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_citations');
    }
};
