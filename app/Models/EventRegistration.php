<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventRegistration extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\RegistrationFactory> */
    use HasFactory;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'name',
        'email',
        'phone',
        'is_attending',
        'registered_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'is_attending' => 'boolean',
        'event_id' => 'integer',
        'email' => 'string',
        'phone' => 'string',
        'notes' => 'string',
        'name' => 'string',
        //'event' => Event::class,
        'status' => RegistrationStatus::class,
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAttending($query)
    {
        return $query->where('is_attending', true);
    }

    public function getEventTitleValue()
    {
        return $this->event->title;
    }

    public function getEventOrganizerValue()
    {
        return $this->event->organizer->name;
    }
}
