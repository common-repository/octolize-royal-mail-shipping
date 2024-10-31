<?php

/**
 * @package WPDesk\WooCommerceShipping\RoyalMail
 */
namespace OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\RoyalMail;

use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\OrderMetaData\AdminOrderMetaDataDisplay;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\OrderMetaData\SingleAdminOrderMetaDataInterpreterImplementation;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder;
/**
 * Can hide meta data in order.
 */
class RoyalMailAdminOrderMetaDataDisplay extends AdminOrderMetaDataDisplay
{
    /**
     * @param string $method_id .
     */
    public function __construct($method_id)
    {
        parent::__construct($method_id);
        $this->add_hidden_order_item_meta_key(WooCommerceShippingMetaDataBuilder::SERVICE_TYPE);
        $this->add_interpreter(new SingleAdminOrderMetaDataInterpreterImplementation(RoyalMailMetaDataBuilder::META_ROYAL_MAIL_SERVICE_CODE, __('Royal Mail Service Code', 'octolize-royal-mail-shipping')));
    }
}
