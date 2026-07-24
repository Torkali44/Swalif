<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Support\PublicMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
        'is_admin',
        'is_active',
        'play_blocked',
        'play_blocked_at',
        'play_blocked_reason',
        'phone',
        'phone_code',
        'birth_date',
        'avatar',
        'free_category_id',
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
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'play_blocked' => 'boolean',
            'play_blocked_at' => 'datetime',
            'birth_date' => 'date',
        ];
    }

    public function avatarUrl(): ?string
    {
        return PublicMedia::url($this->avatar);
    }

    public function firstName(): string
    {
        $parts = preg_split('/\s+/u', trim($this->name), 2);

        return $parts[0] ?? '';
    }

    public function lastName(): string
    {
        $parts = preg_split('/\s+/u', trim($this->name), 2);

        return $parts[1] ?? '';
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->exists();
    }

    public function activeSubscription()
    {
        return $this->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->latest('ends_at')
            ->first();
    }

    public function freeCategory()
    {
        return $this->belongsTo(Category::class, 'free_category_id');
    }

    public function formattedPhone(): string
    {
        $code = trim((string) $this->phone_code);
        $phone = trim((string) $this->phone);

        return trim($code.' '.$phone) ?: '—';
    }
}
