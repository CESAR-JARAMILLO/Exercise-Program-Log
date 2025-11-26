<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionTier;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionTier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $requirement  The tier name or feature name to check
     */
    public function handle(Request $request, Closure $next, string $requirement): Response
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            abort(401, 'Unauthenticated.');
        }

        $user = auth()->user();

        // Check if requirement is a tier name
        $validTiers = SubscriptionTier::values();
        if (in_array($requirement, $validTiers)) {
            // It's a tier check
            if ($user->subscription_tier !== $requirement) {
                // For tier checks, we might want to allow higher tiers
                // For example, if checking 'trainer', allow 'pro_trainer' too
                if ($requirement === SubscriptionTier::TRAINER->value && $user->isProTrainer()) {
                    return $next($request);
                }
                
                abort(403, 'This feature requires a ' . config("subscription.tiers.{$requirement}.name", $requirement) . ' subscription.');
            }
        } else {
            // It's a feature check
            if (!$user->hasFeature($requirement)) {
                // Get tiers that have this feature
                $allowedTiers = config("subscription.features.{$requirement}", []);
                $tierNames = array_map(function($tier) {
                    return config("subscription.tiers.{$tier}.name", $tier);
                }, $allowedTiers);
                
                $tierList = implode(' or ', $tierNames);
                abort(403, "This feature requires a {$tierList} subscription.");
            }
        }

        return $next($request);
    }
}
