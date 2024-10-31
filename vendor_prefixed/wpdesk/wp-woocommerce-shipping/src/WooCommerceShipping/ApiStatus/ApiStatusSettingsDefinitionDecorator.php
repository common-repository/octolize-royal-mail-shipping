<?php

/**
 * Decorator for api status settings field.
 *
 * @package WPDesk\WooCommerceShipping\ApiStatus
 */
namespace OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ApiStatus;

use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Settings\DefinitionModifier\SettingsDefinitionModifierAfter;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatus;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\CustomFields\ApiStatus\FieldApiStatusAjax;
/**
 * Can decorate settings for estimated delivery field.
 */
class ApiStatusSettingsDefinitionDecorator extends SettingsDefinitionModifierAfter
{
    const API_STATUS = 'api_status';
    /**
     * ApiStatusSettingsDefinitionDecorator constructor.
     *
     * @param SettingsDefinition $ups_settings_definition .
     * @param string $after_field API Status field will be added after this field.
     * @param FieldApiStatusAjax $api_status_ajax_handler .
     * @param string $service_id .
     */
    public function __construct(SettingsDefinition $ups_settings_definition, $after_field, FieldApiStatusAjax $api_status_ajax_handler, $service_id)
    {
        parent::__construct($ups_settings_definition, $after_field, self::API_STATUS, ['title' => __('API Connection Status', 'octolize-royal-mail-shipping'), 'type' => 'api_status', 'class' => 'flexible_shipping_api_status', 'default' => __('Checking...', 'octolize-royal-mail-shipping'), 'description' => __('If you encounter any problems with establishing the API connection, the detailed information on its cause will be displayed here.', 'octolize-royal-mail-shipping'), 'desc_tip' => \true, FieldApiStatus::SECURITY_NONCE => wp_create_nonce($api_status_ajax_handler->get_nonce_name()), FieldApiStatus::SHIPPING_SERVICE_ID => $service_id]);
    }
}
