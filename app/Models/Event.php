<?php

namespace App\Models;

use App\Enums\EventStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory, HasSlug, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'location',
        'organizer',
        'capacity',
        'is_public',
        'status',
        'organizer_id',
        'slug',
        'registration_deadline',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            $event->registration_code = strtoupper(bin2hex(random_bytes(4)));
        });
    }

    protected $casts = [
        'start_time' => 'datetime:Y-m-d H:i',
        'registration_deadline' => 'datetime:Y-m-d H:i',
        'is_public' => 'boolean',
        'status' => EventStatus::class,
        'capacity' => 'integer',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(50);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    protected function status_value(): Attribute
    {
        return Attribute::make(
            get: fn($value) => EventStatus::tryFrom($value)
        );
    }

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->format('Y-m-d H:i');
    }

    public function getPublicStatusAttribute()
    {
        return $this->is_public ? 'Public' : 'Private';
    }

    public function getStartTimeFormattedAttribute()
    {
        return $this->start_time->format('d M Y H:i');
    }
}
