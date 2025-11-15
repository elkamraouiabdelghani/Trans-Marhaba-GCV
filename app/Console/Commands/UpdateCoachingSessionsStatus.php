<?php

namespace App\Console\Commands;

use App\Models\CoachingSession;
use Illuminate\Console\Command;

class UpdateCoachingSessionsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coaching-sessions:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically update coaching sessions status from planned to in_progress when session date is today or in the past';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating coaching sessions status...');

        try {
            $today = now()->startOfDay();
            
            // Update sessions that are 'planned' and have date <= today
            $updatedCount = CoachingSession::where('status', 'planned')
                ->whereNotNull('date')
                ->whereDate('date', '<=', $today)
                ->update(['status' => 'in_progress']);

            if ($updatedCount > 0) {
                $this->info("✓ Successfully updated {$updatedCount} coaching session(s) to 'in_progress' status.");
            } else {
                $this->info('✓ No coaching sessions needed to be updated.');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Error updating coaching sessions: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
