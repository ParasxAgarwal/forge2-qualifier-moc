<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('board_id')->constrained()->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('list_id')->constrained()->onDelete('cascade');
            $table->integer('position')->default(0);
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 7)->default('#6b7280');
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('card_tag', function (Blueprint $table) {
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['card_id', 'tag_id']);
        });

        Schema::create('card_member', function (Blueprint $table) {
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->primary(['card_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_member');
        Schema::dropIfExists('card_tag');
        Schema::dropIfExists('members');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('lists');
        Schema::dropIfExists('boards');
    }
};