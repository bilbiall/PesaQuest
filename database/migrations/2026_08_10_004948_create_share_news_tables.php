<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_news_templates', function (Blueprint $table) {
            $table->id();
            // {name} is substituted at generation time — the chosen share's
            // name for company scope, or a friendly sector phrase for sector
            // scope — so one template can read fresh across many generations.
            $table->string('headline', 200);
            $table->text('flavor');
            $table->text('lesson');
            $table->enum('scope', ['company', 'sector']);
            $table->string('sector', 40)->nullable();
            $table->enum('sentiment', ['up', 'down']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('share_news_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('share_news_templates')->nullOnDelete();
            // Rendered text stored verbatim — the archive must read the same
            // years later even if the template it came from is later edited.
            $table->string('headline', 200);
            $table->text('flavor');
            $table->text('lesson');
            $table->enum('scope', ['company', 'sector']);
            $table->foreignId('share_id')->nullable()->constrained('shares')->nullOnDelete();
            $table->string('sector', 40)->nullable();
            // Whether this headline is a real signal or a dud — hidden from
            // the player, revealed only once it resolves.
            $table->boolean('is_true');
            $table->enum('direction', ['up', 'down']);
            $table->decimal('magnitude_pct', 5, 2);
            // dateTime, not timestamp — MySQL's legacy "first TIMESTAMP column
            // auto-defaults" rule collides with a second non-nullable TIMESTAMP
            // column in the same table under strict mode.
            $table->dateTime('published_at');
            $table->dateTime('effect_at');
            $table->enum('status', ['scheduled', 'resolved'])->default('scheduled');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('forum_topic_id')->nullable()->constrained('forum_topics')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'effect_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_news_items');
        Schema::dropIfExists('share_news_templates');
    }
};
