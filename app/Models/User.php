<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'google_id',
        'avatar',
        'role',
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
            'password' => 'hashed',
        ];
    }

    public function createdRequests()
    {
        return $this->hasMany(RequirementRequest::class, 'created_by');
    }

    public function approvedRequests()
    {
        return $this->hasMany(RequirementRequest::class, 'approved_by');
    }

    public function assignedRequests()
    {
        return $this->hasMany(RequirementRequest::class, 'assigned_to');
    }

    public function isFmoUser(): bool
    {
        return $this->role === 'fmo_user';
    }

    public function isFmoAdmin(): bool
    {
        return $this->role === 'fmo_admin';
    }

    public function isPoAdmin(): bool
    {
        return $this->role === 'po_admin';
    }

    public function isPoUser(): bool
    {
        return $this->role === 'po_user';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
}
