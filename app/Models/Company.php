<?php

namespace App\Models;

use App\Concerns\GeneratesUniqueCompanySlugs;
use App\Enums\CompanyRole;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'slug', 'is_personal', 'allowed_domains'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use GeneratesUniqueCompanySlugs, HasFactory, SoftDeletes;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Company $company) {
            if (empty($company->slug)) {
                $company->slug = static::generateUniqueCompanySlug($company->name);
            }
        });

        static::updating(function (Company $company) {
            if ($company->isDirty('name')) {
                $company->slug = static::generateUniqueCompanySlug($company->name, $company->id);
            }
        });
    }

    /**
     * Get the company owner.
     */
    public function owner(): ?Model
    {
        return $this->members()
            ->wherePivot('role', CompanyRole::Owner->value)
            ->first();
    }

    /**
     * Get all members of this company.
     *
     * @return BelongsToMany<Model, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_members', 'company_id', 'user_id')
            ->using(Membership::class)
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    /**
     * Get all memberships for this company.
     *
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get all invitations for this company.
     *
     * @return HasMany<CompanyInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(CompanyInvitation::class);
    }

    /**
     * Get the positions for this company.
     *
     * @return HasMany<Position, $this>
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Get the clients for this company.
     *
     * @return HasMany<Client, $this>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get the hourly rates for users in this company.
     */
    public function hourlyRates(): HasMany
    {
        return $this->hasMany(HourlyRate::class);
    }

    /**
     * Get the issues for this company.
     */
    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    /**
     * Get the projects for this company.
     */
    public function projects(): HasManyThrough
    {
        return $this->hasManyThrough(Project::class, Client::class, 'company_id', 'client_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_personal' => 'boolean',
            'allowed_domains' => 'array',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
