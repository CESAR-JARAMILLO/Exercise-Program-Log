<?php

namespace App\Actions\Fortify;

use App\Enums\SubscriptionTier;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255', 'min:2'],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ], [
            'name.required' => 'Please enter your full name.',
            'name.min' => 'Your name must be at least 2 characters.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered. Please log in instead.',
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'The passwords do not match.',
        ])->validate();

        return User::create([
            'name' => trim($input['name']),
            'email' => strtolower(trim($input['email'])),
            'password' => $input['password'],
            'subscription_tier' => SubscriptionTier::FREE->value,
        ]);
    }
}
