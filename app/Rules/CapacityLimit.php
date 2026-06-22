<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class CapacityLimit implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = auth()->user();
        /*         if ($user) {
            $maxCapacity = app('App\Services\SubscriptionService')->getRegistrationLimit($user);
            Log::info('User ID: ' . $user->id . ' has a max registration limit of: ' . $maxCapacity);
            if ($maxCapacity > 0 && $value > $maxCapacity) {
                $fail("The {$attribute} exceeds your subscription limit of {$maxCapacity}.");
            }
        } else {
            $fail("Unable to validate {$attribute} as user is not authenticated.");
        } */
    }
}
