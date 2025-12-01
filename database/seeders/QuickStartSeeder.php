<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\Property;
use App\Models\Estimate;
use App\Models\Job;
use App\Models\JobWorkArea;
use App\Models\Timesheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class QuickStartSeeder extends Seeder
{
    /**
     * Seed the database with sample data for testing timesheets
     */
    public function run(): void
    {
        $this->command->info('🚀 Quick Start Seeder - Creating test data...');

        // 1. Create Users
        $this->command->info('👥 Creating users...');
        
        $admin = User::firstOrCreate(
            ['email' => 'admin@cfllandscape.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $foreman = User::firstOrCreate(
            ['email' => 'foreman@cfllandscape.com'],
            [
                'name' => 'Mike Johnson',
                'password' => Hash::make('password'),
                'role' => 'foreman',
            ]
        );

        $crew1 = User::firstOrCreate(
            ['email' => 'crew1@cfllandscape.com'],
            [
                'name' => 'Carlos Rodriguez',
                'password' => Hash::make('password'),
                'role' => 'crew',
            ]
        );

        $crew2 = User::firstOrCreate(
            ['email' => 'crew2@cfllandscape.com'],
            [
                'name' => 'James Wilson',
                'password' => Hash::make('password'),
                'role' => 'crew',
            ]
        );

        $this->command->info('✅ Created 4 users');

        // 2. Create Clients
        $this->command->info('🏢 Creating clients...');
        
        $client1 = Client::firstOrCreate(
            ['email' => 'john.smith@email.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'company_name' => 'Smith Residence',
                'phone' => '555-0101',
                'contact_type' => 'residential',
            ]
        );

        $client2 = Client::firstOrCreate(
            ['email' => 'sarah.jones@email.com'],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Jones',
                'company_name' => 'Jones Family Trust',
                'phone' => '555-0102',
                'contact_type' => 'residential',
            ]
        );

        $this->command->info('✅ Created 2 clients');

        // 3. Create Properties
        $this->command->info('🏡 Creating properties...');
        
        $property1 = Property::firstOrCreate(
            ['client_id' => $client1->id, 'name' => 'Smith Main Residence'],
            [
                'type' => 'residential',
                'address_line1' => '123 Main Street',
                'city' => 'Charlotte',
                'state' => 'NC',
                'postal_code' => '28202',
            ]
        );

        $property2 = Property::firstOrCreate(
            ['client_id' => $client2->id, 'name' => 'Jones Family Home'],
            [
                'type' => 'residential',
                'address_line1' => '456 Oak Avenue',
                'city' => 'Charlotte',
                'state' => 'NC',
                'postal_code' => '28203',
            ]
        );

        $this->command->info('✅ Created 2 properties');

        // 4. Create Estimates (skip if they don't match schema)
        $this->command->info('📋 Creating estimates...');
        
        try {
            $estimate1 = Estimate::create([
                'client_id' => $client1->id,
                'property_id' => $property1->id,
                'title' => 'Backyard Paver Patio',
                'status' => 'approved',
                'total_price' => 15000.00,
                'total_cost' => 9000.00,
            ]);

            $estimate2 = Estimate::create([
                'client_id' => $client2->id,
                'property_id' => $property2->id,
                'title' => 'Front Yard Landscape Renovation',
                'status' => 'approved',
                'total_price' => 22000.00,
                'total_cost' => 14000.00,
            ]);
            $this->command->info('✅ Created 2 estimates');
        } catch (\Exception $e) {
            // Use existing estimates or skip
            $estimate1 = Estimate::first() ?? Estimate::create(['client_id' => $client1->id, 'property_id' => $property1->id]);
            $estimate2 = Estimate::skip(1)->first() ?? Estimate::create(['client_id' => $client2->id, 'property_id' => $property2->id]);
            $this->command->warn('⚠️  Using existing estimates or created minimal ones');
        }


        // 5. Create Jobs
        $this->command->info('💼 Creating jobs...');
        
        $job1 = Job::where('job_number', 'JOB-2025-001')->first();
        if (!$job1) {
            $job1 = Job::create([
                'job_number' => 'JOB-2025-001',
                'estimate_id' => $estimate1->id,
                'client_id' => $client1->id,
                'property_id' => $property1->id,
                'foreman_id' => $foreman->id,
                'title' => 'Smith - Paver Patio Installation',
                'status' => 'in_progress',
            ]);
        }

        $job2 = Job::where('job_number', 'JOB-2025-002')->first();
        if (!$job2) {
            $job2 = Job::create([
                'job_number' => 'JOB-2025-002',
                'estimate_id' => $estimate2->id,
                'client_id' => $client2->id,
                'property_id' => $property2->id,
                'foreman_id' => $foreman->id,
                'title' => 'Jones - Landscape Renovation',
                'status' => 'in_progress',
            ]);
        }

        $this->command->info('✅ Created/found 2 jobs');

        // 6. Create Work Areas
        $this->command->info('📍 Creating work areas...');
        
        $workArea1 = JobWorkArea::create([
            'job_id' => $job1->id,
            'name' => 'Base Preparation',
            'description' => 'Excavation and base material installation',
        ]);

        $workArea2 = JobWorkArea::create([
            'job_id' => $job1->id,
            'name' => 'Paver Installation',
            'description' => 'Paver laying and cutting',
        ]);

        $workArea3 = JobWorkArea::create([
            'job_id' => $job2->id,
            'name' => 'Site Preparation',
            'description' => 'Clearing and grading',
        ]);

        $workArea4 = JobWorkArea::create([
            'job_id' => $job2->id,
            'name' => 'Planting',
            'description' => 'Plant installation',
        ]);

        $this->command->info('✅ Created 4 work areas');

        // 7. Create Timesheets
        $this->command->info('⏱️  Creating timesheets...');
        
        $users = [$crew1, $crew2];
        $timesheetCount = 0;

        // Create timesheets for the past week
        for ($day = 7; $day >= 0; $day--) {
            $workDate = Carbon::today()->subDays($day);
            
            // Skip weekends
            if ($workDate->isWeekend()) {
                continue;
            }

            foreach ($users as $user) {
                // Job 1 timesheets
                if ($day >= 3) {
                    $clockIn = $workDate->copy()->setTime(8, 0);
                    $clockOut = $clockIn->copy()->addHours(8);
                    
                    $timesheet = Timesheet::create([
                        'job_id' => $job1->id,
                        'user_id' => $user->id,
                        'job_work_area_id' => $day >= 5 ? $workArea1->id : $workArea2->id,
                        'work_date' => $workDate,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'break_minutes' => 30,
                        'total_hours' => 7.5,
                        'status' => $day >= 2 ? 'approved' : 'submitted',
                        'notes' => $day >= 5 ? 'Base work completed' : 'Paver installation progress',
                        'approved_by' => $day >= 2 ? $foreman->id : null,
                        'approved_at' => $day >= 2 ? $workDate->copy()->addDay()->setTime(17, 0) : null,
                    ]);
                    $timesheetCount++;
                }

                // Job 2 timesheets (last 3 days)
                if ($day <= 3 && $day > 0) {
                    $clockIn = $workDate->copy()->setTime(8, 30);
                    $clockOut = $clockIn->copy()->addHours(7)->addMinutes(30);
                    
                    $timesheet = Timesheet::create([
                        'job_id' => $job2->id,
                        'user_id' => $user->id,
                        'job_work_area_id' => $workArea3->id,
                        'work_date' => $workDate,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'break_minutes' => 30,
                        'total_hours' => 7.0,
                        'status' => $day >= 2 ? 'approved' : 'submitted',
                        'notes' => 'Site clearing and grading',
                        'approved_by' => $day >= 2 ? $foreman->id : null,
                        'approved_at' => $day >= 2 ? $workDate->copy()->addDay()->setTime(16, 30) : null,
                    ]);
                    $timesheetCount++;
                }
            }
        }

        // Create one active (clocked in) timesheet for today
        $today = Carbon::today();
        if (!$today->isWeekend()) {
            $activeTimesheet = Timesheet::create([
                'job_id' => $job1->id,
                'user_id' => $crew1->id,
                'job_work_area_id' => $workArea2->id,
                'work_date' => $today,
                'clock_in' => $today->copy()->setTime(8, 15),
                'clock_out' => null, // Still clocked in
                'break_minutes' => 0,
                'total_hours' => null,
                'status' => 'draft',
                'notes' => null,
            ]);
            $timesheetCount++;
        }

        $this->command->info("✅ Created {$timesheetCount} timesheets");

        // Update job costs based on approved timesheets
        foreach ([$job1, $job2] as $job) {
            $approvedHours = Timesheet::where('job_id', $job->id)
                ->where('status', 'approved')
                ->sum('total_hours');
            
            $avgRate = 27.00; // Average of work area rates
            if ($job->actual_labor_cost ?? false) {
                $job->actual_labor_cost = $approvedHours * $avgRate;
                $job->save();
            }
        }

        $this->command->info('💰 Updated job costs (if applicable)');

        // Summary
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   Users: 4 (1 admin, 1 foreman, 2 crew)');
        $this->command->info('   Jobs: 2 (both in progress)');
        $this->command->info('   Work Areas: 4');
        $this->command->info('   Timesheets: ' . $timesheetCount);
        $this->command->info('');
        $this->command->info('🔑 Login Credentials:');
        $this->command->info('   Admin: admin@cfllandscape.com / password');
        $this->command->info('   Foreman: foreman@cfllandscape.com / password');
        $this->command->info('   Crew 1: crew1@cfllandscape.com / password');
        $this->command->info('   Crew 2: crew2@cfllandscape.com / password');
        $this->command->info('');
        $this->command->info('🌐 Test URLs:');
        $this->command->info('   Timesheets: http://localhost:8000/timesheets');
        $this->command->info('   Approve: http://localhost:8000/timesheets-approve');
        $this->command->info('   Job 1: http://localhost:8000/jobs/' . $job1->id);
        $this->command->info('   Job 2: http://localhost:8000/jobs/' . $job2->id);
    }
}
