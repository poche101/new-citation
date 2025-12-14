<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->string('kingschat')->nullable();
            $table->string('unit');
            $table->string('designation');
            $table->string('title')->nullable();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->text('description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_citations');
    }
};
