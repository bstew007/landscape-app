# Role-Based Permission System

## Overview
The application implements a comprehensive role-based access control (RBAC) system with six distinct user roles, each with specific permissions for different areas of the application.

## User Roles

### 1. Admin (`admin`)
**Full system access** - Complete control over all features and settings.

**Permissions:**
- All CRUD operations on estimates, jobs, timesheets, clients, and users
- Full access to admin panel (budgets, catalogs, settings, divisions, cost codes)
- Can approve/reject timesheets
- Can clock in crew members
- Can sync with QuickBooks
- Can view all reports and analytics
- Can manage company settings

### 2. Manager (`manager`)
**Operational management** - Can manage estimates, jobs, and business operations.

**Permissions:**
- Create, edit, and delete estimates
- Create and manage jobs
- View all jobs and estimates
- Approve/reject timesheets
- Access to reports and analytics
- Manage catalogs (materials, labor, production rates)
- Limited admin access (no user management or company settings)

### 3. Foreman (`foreman`)
**Field supervision** - Can manage assigned jobs and crew timesheets.

**Permissions:**
- View assigned jobs only (jobs where they are the foreman)
- Create and edit timesheets for assigned jobs
- Approve/reject timesheets for their crew
- Clock in crew members
- View estimates (read-only)
- Cannot create or edit estimates
- Cannot access admin panel or catalogs

### 4. Crew (`crew`)
**Field worker** - Can manage their own timesheets.

**Permissions:**
- Create and edit their own timesheets (draft status only)
- View jobs they're assigned to
- Clock in/out
- Cannot approve timesheets
- Cannot view estimates
- Cannot access admin panel

### 5. Office (`office`)
**Administrative support** - Can manage estimates and reports.

**Permissions:**
- Create, edit, and view estimates
- View jobs (read-only)
- Access to reports and analytics
- Cannot approve timesheets
- Cannot clock in crew
- Cannot access admin panel (except reports)

### 6. User (`user`)
**Basic access** - Default role with minimal permissions.

**Permissions:**
- View own profile
- View dashboard
- Limited read-only access
- Cannot create or edit any resources

## Implementation Details

### User Model Methods

#### Role Checking
```php
$user->isAdmin()      // Returns true if user is admin
$user->isManager()    // Returns true if user is manager
$user->isForeman()    // Returns true if user is foreman
$user->isCrew()       // Returns true if user is crew
$user->isOffice()     // Returns true if user is office
$user->hasRole(['admin', 'manager'])  // Check multiple roles
```

#### Permission Checking
```php
$user->canManageEstimates()      // Can create/edit estimates
$user->canManageJobs()           // Can create/edit jobs
$user->canApproveTimesheets()    // Can approve/reject timesheets
$user->canClockInCrew()          // Can clock in crew members
$user->canManageUsers()          // Can manage user accounts
$user->canAccessAdmin()          // Can access admin panel
$user->canViewReports()          // Can view reports
$user->canSyncQuickBooks()       // Can sync with QuickBooks
```

### Route Protection

Routes are protected using the `role` middleware:

```php
// Admin only
Route::middleware(['role:admin'])->group(function () {
    Route::resource('admin/users', UserController::class);
});

// Admin and Manager
Route::middleware(['role:admin,manager'])->group(function () {
    Route::resource('materials', MaterialController::class);
});

// Admin, Manager, and Foreman
Route::middleware(['role:admin,manager,foreman'])->group(function () {
    Route::get('timesheets-approve', [TimesheetController::class, 'approvalPage']);
});
```

### Gates

Gates provide fine-grained authorization checks in controllers and views:

```php
// In Controllers
Gate::authorize('edit-estimate', $estimate);
Gate::authorize('approve-timesheets');

// In Blade Views
@can('edit-estimate', $estimate)
    <button>Edit Estimate</button>
@endcan

@can('approve-timesheets')
    <a href="{{ route('timesheets.approve') }}">Approve Timesheets</a>
@endcan
```

### Available Gates

**Estimates:**
- `view-estimates` - View estimate list
- `create-estimate` - Create new estimates
- `edit-estimate` - Edit existing estimate (checks ownership for office users)
- `delete-estimate` - Delete estimates

**Jobs:**
- `view-jobs` - View job list
- `view-job` - View specific job (checks foreman assignment)
- `create-job` - Create new jobs
- `edit-job` - Edit existing job
- `delete-job` - Delete jobs

**Timesheets:**
- `view-timesheets` - View timesheet list
- `view-timesheet` - View specific timesheet
- `create-timesheet` - Create new timesheets
- `edit-timesheet` - Edit timesheet (checks ownership and status)
- `delete-timesheet` - Delete timesheets
- `approve-timesheets` - Approve/reject timesheets
- `clock-in-crew` - Clock in crew members

**Clients:**
- `view-clients`, `create-client`, `edit-client`, `delete-client`

**Users:**
- `manage-users` - User management (admin only)

**Reports:**
- `view-reports` - Access analytics and reports

**Budgets:**
- `manage-budgets` - Company budget management (admin only)

**Catalogs:**
- `manage-catalogs` - Materials, labor, production rates (admin/manager only)

**QuickBooks:**
- `sync-quickbooks` - QuickBooks integration (admin/manager only)

## Usage Examples

### In Controllers

```php
class EstimateController extends Controller
{
    public function edit(Estimate $estimate)
    {
        // Authorize the action
        Gate::authorize('edit-estimate', $estimate);
        
        return view('estimates.edit', compact('estimate'));
    }
    
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::user()->canManageEstimates()) {
            abort(403, 'You do not have permission to create estimates.');
        }
        
        // ... create estimate
    }
}
```

### In Blade Views

```blade
{{-- Conditionally show UI elements --}}
@can('create-estimate')
    <a href="{{ route('estimates.create') }}" class="btn btn-primary">
        New Estimate
    </a>
@endcan

@can('approve-timesheets')
    <a href="{{ route('timesheets.approve') }}">
        Approve Timesheets
    </a>
@endcan

{{-- Check user role directly --}}
@if(auth()->user()->isForeman())
    <p>Welcome, Foreman! Here are your assigned jobs:</p>
@endif

{{-- Multiple permissions --}}
@canany(['edit-estimate', 'delete-estimate'], $estimate)
    <div class="actions">
        @can('edit-estimate', $estimate)
            <button>Edit</button>
        @endcan
        @can('delete-estimate', $estimate)
            <button>Delete</button>
        @endcan
    </div>
@endcanany
```

### Query Scopes

Filter jobs based on user role:

```php
// In Controller
$jobs = Job::viewableBy(Auth::user())->get();

// The scope automatically filters:
// - Foreman: Only jobs where they are the foreman
// - Admin/Manager: All jobs
// - Others: Empty collection
```

## Testing

### Seed Test Users

Run the seeder to create test users with different roles:

```bash
php artisan db:seed --class=UserRoleSeeder
```

This creates:
- `admin@example.com` (Admin)
- `manager@example.com` (Manager)
- `foreman@example.com` (Foreman)
- `crew@example.com` (Crew)
- `office@example.com` (Office)

All passwords: `password`

### Testing Checklist

1. **Admin User**
   - ✅ Can access all admin routes
   - ✅ Can create/edit/delete all resources
   - ✅ Can manage users
   - ✅ Can approve timesheets
   - ✅ Can clock in crew

2. **Manager User**
   - ✅ Can create/edit estimates
   - ✅ Can view all jobs
   - ✅ Can approve timesheets
   - ✅ Can manage catalogs
   - ❌ Cannot access user management

3. **Foreman User**
   - ✅ Can view assigned jobs only
   - ✅ Can approve crew timesheets
   - ✅ Can clock in crew
   - ❌ Cannot create estimates
   - ❌ Cannot access admin panel

4. **Crew User**
   - ✅ Can create own timesheets
   - ✅ Can clock in/out
   - ❌ Cannot approve timesheets
   - ❌ Cannot view estimates
   - ❌ Cannot access admin panel

5. **Office User**
   - ✅ Can create/edit estimates
   - ✅ Can view jobs
   - ✅ Can view reports
   - ❌ Cannot approve timesheets
   - ❌ Cannot access admin panel

## Assigning Roles

Roles can be assigned in the user management interface (admin only):

```php
// In code
$user->role = User::ROLE_FOREMAN;
$user->save();

// Or when creating a user
User::create([
    'name' => 'John Foreman',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
    'role' => User::ROLE_FOREMAN,
]);
```

## Future Enhancements

1. **Custom Permissions** - Add ability to grant specific permissions to individual users
2. **Team Management** - Allow foremen to be assigned to multiple jobs
3. **Permission History** - Track changes to user roles and permissions
4. **API Tokens** - Role-based API access for mobile app
5. **Multi-Role Support** - Allow users to have multiple roles simultaneously
