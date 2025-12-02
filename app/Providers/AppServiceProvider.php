<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Contact;
use App\Models\Timesheet;
use App\Models\Job;
use App\Models\Estimate;
use App\Models\User;
use App\Observers\ContactObserver;
use App\Observers\TimesheetObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $mailPaths = [
            resource_path('views/vendor/mail'),
            base_path('vendor/laravel/framework/src/Illuminate/Mail/resources/views'),
        ];
        $this->app['view']->addNamespace('mail', $mailPaths);

        // Observe contact changes for QBO auto-sync
        if (config('qbo.auto_sync')) {
            Contact::observe(ContactObserver::class);
        }
        
        // Observe timesheet changes for job cost updates
        Timesheet::observe(TimesheetObserver::class);
        
        // Define authorization gates
        $this->defineGates();
    }
    
    /**
     * Define authorization gates
     */
    protected function defineGates(): void
    {
        // Estimates
        Gate::define('view-estimates', fn (User $user) => $user->canManageEstimates());
        Gate::define('create-estimates', fn (User $user) => $user->canManageEstimates());
        Gate::define('edit-estimates', fn (User $user) => $user->canManageEstimates());
        Gate::define('delete-estimates', fn (User $user) => $user->isAdmin() || $user->isManager());
        
        // Jobs
        Gate::define('view-jobs', fn (User $user) => $user->canViewJobs());
        Gate::define('view-job', function (User $user, Job $job) {
            if ($user->hasRole([User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_OFFICE])) {
                return true;
            }
            if ($user->isForeman()) {
                return $job->foreman_id === $user->id;
            }
            // Crew can view jobs they have timesheets for
            return $job->timesheets()->where('user_id', $user->id)->exists();
        });
        Gate::define('create-jobs', fn (User $user) => $user->canManageJobs());
        Gate::define('edit-jobs', fn (User $user) => $user->canManageJobs());
        Gate::define('delete-jobs', fn (User $user) => $user->isAdmin());
        
        // Timesheets
        Gate::define('view-timesheets', fn (User $user) => $user->canManageTimesheets());
        Gate::define('view-timesheet', function (User $user, Timesheet $timesheet) {
            // Users can view their own timesheets
            if ($timesheet->user_id === $user->id) {
                return true;
            }
            // Foremen can view timesheets for their jobs
            if ($user->isForeman() && $timesheet->job->foreman_id === $user->id) {
                return true;
            }
            // Admin, Manager, Office can view all
            return $user->hasRole([User::ROLE_ADMIN, User::ROLE_MANAGER, User::ROLE_OFFICE]);
        });
        Gate::define('create-timesheets', fn (User $user) => $user->canCreateTimesheets());
        Gate::define('edit-timesheet', function (User $user, Timesheet $timesheet) {
            // Can only edit draft timesheets
            if ($timesheet->status !== 'draft') {
                return false;
            }
            // Users can edit their own draft timesheets
            if ($timesheet->user_id === $user->id) {
                return true;
            }
            // Foremen can edit crew timesheets on their jobs
            if ($user->isForeman() && $timesheet->job->foreman_id === $user->id) {
                return true;
            }
            // Admin and Manager can edit any draft timesheet
            return $user->hasRole([User::ROLE_ADMIN, User::ROLE_MANAGER]);
        });
        Gate::define('delete-timesheet', function (User $user, Timesheet $timesheet) {
            // Can only delete draft timesheets
            if ($timesheet->status !== 'draft') {
                return false;
            }
            return $user->hasRole([User::ROLE_ADMIN, User::ROLE_MANAGER]) 
                || ($timesheet->user_id === $user->id);
        });
        Gate::define('approve-timesheets', fn (User $user) => $user->canApproveTimesheets());
        Gate::define('clock-in-crew', fn (User $user) => $user->canClockInCrew());
        
        // Clients
        Gate::define('view-clients', fn (User $user) => $user->canManageClients());
        Gate::define('create-clients', fn (User $user) => $user->canManageClients());
        Gate::define('edit-clients', fn (User $user) => $user->canManageClients());
        Gate::define('delete-clients', fn (User $user) => $user->isAdmin() || $user->isManager());
        
        // Users
        Gate::define('view-users', fn (User $user) => $user->canManageUsers());
        Gate::define('create-users', fn (User $user) => $user->canManageUsers());
        Gate::define('edit-users', fn (User $user) => $user->canManageUsers());
        Gate::define('delete-users', fn (User $user) => $user->isAdmin());
        
        // Reports
        Gate::define('view-reports', fn (User $user) => $user->canViewReports());
        
        // Budgets
        Gate::define('manage-budgets', fn (User $user) => $user->canManageBudgets());
        
        // QuickBooks
        Gate::define('sync-quickbooks', fn (User $user) => $user->canSyncQuickBooks());
    }
}
