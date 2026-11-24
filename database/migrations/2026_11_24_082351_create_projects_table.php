<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_project_id')->nullable()->constrained('projects')->restrictOnDelete();
            $table->string('title');
            $table->string('description', 280);
            $table->text('story');
            $table->text('abandonment_reason');
            $table->text('original_goal');
            $table->text('what_went_wrong');
            $table->text('what_could_be_improved');
            $table->string('status')->default('draft');
            $table->boolean('adoption_available')->default(false);
            $table->timestamps();

            $table->index(['status', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
