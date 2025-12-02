<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $role
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Available user roles
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_FOREMAN = 'foreman';
    public const ROLE_CREW = 'crew';
    public const ROLE_OFFICE = 'office';
    public const ROLE_USER = 'user';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the jobs assigned to this user as foreman.
     */
    public function jobs()
    {
        return $this->hasMany(Job::class, 'foreman_id');
    }

    /**
     * Get the timesheets for this user.
     */
    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    // Role Checking Methods
    
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isForeman(): bool
    {
        return $this->role === self::ROLE_FOREMAN;
    }

    public function isCrew(): bool
    {
        return $this->role === self::ROLE_CREW;
    }

    public function isOffice(): bool
    {
        return $this->role === self::ROLE_OFFICE;
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }

        return in_array($this->role, $roles);
    }

    // Permission Checking Methods

    public function canManageEstimates(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_OFFICE]);
    }

    public function canManageJobs(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    public function canViewJobs(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_FOREMAN, self::ROLE_OFFICE]);
    }

    public function canManageTimesheets(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_FOREMAN]);
    }

    public function canApproveTimesheets(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_FOREMAN]);
    }

    public function canCreateTimesheets(): bool
    {
        // Everyone can create their own timesheets
        return true;
    }

    public function canViewReports(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_OFFICE]);
    }

    public function canManageUsers(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }

    public function canManageClients(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_OFFICE]);
    }

    public function canManageBudgets(): bool
    {
        return $this->isAdmin();
    }

    public function canSyncQuickBooks(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_OFFICE]);
    }

    public function canClockInCrew(): bool
    {
        return $this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_FOREMAN]);
    }

    /**
     * Get jobs this user can view
     */
    public function scopeViewableJobs($query)
    {
        if ($this->hasRole([self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_OFFICE])) {
            return Job::query();
        }

        if ($this->isForeman()) {
            return Job::where('foreman_id', $this->id);
        }

        // Crew can only see jobs they have timesheets for
        return Job::whereHas('timesheets', function ($q) {
            $q->where('user_id', $this->id);
        });
    }
}
