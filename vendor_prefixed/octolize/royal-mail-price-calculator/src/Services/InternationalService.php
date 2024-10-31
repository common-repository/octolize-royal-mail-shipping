<?php

namespace OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services;

use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Exceptions\UnknownPackageTypeException;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Package;
abstract class InternationalService extends Service
{
    const LETTER = 'letter';
    const LARGE_LETTER = 'large_letter';
    const SMALL_PARCEL = 'small_parcel';
    const MEDIUM_PARCEL = 'medium_parcel';
    const TUBE = 'tube';
    /**
     * @param Package $package
     * @returns String
     * @throws UnknownPackageTypeException
     */
    public function getPackageType(Package $package)
    {
        $length = $package->getLength();
        $width = $package->getWidth();
        $depth = $package->getDepth();
        $weight = $package->getWeight();
        if ($package->isTube() && $length <= 90 && $length + $depth + $width < 104 && $weight <= 2000) {
            return self::TUBE;
        } elseif ($length <= 24 && $width <= 16.5 && $depth <= 0.5 && $weight <= 100) {
            return self::LETTER;
        } elseif ($length <= 35.3 && $width <= 25 && $depth <= 2.5 && $weight <= 750) {
            return self::LARGE_LETTER;
        } elseif ($length <= 45 && $width <= 35 && $depth <= 16 && $weight <= 2000) {
            return self::SMALL_PARCEL;
        } elseif ($length <= 61 && $width <= 46 && $depth <= 46 && $weight <= 20000) {
            return self::MEDIUM_PARCEL;
        } elseif ($length + $depth + $width <= 90 && $length <= 60 && $width <= 60 && $depth <= 60 && $weight <= 2000) {
            return self::SMALL_PARCEL;
        } else {
            throw new UnknownPackageTypeException();
        }
    }
}
