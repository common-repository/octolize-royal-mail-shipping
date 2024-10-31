<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\RoyalMail;

use OctolizeShippingRoyalMailVendor\Octolize\RoyalMailShippingService\RoyalMailServices;
use OctolizeShippingRoyalMailVendor\Octolize\RoyalMailShippingService\RoyalMailSettingsDefinition;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatusAjax;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingBuilder\AddressProvider;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceAddressSender;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingMethod;
/**
 * Royal Mail Shipping Method.
 */
class RoyalMailShippingMethod extends ShippingMethod implements ShippingMethod\HasFreeShipping
{
    /**
     * Supports.
     *
     * @var array
     */
    public $supports = array('settings', 'shipping-zones', 'instance-settings');
    /**
     * @var FieldApiStatusAjax
     */
    protected static $api_status_ajax_handler;
    /**
     * Set api status field AJAX handler.
     *
     * @param FieldApiStatusAjax $api_status_ajax_handler .
     */
    public static function set_api_status_ajax_handler(FieldApiStatusAjax $api_status_ajax_handler)
    {
        static::$api_status_ajax_handler = $api_status_ajax_handler;
    }
    /**
     * Prepare description.
     * Description depends on current page.
     *
     * @return string
     */
    private function prepare_description()
    {
        $docs_link = 'https://octol.io/rm-zone-docs';
        return sprintf(
            // Translators: docs URL.
            __('Dynamically calculated Royal Mail live rates based on the official Royal Mail current pricing. %1$sLearn more →%2$s', 'octolize-royal-mail-shipping'),
            '<a target="_blank" href="' . $docs_link . '">',
            '</a>'
        );
    }
    /**
     * Init method.
     */
    public function init()
    {
        parent::init();
        $this->method_description = $this->prepare_description();
    }
    /**
     * Init form fields.
     */
    public function build_form_fields()
    {
        $royal_mail_settings_definition = new RoyalMailSettingsDefinitionWooCommerce($this->form_fields);
        $this->form_fields = $royal_mail_settings_definition->get_form_fields();
        $this->instance_form_fields = $royal_mail_settings_definition->get_instance_form_fields();
    }
    /**
     * Create meta data builder.
     *
     * @return RoyalMailMetaDataBuilder
     */
    protected function create_metadata_builder()
    {
        return new RoyalMailMetaDataBuilder($this);
    }
    /**
     * Render shipping method settings.
     */
    public function admin_options()
    {
        if ($this->instance_id) {
            $royal_mail_services = new RoyalMailServices();
            $shipping_zone = $this->get_zone_for_shipping_method($this->instance_id);
            $services_options = [];
            if ($this->is_zone_for_domestic_uk_services($shipping_zone)) {
                $services_options = array_merge($services_options, $royal_mail_services->get_services_domestic_uk());
            }
            if ($this->is_zone_for_international_services($shipping_zone)) {
                $services_options = array_merge($services_options, $royal_mail_services->get_services_international());
            }
            $this->instance_form_fields[RoyalMailSettingsDefinition::SERVICES]['options'] = $services_options;
        }
        parent::admin_options();
        include __DIR__ . '/view/shipping-method-script.php';
    }
    /**
     * Is custom origin?
     *
     * @return bool
     */
    public function is_custom_origin()
    {
        return \false;
    }
    /**
     * Create sender address.
     *
     * @return AddressProvider
     */
    public function create_sender_address()
    {
        return new WooCommerceAddressSender();
    }
    /**
     * @param int $instance_id
     *
     * @return \WC_Shipping_Zone
     */
    private function get_zone_for_shipping_method($instance_id)
    {
        $woocommerce_shipping_zones = \WC_Shipping_Zones::get_zones();
        $zone = new \WC_Shipping_Zone();
        foreach ($woocommerce_shipping_zones as $woocommerce_shipping_zone) {
            foreach ($woocommerce_shipping_zone['shipping_methods'] as $woocommerce_shipping_method) {
                if ($woocommerce_shipping_method->instance_id === $instance_id) {
                    $zone = \WC_Shipping_Zones::get_zone($woocommerce_shipping_zone['id']);
                }
            }
        }
        return $zone;
    }
    /**
     * @param \WC_Shipping_Zone $zone
     *
     * @return bool
     */
    private function is_zone_for_domestic_uk_services(\WC_Shipping_Zone $zone)
    {
        $is_domestic = \false;
        $zone_locations = $zone->get_zone_locations();
        if (count($zone_locations)) {
            foreach ($zone_locations as $zone_location) {
                if ('country' === $zone_location->type || 'state' === $zone_location->type) {
                    $code_exploded = explode(':', $zone_location->code);
                    $country_code = $code_exploded[0];
                    $is_domestic = $is_domestic || 'GB' === $country_code;
                }
            }
        } else {
            $is_domestic = \true;
        }
        return $is_domestic;
    }
    /**
     * @param \WC_Shipping_Zone $zone
     *
     * @return bool
     */
    private function is_zone_for_international_services(\WC_Shipping_Zone $zone)
    {
        $is_international = \false;
        $zone_locations = $zone->get_zone_locations();
        if (count($zone_locations)) {
            foreach ($zone_locations as $zone_location) {
                if ('country' === $zone_location->type || 'state' === $zone_location->type) {
                    $code_exploded = explode(':', $zone_location->code);
                    $country_code = $code_exploded[0];
                    $is_international = $is_international || 'GB' !== $country_code;
                } elseif ('continent' === $zone_location->type) {
                    $is_international = \true;
                }
            }
        } else {
            $is_international = \true;
        }
        return $is_international;
    }
}
