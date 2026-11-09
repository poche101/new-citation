<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citation_attachments', function (Blueprint $table) {
            $table->id();

            // Belongs to either a DepartmentCitation or a GroupCitation
            $table->morphs('attachable');

            $table->string('kind', 20);              // document | video | audio
            $table->string('original_name');         // name shown to admins
            $table->string('path');                  // location on the disk
            $table->string('disk', 40)->default('local');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0); // bytes

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citation_attachments');
    }
};
