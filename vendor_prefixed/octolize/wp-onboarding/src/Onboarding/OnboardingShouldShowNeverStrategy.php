<?php

/**
 * @package Octolize\Onboarding
 */
namespace OctolizeShippingRoyalMailVendor\Octolize\Onboarding;

/**
 * Never display strategy.
 */
class OnboardingShouldShowNeverStrategy implements OnboardingShouldShowStrategy
{
    public function should_display(): bool
    {
        return \false;
    }
}
