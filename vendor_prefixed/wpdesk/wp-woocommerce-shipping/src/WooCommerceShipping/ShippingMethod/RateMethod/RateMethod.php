<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod;

use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingBuilder;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder;
interface RateMethod
{
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
    public function handle_rates(\WC_Shipping_Method $method, ErrorLogCatcher $logger, WooCommerceShippingMetaDataBuilder $metadata_builder, WooCommerceShippingBuilder $shipment_builder);
    /**
     * Add rate method settings to shipment service settings.
     *
     * @param array $settings Settings from \WC_Shipping_Method
     *
     * @return array Settings with rate settings
     */
    public function add_to_settings(array $settings);
}
