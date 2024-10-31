<?php

namespace OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services;

use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Package;
abstract class GuaranteedService extends Service
{
    public function getPackageType(Package $package)
    {
        return \false;
    }
}
