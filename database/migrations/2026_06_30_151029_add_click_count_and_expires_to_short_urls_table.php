<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClickCountAndExpiresToShortUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('short_urls', function (Blueprint $table) {
            // Atomic click counter — incremented async via queued jobs
            $table->unsignedBigInteger('click_count')->default(0)->after('company_id');

            // Optional expiry — null means never expires
            $table->timestamp('expires_at')->nullable()->after('click_count');

            // Critical: index on short_code for O(log n) lookups at 1M+ rows
            // (already unique, but ensure the index is explicit for query planner)
            $table->index('short_code', 'idx_short_urls_short_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('short_urls', function (Blueprint $table) {
            $table->dropIndex('idx_short_urls_short_code');
            $table->dropColumn(['click_count', 'expires_at']);
        });
    }
}
