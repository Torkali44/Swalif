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
        'phone',
        'phone_code',
        'birth_date',
        'avatar',
        'character_id',
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

    public function character()
    {
        return $this->belongsTo(Character::class);
    }

    public function displayAvatarUrl(): ?string
    {
        // Selected character always wins over an uploaded profile photo
        if ($this->character_id) {
            $character = $this->relationLoaded('character')
                ? $this->character
                : $this->character()->first();

            if ($character) {
                return $character->imageUrl();
            }
        }

        return $this->avatarUrl();
    }

    /**
     * In-memory cache for hasActiveSubscription() — keyed by spl_object_id to avoid
     * persisting a non-column property through Eloquent's attribute bag.
     *
     * @var array<int, bool>
     */
    private static array $subscriptionCache = [];

    /**
     * Clear the subscription cache when any User model is refreshed from DB.
     * This prevents stale cached values from leaking across requests and tests.
     */
    protected static function booted(): void
    {
        // Clear this instance's cache slot whenever it is re-fetched from DB.
        static::retrieved(function (User $user) {
            unset(self::$subscriptionCache[spl_object_id($user)]);
        });
    }

    /**
     * {@inheritdoc}
     *
     * Also resets the subscription cache so the next call re-queries the DB.
     */
    public function refresh(): static
    {
        $this->resetSubscriptionCache();

        return parent::refresh();
    }

    /**
     * Whether the user currently has an active subscription.
     * Result is cached in-memory to avoid multiple DB round-trips per request.
     */
    public function hasActiveSubscription(): bool
    {
        $key = spl_object_id($this);

        if (array_key_exists($key, self::$subscriptionCache)) {
            return self::$subscriptionCache[$key];
        }

        return self::$subscriptionCache[$key] = $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->exists();
    }

    /**
     * Reset the in-memory subscription cache (call after mutating subscriptions).
     */
    public function resetSubscriptionCache(): void
    {
        unset(self::$subscriptionCache[spl_object_id($this)]);
    }

    /**
     * Return the user's avatar emoji from their selected character, or null if they
     * have a custom avatar image or no character selected.
     */
    public function displayAvatarEmoji(): ?string
    {
        if (! $this->character_id) {
            return null;
        }

        $character = $this->relationLoaded('character')
            ? $this->character
            : $this->character()->first();

        return $character?->icon ?: null;
    }

    public function displayAvatarInitial(): string
    {
        return mb_substr($this->name, 0, 1);
    }

    public function displayAvatarGradient(): string
    {
        if ($this->character_id) {
            $character = $this->relationLoaded('character')
                ? $this->character
                : $this->character()->first();

            if ($character) {
                return $character->accentGradient();
            }
        }

        return 'linear-gradient(135deg,#1E3A5F,#0F2440)';
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

    public function customGames()
    {
        return $this->hasMany(CustomGame::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
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
