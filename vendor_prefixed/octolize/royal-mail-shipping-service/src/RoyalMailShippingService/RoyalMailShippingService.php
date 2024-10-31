<?php

namespace OctolizeShippingRoyalMailVendor\Octolize\RoyalMailShippingService;

use OctolizeShippingRoyalMailVendor\Psr\Log\LoggerInterface;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Exception\InvalidSettingsException;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Exception\RateException;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Exception\UnitConversionException;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Rate\ShipmentRating;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Rate\ShipmentRatingImplementation;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Rate\SingleRate;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Settings\SettingsValues;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Shipment\Shipment;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\ShippingService;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\ShippingServiceCapability\CanRate;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\ShippingServiceCapability\HasSettings;
use OctolizeShippingRoyalMailVendor\Octolize\RoyalMailShippingService\Exception\CurrencySwitcherException;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShopSettings;
/**
 * Royal Mail main shipping class injected into WooCommerce shipping method.
 */
class RoyalMailShippingService extends ShippingService implements HasSettings, CanRate
{
    const DOMESTIC = 'domestic';
    const INTERNATIONAL = 'international';
    const ENABLED = 'enabled';
    /** Logger.
     *
     * @var LoggerInterface
     */
    private $logger;
    /** Shipping method helper.
     *
     * @var ShopSettings
     */
    private $shop_settings;
    /**
     * Origin country.
     *
     * @var string
     */
    private $origin_country;
    const UNIQUE_ID = 'octolize_royal_mail_shipping';
    /**
     * RoyalMailShippingService constructor.
     *
     * @param LoggerInterface $logger Logger.
     * @param ShopSettings $shop_settings Helper.
     * @param string $origin_country Origin country.
     */
    public function __construct(LoggerInterface $logger, ShopSettings $shop_settings, string $origin_country)
    {
        $this->logger = $logger;
        $this->shop_settings = $shop_settings;
        $this->origin_country = $origin_country;
    }
    /**
     * Set logger.
     *
     * @param LoggerInterface $logger Logger.
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * .
     *
     * @return LoggerInterface
     */
    public function get_logger(): LoggerInterface
    {
        return $this->logger;
    }
    /**
     * .
     *
     * @return ShopSettings
     */
    public function get_shop_settings(): ShopSettings
    {
        return $this->shop_settings;
    }
    /**
     * Is standard rate enabled?
     *
     * @param SettingsValues $settings .
     *
     * @return bool
     */
    public function is_rate_enabled(SettingsValues $settings): bool
    {
        return \true;
    }
    /**
     * Rate shipment.
     *
     * @param SettingsValues $settings Settings.
     * @param Shipment $shipment Shipment.
     *
     * @return ShipmentRating
     * @throws InvalidSettingsException InvalidSettingsException.
     * @throws RateException RateException.
     * @throws UnitConversionException Weight exception.
     */
    public function rate_shipment(SettingsValues $settings, Shipment $shipment): ShipmentRating
    {
        if (!$this->get_settings_definition()->validate_settings($settings)) {
            throw new InvalidSettingsException();
        }
        $this->verify_currency($this->shop_settings->get_default_currency(), $this->shop_settings->get_currency());
        $royal_mail_shipment_rating = new RoyalMailShipmentRating($this->logger, $this->shop_settings, $this->get_services_settings($settings), $this->is_custom_services_enabled($settings));
        $rates = $this->filter_service_rates($settings, $royal_mail_shipment_rating->rate_shipment($settings, $shipment));
        return new ShipmentRatingImplementation($rates);
    }
    /**
     * Verify currency.
     *
     * @param string $default_shop_currency .
     * @param string $currency .
     *
     * @throws CurrencySwitcherException .
     */
    protected function verify_currency(string $default_shop_currency, string $currency)
    {
        if ('GBP' !== $currency) {
            throw new CurrencySwitcherException();
        }
    }
    /**
     * Get settings
     *
     * @return RoyalMailSettingsDefinition
     */
    public function get_settings_definition(): RoyalMailSettingsDefinition
    {
        return new RoyalMailSettingsDefinition($this->shop_settings);
    }
    /**
     * Get unique ID.
     *
     * @return string
     */
    public function get_unique_id(): string
    {
        return self::UNIQUE_ID;
    }
    /**
     * Get name.
     *
     * @return string
     */
    public function get_name(): string
    {
        return __('Royal Mail Live Rates', 'octolize-royal-mail-shipping');
    }
    /**
     * Get description.
     *
     * @return string
     */
    public function get_description(): string
    {
        return __('Royal Mail integration', 'octolize-royal-mail-shipping');
    }
    /**
     * Filter&change rates according to settings.
     *
     * @param SettingsValues $settings Settings.
     * @param SingleRate[] $royal_mail_rates Response.
     *
     * @return SingleRate[]
     */
    private function filter_service_rates(SettingsValues $settings, array $royal_mail_rates): array
    {
        $rates = [];
        if (!empty($royal_mail_rates)) {
            $all_services = $this->get_services();
            $services_settings = $this->get_services_settings($settings);
            if ($this->is_custom_services_enabled($settings)) {
                foreach ($royal_mail_rates as $service) {
                    if (isset($service->service_type, $services_settings[$service->service_type]) && !empty($services_settings[$service->service_type][self::ENABLED])) {
                        $service->service_name = $services_settings[$service->service_type]['name'];
                        $rates[$service->service_type] = $service;
                    }
                }
                $rates = $this->sort_services($rates, $services_settings);
            } else {
                foreach ($royal_mail_rates as $service) {
                    if (isset($service->service_type, $all_services[$service->service_type])) {
                        $service->service_name = $all_services[$service->service_type];
                        $rates[$service->service_type] = $service;
                    }
                }
            }
        }
        return $rates;
    }
    /**
     * @param string $type
     *
     * @return array
     */
    private function get_services($type = 'all'): array
    {
        $royal_mail_services = new RoyalMailServices();
        if ($type === self::DOMESTIC) {
            return $royal_mail_services->get_services_domestic_uk();
        }
        if ($type === self::INTERNATIONAL) {
            return $royal_mail_services->get_services_international();
        }
        return $royal_mail_services->get_all_services();
    }
    /**
     * @param SettingsValues $settings Settings.
     * @param bool $is_domestic Domestic rates.
     *
     * @return array
     */
    private function get_services_settings(SettingsValues $settings): array
    {
        $services_settings = $settings->get_value(RoyalMailSettingsDefinition::SERVICES, []);
        return is_array($services_settings) ? $services_settings : [];
    }
    /**
     * Sort rates according to order set in admin settings.
     *
     * @param SingleRate[] $rates Rates.
     * @param array $option_services Saved services to settings.
     *
     * @return SingleRate[]
     */
    private function sort_services(array $rates, array $option_services): array
    {
        if (!empty($option_services)) {
            $services = [];
            foreach ($option_services as $service_code => $service_name) {
                if (isset($rates[$service_code])) {
                    $services[] = $rates[$service_code];
                }
            }
            return $services;
        }
        return $rates;
    }
    /**
     * Are customs service settings enabled.
     *
     * @param SettingsValues $settings Values.
     *
     * @return bool
     */
    private function is_custom_services_enabled(SettingsValues $settings): bool
    {
        return $settings->has_value(RoyalMailSettingsDefinition::CUSTOM_SERVICES) && 'yes' === $settings->get_value(RoyalMailSettingsDefinition::CUSTOM_SERVICES);
    }
}
