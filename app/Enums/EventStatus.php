<?php

namespace App\Enums;

use App\Models\Event;

enum EventStatus: string
{
    case SCHEDULED = 'Scheduled';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';
    case POSTPONED = 'Postponed';
    case ARCHIVED = 'Archived';
    case DRAFT = 'Draft';
    case PENDING = 'Pending';


    public static function fromValue(string $value): EventStatus
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return $status;
            }
        }
        throw new \ValueError("$value is not a valid backing value for enum " . self::class);
    }

    public static function fromKey(string $key): EventStatus
    {
        foreach (self::cases() as $status) {
            if ($key === $status->name) {
                return $status;
            }
        }
        throw new \ValueError("$key is not a valid backing value for enum " . self::class);
    }

    public static function fromName(string $name): EventStatus
    {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status;
            }
        }
        throw new \ValueError("$name is not a valid backing value for enum " . self::class);
    }

    public static function toArray()
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return ['id' => $case->name, 'name' => $case->value];
        })->toArray();
    }

    public static function toCollection()
    {
        // Convert the enum cases to a collection
        $return = collect(self::cases())->map(function ($case) {
            return [
                'id' => $case->name,
                'name' => $case->value,
            ];
        });

        $return->prepend(['id' => '', 'name' => 'Select Status'], -1);

        return $return;
    }

    public static function creatingStatus()
    {
        // Convert the enum cases to a collection

        $filtered = array_filter(self::cases(), function ($case) {
            return in_array($case, [self::DRAFT, self::PENDING, self::SCHEDULED]);
        });
        
        $return = collect($filtered)->map(function ($case) {
            
                return [
                    'id' => $case->name,
                    'name' => $case->value,
                ];
            
        });

        $return->prepend(['id' => '', 'name' => 'Select Status'], -1);

        return $return;
    }
}
