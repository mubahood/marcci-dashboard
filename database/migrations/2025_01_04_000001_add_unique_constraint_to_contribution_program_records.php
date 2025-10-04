<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueConstraintToContributionProgramRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contribution_program_records', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate records for same member+program+period
            $table->unique(
                ['contribution_program_id', 'member_id', 'period_name'],
                'unique_member_program_period'
            );
            
            // Add indexes for performance optimization
            $table->index('sacco_id', 'idx_sacco_id');
            $table->index('member_id', 'idx_member_id');
            $table->index('contribution_program_id', 'idx_program_id');
            $table->index('teasurer_id', 'idx_teasurer_id');
            $table->index('is_paid', 'idx_is_paid');
            $table->index(['sacco_id', 'is_paid'], 'idx_sacco_paid');
            $table->index('period_name', 'idx_period_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contribution_program_records', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_sacco_id');
            $table->dropIndex('idx_member_id');
            $table->dropIndex('idx_program_id');
            $table->dropIndex('idx_teasurer_id');
            $table->dropIndex('idx_is_paid');
            $table->dropIndex('idx_sacco_paid');
            $table->dropIndex('idx_period_name');
            
            // Drop unique constraint
            $table->dropUnique('unique_member_program_period');
        });
    }
}
