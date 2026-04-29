<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title')->nullable();
            $table->json('messages')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_custom_instructions', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('ai_message_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->nullable()->constrained('ai_chats')->onDelete('cascade');
            $table->string('role'); // user, assistant
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_message_logs');
        Schema::dropIfExists('ai_custom_instructions');
        Schema::dropIfExists('ai_chats');
    }
};
