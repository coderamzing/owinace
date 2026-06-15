<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use App\Traits\TeamTraits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UpworkProfile extends Model
{
    use HasFactory, TeamTraits;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'title',
        'email',
        'code',
        'is_active',
        'proxy_host',
        'proxy_port',
        'proxy_username',
        'proxy_password',
        'proxy_protocol',
        'proxy_validated_at',
        'proxy_last_ip',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'proxy_password' => 'encrypted',
            'proxy_validated_at' => 'datetime',
        ];
    }

    public function hasProxy(): bool
    {
        return filled($this->proxy_host) && filled($this->proxy_port);
    }

    /**
     * @return array{host: string, port: int, username: ?string, password: ?string, protocol: string, last_ip: ?string}|null
     */
    public function proxyConfigForBot(): ?array
    {
        if (! $this->hasProxy()) {
            return null;
        }

        return [
            'host' => $this->proxy_host,
            'port' => (int) $this->proxy_port,
            'username' => $this->proxy_username,
            'password' => $this->proxy_password,
            'protocol' => $this->proxy_protocol ?: 'http',
            'last_ip' => $this->proxy_last_ip,
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TeamScope);

        static::creating(function (UpworkProfile $profile): void {
            if (empty($profile->code)) {
                $profile->code = self::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode(): string
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while (self::withoutGlobalScopes()->where('code', $code)->exists());

        return $code;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(UpworkCampaign::class, 'profile_id');
    }
}
