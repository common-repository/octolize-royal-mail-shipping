<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Beacon;

/**
 * When to show Beacon.
 */
interface BeaconShouldShowStrategy
{
    /**
     * Should Beacon be visible?
     *
     * @return bool
     */
    public function shouldDisplay();
}
