<?php

/**
 * @package Octolize\Onboarding
 */
namespace OctolizeShippingRoyalMailVendor\Octolize\Onboarding;

/**
 * When to show Onboarding.
 */
interface OnboardingShouldShowStrategy
{
    /**
     * Should Onboarding be visible?
     *
     * @return bool
     */
    public function should_display();
}
