<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case PENDING = 'Pending';
    case CONFIRMED = 'Confirmed';
    case CANCELLED = 'Cancelled';
    case WAITLISTED = 'Waitlisted'; 

    public static function fromValue(string $value): RegistrationStatus
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return $status;
            }
        }
        throw new \ValueError("$value is not a valid backing value for enum " . self::class);
    }

    public static function toArray()
    {
        return collect(self::cases())->mapWithKeys(function ($case) {
            return ['id' => $case->name, 'name' => $case->value];
        })->toArray();
    }

    public static function fromName(string $name): RegistrationStatus
    {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status;
            }
        }
        throw new \ValueError("$name is not a valid backing value for enum " . self::class);
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

        $return->prepend(['id' => '', 'name' => 'Select Status'], 0);
        return $return;
    }
}
