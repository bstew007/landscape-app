<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timesheet;
use App\Models\Job;
use App\Models\User;
use Carbon\Carbon;

class TimesheetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some jobs and users
        $jobs = Job::with('workAreas')->limit(5)->get();
        $users = User::limit(3)->get();

        if ($jobs->isEmpty() || $users->isEmpty()) {
            $this->command->warn('⚠️  No jobs or users found. Please seed jobs and users first.');
            return;
        }

        $this->command->info('🌱 Seeding timesheets...');

        $statuses = ['draft', 'submitted', 'approved', 'rejected'];
        $count = 0;

        foreach ($jobs as $job) {
            foreach ($users as $user) {
                // Create 3-5 timesheets per job/user combo
                $numberOfTimesheets = rand(3, 5);

                for ($i = 0; $i < $numberOfTimesheets; $i++) {
                    $workDate = Carbon::today()->subDays(rand(0, 14));
                    $clockIn = $workDate->copy()->setTime(rand(7, 9), rand(0, 59));
                    $clockOut = $clockIn->copy()->addHours(rand(6, 9))->addMinutes(rand(0, 59));
                    $breakMinutes = [0, 15, 30, 45, 60][rand(0, 4)];

                    // Calculate total hours
                    $totalMinutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
                    $totalHours = round($totalMinutes / 60, 2);

                    // Random status, weighted towards submitted/approved
                    $statusWeights = [
                        'draft' => 10,
                        'submitted' => 40,
                        'approved' => 45,
                        'rejected' => 5,
                    ];
                    $status = $this->weightedRandom($statusWeights);

                    $timesheet = new Timesheet([
                        'job_id' => $job->id,
                        'user_id' => $user->id,
                        'job_work_area_id' => $job->workAreas->isNotEmpty() ? $job->workAreas->random()->id : null,
                        'work_date' => $workDate,
                        'clock_in' => $clockIn,
                        'clock_out' => $status === 'draft' && rand(0, 2) === 0 ? null : $clockOut,
                        'break_minutes' => $breakMinutes,
                        'total_hours' => $totalHours,
                        'status' => $status,
                        'notes' => rand(0, 2) === 0 ? $this->getRandomNote() : null,
                    ]);

                    // Add approval data if approved
                    if ($status === 'approved') {
                        $approver = User::where('id', '!=', $user->id)->inRandomOrder()->first() ?? $user;
                        $timesheet->approved_by = $approver->id;
                        $timesheet->approved_at = $workDate->copy()->addDay()->setTime(rand(15, 18), rand(0, 59));
                    }

                    // Add rejection reason if rejected
                    if ($status === 'rejected') {
                        $timesheet->rejection_reason = $this->getRandomRejectionReason();
                    }

                    $timesheet->save();
                    $count++;
                }
            }
        }

        $this->command->info("✅ Created {$count} timesheets");
        $this->command->info('📊 Status breakdown:');
        $this->command->info('   Draft: ' . Timesheet::where('status', 'draft')->count());
        $this->command->info('   Submitted: ' . Timesheet::where('status', 'submitted')->count());
        $this->command->info('   Approved: ' . Timesheet::where('status', 'approved')->count());
        $this->command->info('   Rejected: ' . Timesheet::where('status', 'rejected')->count());
    }

    /**
     * Get weighted random value
     */
    private function weightedRandom(array $weights): string
    {
        $rand = rand(1, array_sum($weights));
        
        foreach ($weights as $key => $weight) {
            $rand -= $weight;
            if ($rand <= 0) {
                return $key;
            }
        }
        
        return array_key_first($weights);
    }

    /**
     * Get random timesheet note
     */
    private function getRandomNote(): string
    {
        $notes = [
            'Completed base preparation and grading',
            'Installed paver base and compacted',
            'Finished retaining wall foundation',
            'Weather delay - rain in afternoon',
            'Equipment issues with compactor',
            'Completed planting in south section',
            'Mulch delivery and spreading',
            'Irrigation installation and testing',
            'Cleanup and site preparation',
            'Final grading and smoothing',
            'Plant installation - all zones complete',
            'Sod installation - front yard',
            'Fence post installation',
            'Gate hardware installed and adjusted',
            'Drainage work completed',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Get random rejection reason
     */
    private function getRandomRejectionReason(): string
    {
        $reasons = [
            'Hours do not match job schedule. Please verify clock in/out times.',
            'Break time seems excessive for the work performed.',
            'No work was scheduled on this date. Please check work date.',
            'Duplicate entry found. Please delete and resubmit.',
            'Missing required notes for this job phase.',
        ];

        return $reasons[array_rand($reasons)];
    }
}
