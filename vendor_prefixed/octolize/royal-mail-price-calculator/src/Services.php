<?php

namespace OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator;

use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\FirstClassService;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\GuaranteedByNineAmService;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\GuaranteedByNineAmWithSaturdayService;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\GuaranteedByOnePmService;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\GuaranteedByOnePmWithSaturdayService;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\InternationalEconomy;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\InternationalSigned;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\InternationalStandard;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\InternationalTracked;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\InternationalTrackedAndSigned;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\SecondClassService;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\Service;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\SignedForFirstClassService;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\SignedForSecondClassService;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\Tracked24;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\Tracked24WithSignature;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\Tracked48;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Services\Tracked48WithSignature;
/**
 * Can provide services.
 */
class Services
{
    /**
     * @return array<string, Service>
     */
    public function getDomesticServices()
    {
        return ['STL1' => new FirstClassService(), 'STL2' => new SecondClassService(), 'STL1S' => new SignedForFirstClassService(), 'STL2S' => new SignedForSecondClassService(), 'SD9' => new GuaranteedByNineAmService(), 'SD9S' => new GuaranteedByNineAmWithSaturdayService(), 'SD1' => new GuaranteedByOnePmService(), 'SD1S' => new GuaranteedByOnePmWithSaturdayService(), 'T24' => new Tracked24(), 'T24S' => new Tracked24WithSignature(), 'T48' => new Tracked48(), 'T48S' => new Tracked48WithSignature()];
    }
    /**
     * @return array<string, Service>
     */
    public function getInternationalServices()
    {
        return ['OLA' => new InternationalStandard(), 'OLS' => new InternationalEconomy(), 'OSA' => new InternationalSigned(), 'OTA' => new InternationalTracked(), 'OTC' => new InternationalTrackedAndSigned()];
    }
}
