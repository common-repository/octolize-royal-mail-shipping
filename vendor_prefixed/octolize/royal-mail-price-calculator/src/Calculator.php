<?php

namespace OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator;

use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Exceptions\UnknownPackageTypeException;
/**
 * Class Calculator
 * @package RoyalMailPriceCalculator
 */
class Calculator
{
    private $now;
    private $iso_code = 'uk';
    public function __construct()
    {
        $this->now = new \DateTime();
    }
    /**
     * @var \RoyalMailPriceCalculator\Services\Service[]
     */
    private $services;
    /**
     * @return \RoyalMailPriceCalculator\Services\Service[]
     */
    public function getServices()
    {
        return $this->services;
    }
    /**
     * @param \RoyalMailPriceCalculator\Services\Service[] | \RoyalMailPriceCalculator\Services\Service $services
     */
    public function setServices($services)
    {
        if (is_array($services)) {
            $this->services = $services;
        } else {
            $this->services = array($services);
        }
    }
    public function setCountryCode($iso2code)
    {
        $this->iso_code = $iso2code;
    }
    /**
     * @param \RoyalMailPriceCalculator\Package $package
     * @return array
     * @throws \Exception
     */
    public function calculatePrice(Package $package)
    {
        $services = $this->getServices();
        $calculatedPrices = array();
        foreach ($services as $service) {
            $service->setZone($this->iso_code);
            try {
                $priceData = $service->getPriceData();
            } catch (\Exception $e) {
                $priceData = [];
            }
            $prices = array();
            try {
                $packageType = $service->getPackageType($package);
                foreach ($priceData as $data) {
                    if ($packageType === \false) {
                        $packageTypePrices = $data['prices'];
                    } else {
                        $packageTypePrices = $data['prices'][$packageType] ?? [];
                    }
                    ksort($packageTypePrices);
                    $packagePrice = 0;
                    foreach ($packageTypePrices as $weight => $price) {
                        if ($weight >= $package->getWeight()) {
                            $packagePrice = $price;
                            break;
                        }
                    }
                    if (!$packagePrice) {
                        continue;
                    }
                    $prices[] = array('price' => number_format($packagePrice, 2, '.', ''), 'compensation' => $data['compensation'], 'tax' => (float) ($data['tax'] ?? 0.0));
                }
            } catch (UnknownPackageTypeException $e) {
            }
            if (count($prices)) {
                $calculatedPrices[] = array('service' => $service, 'days' => $priceData[0]['days'] ?? null, 'prices' => $prices);
            }
        }
        return $calculatedPrices;
    }
    public static function get_region_code($iso2code)
    {
        switch (strtolower($iso2code)) {
            case 'uk':
            case 'gb':
                return 'uk';
            case 'ie':
            case 'ge':
            case 'dk':
            case 'fr':
            case 'mc':
                return 'eu_1';
            case 'at':
            case 'az':
            case 'es':
            case 'be':
            case 'bg':
            case 'hr':
            case 'cy':
            case 'cz':
            case 'ee':
            case 'fi':
            case 'gr':
            case 'hu':
            case 'it':
            case 'lv':
            case 'lt':
            case 'lu':
            case 'mt':
            case 'nl':
            case 'pl':
            case 'pt':
            case 'ro':
            case 'sk':
            case 'si':
            case 'se':
                return 'eu_2';
            case 'al':
            case 'ad':
            case 'am':
            case 'by':
            case 'ba':
            case 'fo':
            case 'de':
            case 'gi':
            case 'gl':
            case 'is':
            case 'kz':
            case 'xk':
            case 'kg':
            case 'li':
            case 'mk':
            case 'md':
            case 'me':
            case 'no':
            case 'ru':
            case 'sm':
            case 'rs':
            case 'ch':
            case 'tj':
            case 'tr':
            case 'tm':
            case 'ua':
            case 'uz':
            case 'va':
                return 'eu_3';
            case 'au':
            case 'cx':
            case 'fj':
            case 'ki':
            case 'nz':
            case 'aq':
            case 'sg':
            case 'to':
            case 'cc':
            case 'pf':
            case 'mo':
            case 'pg':
            case 'sb':
            case 'tv':
            case 'io':
            case 'ck':
            case 'nr':
            case 'nu':
            case 'la':
            case 'as':
            case 'nc':
            case 'nf':
            case 'pn':
            case 'tk':
            case 'ws':
                return 'intl_2';
            case 'us':
                return 'intl_3';
            default:
                return 'intl_1';
        }
    }
}
