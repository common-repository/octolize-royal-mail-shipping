<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\Standard;

use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Rate\ShipmentRating;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Settings\SettingsValuesAsArray;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Shipment\Shipment;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\ShippingServiceCapability\CanRate;
use OctolizeShippingRoyalMailVendor\WPDesk\Persistence\Adapter\ArrayContainer;
use OctolizeShippingRoyalMailVendor\WPDesk\Persistence\Adapter\WooCommerce\WooCommerceSessionContainer;
use OctolizeShippingRoyalMailVendor\WPDesk\Tracker\Shop;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\Cache\CachedRating;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingBuilder;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingMethod;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingMethod\HasRateCache;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\ErrorLogCatcher;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\RateMethod;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShopSettings;
/**
 * Rate method that uses Shipping service to create live rates in method.
 *
 * @package WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\Standard\
 */
class StandardServiceRateMethod implements RateMethod
{
    /** @var CanRate */
    private $rate_provider;
    /**
     * StandardServiceRateMethod constructor.
     *
     * @param CanRate         $service Service that provides the rates.
     */
    public function __construct(CanRate $service)
    {
        $this->rate_provider = $service;
    }
    /**
     * Adds shipment rates to method.
     *
     * @param \WC_Shipping_Method $method Method to add rates.
     * @param ErrorLogCatcher $logger Special logger that can return last error.
     * @param WooCommerceShippingMetaDataBuilder $metadata_builder
     * @param WooCommerceShippingBuilder $shipment_builder Class that can build shipment from package
     *
     * @return void
     */
    public function handle_rates(\WC_Shipping_Method $method, ErrorLogCatcher $logger, WooCommerceShippingMetaDataBuilder $metadata_builder, WooCommerceShippingBuilder $shipment_builder)
    {
        try {
            $rates_count = count($method->rates);
            $this->add_rates($this->rate_provider, $method, $metadata_builder, $shipment_builder);
            if ($rates_count === count($method->rates)) {
                $logger->info(__('No rates added from standard service rates!', 'octolize-royal-mail-shipping'));
            }
        } catch (\Exception $e) {
            $logger->error($e->getMessage());
        }
    }
    /**
     * Rate shipment.
     *
     * @param CanRate $service Service.
     * @param \WC_Shipping_Method $method Method.
     * @param WooCommerceShippingMetaDataBuilder $meta_data_builder Meta data builder.
     * @param WooCommerceShippingBuilder $shipping_builder Class that can build shipment from package
     */
    private function add_rates(CanRate $service, ShippingMethod $method, WooCommerceShippingMetaDataBuilder $meta_data_builder, WooCommerceShippingBuilder $shipping_builder)
    {
        $service_id = $method->id;
        $service_settings = apply_filters("{$service_id}_settings_before_rate", $method->create_settings_values_as_array(), $method);
        if ($service->is_rate_enabled($service_settings)) {
            $shipment = apply_filters("{$service_id}_shipment_before_rate", $shipping_builder->build_shipment(), $method);
            if ($method instanceof HasRateCache) {
                $cached_rating = new CachedRating((new \OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\Cache\ShopSettingsMd5HashGenerator())->generate_md5_hash(new ShopSettings($service_id)), WC()->session ? new WooCommerceSessionContainer(WC()->session) : new ArrayContainer());
                $rates_from_response = $cached_rating->rate_shipment($service_settings, $shipment, $service);
            } else {
                $rates_from_response = $service->rate_shipment($service_settings, $shipment);
            }
            $rates_from_response = apply_filters("{$service_id}_rates", $rates_from_response, $method);
            $meta_data_builder = apply_filters("{$service_id}_meta_data_builder", $meta_data_builder, $method);
            $this->add_rates_from_response($method, $shipment, $rates_from_response, $meta_data_builder);
        }
    }
    /**
     * Add Woocommerce shipping rates.
     *
     * @param \WC_Shipping_Method $method Method.
     * @param Shipment $shipment Shipment.
     * @param ShipmentRating $shipment_ratings Shipment rates.
     * @param WooCommerceShippingMetaDataBuilder $meta_data_builder Meta data builder.
     */
    private function add_rates_from_response(\WC_Shipping_Method $method, Shipment $shipment, ShipmentRating $shipment_ratings, WooCommerceShippingMetaDataBuilder $meta_data_builder)
    {
        foreach ($shipment_ratings->get_ratings() as $rate) {
            if (isset($meta_data_builder)) {
                $meta_data = $meta_data_builder->build_meta_data_for_rate($rate, $shipment);
            } else {
                $meta_data = [];
            }
            $meta_data = (array) apply_filters($method->id . '/rate/meta_data', $meta_data, $method);
            $method->add_rate(['id' => $method->id . ':' . $method->instance_id . ':' . $rate->service_type, 'label' => $rate->service_name, 'cost' => $rate->total_charge->amount, 'sort' => 0, 'meta_data' => $meta_data]);
        }
    }
    /**
     * Add rate method settings to shipment service settings.
     *
     * @param array $settings Settings from \WC_Shipping_Method
     *
     * @return array Settings with rate settings
     */
    public function add_to_settings(array $settings)
    {
        return $settings;
    }
}
