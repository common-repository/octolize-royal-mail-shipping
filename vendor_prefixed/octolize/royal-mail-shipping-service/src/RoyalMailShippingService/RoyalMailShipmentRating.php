<?php

namespace OctolizeShippingRoyalMailVendor\Octolize\RoyalMailShippingService;

use OctolizeShippingRoyalMailVendor\Psr\Log\LoggerInterface;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Calculator;
use OctolizeShippingRoyalMailVendor\RoyalMailPriceCalculator\Package;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Exception\UnitConversionException;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Rate\Money;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Rate\SingleRate;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Settings\SettingsValues;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Shipment\Dimensions;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Shipment\Shipment;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Shipment\Weight;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\UnitConversion\UniversalDimension;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\UnitConversion\UniversalWeight;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShopSettings;
/**
 * Can calculate rates for given shipment.
 */
class RoyalMailShipmentRating
{
    const ENABLED = 'enabled';
    const SERVICE = 'service';
    const PRICES = 'prices';
    const PRICE = 'price';
    const TAX = 'tax';
    const NO = 'no';
    const GBP = 'GBP';
    const COMPENSATION = 'compensation';
    const NAME = 'name';
    /**
     * @var LoggerInterface
     */
    private $logger;
    /** Shipping method helper.
     *
     * @var ShopSettings
     */
    private $shop_settings;
    /**
     * @var array
     */
    private $services_settings;
    /**
     * @var bool
     */
    private $is_custom_services_enabled;
    /**
     * @param LoggerInterface $logger
     * @param ShopSettings $shop_settings
     * @param array $services_settings
     * @param bool $is_custom_services_enabled
     */
    public function __construct(LoggerInterface $logger, ShopSettings $shop_settings, array $services_settings, bool $is_custom_services_enabled)
    {
        $this->logger = $logger;
        $this->shop_settings = $shop_settings;
        $this->services_settings = $services_settings;
        $this->is_custom_services_enabled = $is_custom_services_enabled;
    }
    /**
     * Rate shipment.
     *
     * @param SettingsValues $settings Settings.
     * @param Shipment $shipment Shipment.
     *
     * @return SingleRate[]
     * @throws \Exception
     */
    public function rate_shipment(SettingsValues $settings, Shipment $shipment): array
    {
        $rates = [];
        $first_package = \true;
        foreach ($shipment->packages as $package) {
            $single_package_shipment = new Shipment();
            $single_package_shipment->packages = [$package];
            $single_package_shipment->packed = $shipment->packed;
            $single_package_shipment->ship_from = $shipment->ship_from;
            $single_package_shipment->ship_to = $shipment->ship_to;
            $single_package_shipment->insurance = $shipment->insurance;
            $single_package_rates = $this->rate_single_package($single_package_shipment, $settings);
            if (!$first_package) {
                $rates = $this->merge_rates($rates, $single_package_rates);
            } else {
                $rates = $single_package_rates;
                $first_package = \false;
            }
        }
        return $rates;
    }
    /**
     * @param SingleRate[] $rates
     * @param SingleRate[] $single_rates
     *
     * @return SingleRate[]
     */
    private function merge_rates(array $rates, array $single_rates): array
    {
        foreach ($rates as $key => $rate) {
            $single_rate = $this->find_rate_by_service($rate->service_name, $rate->service_type, $single_rates);
            if ($single_rate) {
                $rate->total_charge->amount += $single_rate->total_charge->amount;
            } else {
                unset($rates[$key]);
            }
        }
        return $rates;
    }
    /**
     * @param string $service_name
     * @param string $service_type
     * @param SingleRate[] $rates
     *
     * @return SingleRate|null
     */
    private function find_rate_by_service(string $service_name, string $service_type, array $rates): ?SingleRate
    {
        foreach ($rates as $rate) {
            if ($rate->service_name === $service_name && $rate->service_type === $service_type) {
                return $rate;
            }
        }
        return null;
    }
    /**
     * @param Shipment $shipment
     * @param SettingsValues $settings
     * @return Package
     * @throws UnitConversionException
     */
    private function prepare_package(Shipment $shipment, SettingsValues $settings): Package
    {
        $package = new Package();
        $shipment_package = $shipment->packages[0];
        if ($shipment_package->dimensions) {
            $width = new UniversalDimension($shipment_package->dimensions->width, $shipment_package->dimensions->dimensions_unit);
            $height = new UniversalDimension($shipment_package->dimensions->height, $shipment_package->dimensions->dimensions_unit);
            $length = new UniversalDimension($shipment_package->dimensions->length, $shipment_package->dimensions->dimensions_unit);
            $package->setDimensions($length->as_unit_rounded(Dimensions::DIMENSION_UNIT_CM), $width->as_unit_rounded(Dimensions::DIMENSION_UNIT_CM), $height->as_unit_rounded(Dimensions::DIMENSION_UNIT_CM));
        } else {
            $package->setDimensions((float) $settings->get_value(RoyalMailSettingsDefinition::PACKAGE_LENGTH, 0), (float) $settings->get_value(RoyalMailSettingsDefinition::PACKAGE_WIDTH, 0), (float) $settings->get_value(RoyalMailSettingsDefinition::PACKAGE_HEIGHT, 0));
        }
        $weight = $shipment_package->weight->weight === 0.0 ? (new UniversalWeight((float) $settings->get_value(RoyalMailSettingsDefinition::PACKAGE_WEIGHT, 0), Weight::WEIGHT_UNIT_KG))->as_unit_rounded(Weight::WEIGHT_UNIT_G) : (new UniversalWeight($shipment_package->weight->weight, $shipment_package->weight->weight_unit))->as_unit_rounded(Weight::WEIGHT_UNIT_G);
        $package->setWeight($weight);
        $package_debug_data = sprintf(__('Width: %1$d cm, length: %2$d cm, depth: %3$d cm, weight: %4$d g, value: %5$d %6$s.', 'octolize-royal-mail-shipping'), $package->getWidth(), $package->getLength(), $package->getDepth(), $package->getWeight(), $this->calculate_package_value($shipment->packages[0]), $this->shop_settings->get_currency());
        $this->logger->debug('Royal Mail Package', ['package' => $package_debug_data, 'timestamp' => date('c') . ' ' . rand(0, 1000)]);
        return $package;
    }
    /**
     * @param Package $package
     * @param Shipment $shipment
     * @param SettingsValues $settings
     * @param RoyalMailServices $royal_mail_services
     * @return array
     * @throws \Exception
     */
    private function calculate_rates(Package $package, Shipment $shipment, SettingsValues $settings, RoyalMailServices $royal_mail_services): array
    {
        $calculator = new Calculator();
        if ($this->is_custom_services_enabled) {
            $enabled_services = $this->get_enabled_services($this->services_settings);
        } else {
            $enabled_services = $royal_mail_services->get_all_services();
        }
        $calculator->setServices($royal_mail_services->get_royal_mail_services($enabled_services));
        $calculator->setCountryCode($shipment->ship_to->address->country_code);
        return $calculator->calculatePrice($package);
    }
    /**
     * @param \WPDesk\AbstractShipping\Shipment\Package $package
     * @return float
     */
    private function calculate_package_value(\OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Shipment\Package $package): float
    {
        $package_value = 0.0;
        foreach ($package->items as $item) {
            $package_value += $item->declared_value->amount;
        }
        return $package_value;
    }
    /**
     * @param array $services_settings
     * @return array
     */
    private function get_enabled_services(array $services_settings): array
    {
        $enabled_services = [];
        foreach ($services_settings as $single_service) {
            if (isset($single_service[self::ENABLED])) {
                $enabled_services[$single_service[self::ENABLED]] = $single_service[self::NAME];
            }
        }
        return $enabled_services;
    }
    /**
     * @param Shipment $shipment
     * @param SettingsValues $settings
     *
     * @return array
     * @throws UnitConversionException
     */
    private function rate_single_package(Shipment $shipment, SettingsValues $settings): array
    {
        $rates = [];
        $package = $this->prepare_package($shipment, $settings);
        $royal_mail_services = new RoyalMailServices();
        $calculated_rates = $this->calculate_rates($package, $shipment, $settings, $royal_mail_services);
        $rates_debug = [];
        foreach ($calculated_rates as $calculated_rate) {
            foreach ($calculated_rate[self::PRICES] as $single_rate_price) {
                $rate = new SingleRate();
                $rate->service_name = $calculated_rate[self::SERVICE]->getName();
                $rate->service_type = $royal_mail_services->get_service_code_for_service($calculated_rate[self::SERVICE]);
                $rate->total_charge = new Money();
                $rate->total_charge->amount = $single_rate_price[self::PRICE];
                if ('yes' === $settings->get_value(RoyalMailSettingsDefinition::REMOVE_TAX, self::NO)) {
                    $tax = (float) ($single_rate_price[self::TAX] ?? 0.0);
                    $rate->total_charge->amount = (float) $rate->total_charge->amount / (1 + $tax / 100);
                }
                $rate->total_charge->currency = self::GBP;
                $add_rate_considering_compensation = self::NO === $settings->get_value(RoyalMailSettingsDefinition::INSURANCE, self::NO) || $this->calculate_package_value($shipment->packages[0]) <= (float) $single_rate_price[self::COMPENSATION];
                $rates_debug[$calculated_rate[self::SERVICE]->getName()] = sprintf(__('Service: %1$s,
price: %2$s %3$s,
remove tax: %4$s,
price after tax: %5$s %3$s
compensation: %6$d,
add rate considering compensation: %7$s', 'octolize-royal-mail-shipping'), $rate->service_name, $single_rate_price[self::PRICE], $rate->total_charge->currency, $settings->get_value(RoyalMailSettingsDefinition::REMOVE_TAX, self::NO), $rate->total_charge->amount, (float) $single_rate_price[self::COMPENSATION], $add_rate_considering_compensation ? 'yes' : self::NO);
                if ($add_rate_considering_compensation) {
                    $rates[] = $rate;
                    break;
                }
            }
        }
        $rates_debug['timestamp'] = date('c') . ' ' . rand(0, 1000);
        if (count($rates_debug)) {
            $this->logger->debug('Royal Mail Calculated Rates', $rates_debug);
        } else {
            $rates_debug[__('Rates', 'octolize-royal-mail-shipping')] = __('There are no calculated rates for this package.', 'octolize-royal-mail-shipping');
            $this->logger->debug('Royal Mail Calculated Rates', $rates_debug);
        }
        return $rates;
    }
}
