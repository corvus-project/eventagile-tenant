<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use jeremykenedy\LaravelRoles\Traits\HasRoleAndPermission;
use App\Services\SubscriptionService;
use App\Services\VerifyEmailQueued;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable  implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoleAndPermission, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

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
            'created_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'user_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }
        return false;
    }

    public function sendEmailVerificationNotification()
    {
        Log::info('Sending email verification notification', ['user_id' => $this->id]);
        $this->notify(new VerifyEmailQueued);
    }

    public function configure()
    {
        return $this->afterMaking(function (User $user) {
            return $user->assignRole('User');
        });
    }

    /**
     * @return UserFactory
     */
    private function assignRole(string $role): UserFactory
    {
        $userRole = config('roles.models.role')::where('name', '=', $role)->first();
        return $this->afterCreating(fn(User $user) => $user->attachRole($userRole));
    }
}
