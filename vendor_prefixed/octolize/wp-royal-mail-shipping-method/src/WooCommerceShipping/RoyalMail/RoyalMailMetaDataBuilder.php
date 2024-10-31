<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\RoyalMail;

use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Rate\SingleRate;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Shipment\Shipment;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder;
/**
 * Can build Canad Post meta data.
 */
class RoyalMailMetaDataBuilder extends WooCommerceShippingMetaDataBuilder
{
    const META_ROYAL_MAIL_SERVICE_CODE = 'royal_mail_service_id';
    /**
     * Build meta data for rate.
     *
     * @param SingleRate $rate .
     * @param Shipment $shipment .
     *
     * @return array
     */
    public function build_meta_data_for_rate(SingleRate $rate, Shipment $shipment)
    {
        $meta_data = parent::build_meta_data_for_rate($rate, $shipment);
        $meta_data = $this->add_royal_mail_service_code_to_metadata($meta_data, $rate);
        return $meta_data;
    }
    /**
     * Add Royal Mail service to metadata.
     *
     * @param array $meta_data
     * @param SingleRate $rate
     *
     * @return array
     */
    private function add_royal_mail_service_code_to_metadata(array $meta_data, SingleRate $rate)
    {
        $meta_data[self::META_ROYAL_MAIL_SERVICE_CODE] = $rate->service_type;
        return $meta_data;
    }
}
