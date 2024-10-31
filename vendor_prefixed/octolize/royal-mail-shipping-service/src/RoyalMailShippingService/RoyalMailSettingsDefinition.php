<?php

namespace OctolizeShippingRoyalMailVendor\Octolize\RoyalMailShippingService;

use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Settings\SettingsDefinition;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Settings\SettingsValues;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\FreeShipping\FreeShippingFields;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingMethod\RateMethod\Fallback\FallbackRateMethod;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShopSettings;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\WooCommerceNotInitializedException;
/**
 * A class that defines the basic settings for the shipping method.
 */
class RoyalMailSettingsDefinition extends SettingsDefinition
{
    const CUSTOM_SERVICES_CHECKBOX_CLASS = 'wpdesk_wc_shipping_custom_service_checkbox';
    const SHIPPING_METHOD_TITLE = 'shipping_method_title';
    const ADVANCED_OPTIONS_TITLE = 'advanced_options_title';
    const DEBUG_MODE = 'debug_mode';
    const METHOD_SETTINGS_TITLE = 'method_settings_title';
    const TITLE = 'title';
    const FALLBACK = 'fallback';
    const CUSTOM_SERVICES = 'custom_services';
    const SERVICES = 'services';
    const FREE_SHIPPING = 'free_shipping';
    const PACKAGE_SETTINGS_TITLE = 'package_settings_title';
    const PACKAGE_LENGTH = 'package_length';
    const PACKAGE_WIDTH = 'package_width';
    const PACKAGE_HEIGHT = 'package_height';
    const PACKAGE_WEIGHT = 'package_weight';
    const RATE_ADJUSTMENTS_TITLE = 'rate_adjustments_title';
    const REMOVE_TAX = 'remove_tax';
    const INSURANCE = 'insurance';
    /**
     * Shop settings.
     *
     * @var ShopSettings
     */
    private $shop_settings;
    /**
     * @param ShopSettings $shop_settings .
     */
    public function __construct(ShopSettings $shop_settings)
    {
        $this->shop_settings = $shop_settings;
    }
    /**
     * Validate settings.
     *
     * @param SettingsValues $settings Settings.
     *
     * @return bool
     */
    public function validate_settings(SettingsValues $settings): bool
    {
        return \true;
    }
    /**
     * Prepare country state options.
     *
     * @return array
     */
    private function prepare_country_state_options(): array
    {
        try {
            $countries = $this->shop_settings->get_countries();
        } catch (WooCommerceNotInitializedException $e) {
            $countries = array();
        }
        $country_state_options = $countries;
        foreach ($country_state_options as $country_code => $country) {
            $states = $this->shop_settings->get_states($country_code);
            if ($states) {
                unset($country_state_options[$country_code]);
                foreach ($states as $state_code => $state_name) {
                    $country_state_options[$country_code . ':' . $state_code] = $country . ' &mdash; ' . $state_name;
                }
            }
        }
        return $country_state_options;
    }
    /**
     * Initialise Settings Form Fields.
     *
     * @return array
     */
    public function get_form_fields()
    {
        $services = new RoyalMailServices();
        $docs_link = 'https://octol.io/rm-method-docs';
        $zones_link = '[shipping_zones_link]';
        $how_to_add_link = 'https://octol.io/rm-general-settings-adding-method';
        $connection_fields = array(self::SHIPPING_METHOD_TITLE => array('title' => __('Royal Mail', 'octolize-royal-mail-shipping'), 'type' => 'title', 'description' => sprintf(
            // Translators: docs link.
            __('These are the Royal Mail Live Rates plugin general settings. In order to learn more about its configuration please refer to its %1$sdedicated documentation →%2$s%3$s
                        Please mind that %4$syou don\'t need to enter your Royal Mail account credentials here in order to obtain and display the rates to your customers%5$s. The Royal Mail shipping rates will be calculated automatically in the background without it.%6$s
                        %7$sGo to the Shipping Zones%8$s to add the Royal Mail shipping method or learn %9$show to add the Royal Mail Live Rates there step by step →%10$s', 'octolize-royal-mail-shipping'),
            '<a href="' . $docs_link . '" target="_blank">',
            '</a>',
            '<br/><br/>',
            '<strong>',
            '</strong>',
            '<br/><br/>',
            '<a href="' . $zones_link . '">',
            '</a>',
            '<a href="' . $how_to_add_link . '" target="_blank">',
            '</a>'
        ), 'default' => ''));
        $fields = array(self::ADVANCED_OPTIONS_TITLE => array('title' => __('Advanced Options', 'octolize-royal-mail-shipping'), 'type' => 'title', 'default' => ''), self::DEBUG_MODE => array('title' => __('Debug Mode', 'octolize-royal-mail-shipping'), 'label' => __('Enable debug mode', 'octolize-royal-mail-shipping'), 'type' => 'checkbox', 'description' => __('Enable debug mode to display additional tech information, incl. the data sent to Royal Mail API, visible only for Admins and Shop Managers in the cart and checkout.', 'octolize-royal-mail-shipping'), 'desc_tip' => \true, 'default' => 'no'));
        $instance_fields = array(self::METHOD_SETTINGS_TITLE => array('title' => __('Method Settings', 'octolize-royal-mail-shipping'), 'description' => __('Manage the way how the Royal Mail services are displayed in the cart and checkout.', 'octolize-royal-mail-shipping'), 'type' => 'title', 'default' => ''), self::TITLE => array('title' => __('Method Title', 'octolize-royal-mail-shipping'), 'type' => 'text', 'description' => __('Define the Royal Mail shipping method title which should be used in the cart/checkout when the Fallback option was triggered.', 'octolize-royal-mail-shipping'), 'default' => __('Royal Mail Live Rates', 'octolize-royal-mail-shipping'), 'desc_tip' => \true), self::FALLBACK => array('type' => FallbackRateMethod::FIELD_TYPE_FALLBACK, 'description' => __('Enable to offer flat rate cost for shipping so that the user can still checkout, if API for some reason returns no matching rates.', 'octolize-royal-mail-shipping'), 'default' => ''), self::FREE_SHIPPING => array('title' => __('Free Shipping', 'octolize-royal-mail-shipping'), 'type' => FreeShippingFields::FIELD_TYPE_FREE_SHIPPING, 'default' => ''), self::CUSTOM_SERVICES => array('title' => __('Services', 'octolize-royal-mail-shipping'), 'label' => __('Enable the services\' custom settings', 'octolize-royal-mail-shipping'), 'type' => 'checkbox', 'description' => __('Decide which services should be displayed and which not, change their names and order. Please mind that enabling a service does not guarantee it will be visible in the cart/checkout. It has to be available for the provided package weight, origin and destination in order to be displayed.', 'octolize-royal-mail-shipping'), 'desc_tip' => \true, 'class' => self::CUSTOM_SERVICES_CHECKBOX_CLASS, 'default' => 'no'), self::SERVICES => array('title' => __('Services Table', 'octolize-royal-mail-shipping'), 'type' => 'services', 'default' => '', 'options' => $services->get_all_services()), self::PACKAGE_SETTINGS_TITLE => array('title' => __('Package Settings', 'octolize-royal-mail-shipping'), 'description' => sprintf(__('Define the package details including its dimensions and weight which will be used as default for this shipping method.', 'octolize-royal-mail-shipping')), 'type' => 'title', 'default' => ''), self::PACKAGE_LENGTH => array('title' => __('Length [cm] *', 'octolize-royal-mail-shipping'), 'type' => 'number', 'description' => __('Enter only a numeric value without the metric symbol.', 'octolize-royal-mail-shipping'), 'desc_tip' => \true, 'custom_attributes' => array('required' => 'required', 'step' => '0.01'), 'default' => ''), self::PACKAGE_WIDTH => array('title' => __('Width [cm] *', 'octolize-royal-mail-shipping'), 'type' => 'number', 'description' => __('Enter only a numeric value without the metric symbol.', 'octolize-royal-mail-shipping'), 'desc_tip' => \true, 'custom_attributes' => array('required' => 'required', 'step' => '0.01'), 'default' => ''), self::PACKAGE_HEIGHT => array('title' => __('Height [cm] *', 'octolize-royal-mail-shipping'), 'type' => 'number', 'description' => __('Enter only a numeric value without the metric symbol.', 'octolize-royal-mail-shipping'), 'desc_tip' => \true, 'custom_attributes' => array('required' => 'required', 'step' => '0.01'), 'default' => ''), self::PACKAGE_WEIGHT => array('title' => __('Default weight [kg] *', 'octolize-royal-mail-shipping'), 'type' => 'number', 'description' => __('Enter the package weight value which will be used as default if none of the products\' in the cart individual weight has been filled in or if the cart total weight equals 0 kg.', 'octolize-royal-mail-shipping'), 'desc_tip' => \true, 'custom_attributes' => array('required' => 'required', 'step' => '0.001'), 'default' => ''), self::RATE_ADJUSTMENTS_TITLE => array('title' => __('Rates Adjustments', 'octolize-royal-mail-shipping'), 'description' => sprintf(__('Use these settings and adjust them to your needs to get more accurate rates. Read %1$swhat affects the Royal Mail rates in Royal Mail WooCommerce plugin →%2$s', 'octolize-royal-mail-shipping'), sprintf('<a href="%s" target="_blank">', __('https://octol.io/rm-free-rates', 'octolize-royal-mail-shipping')), '</a>'), 'type' => 'title', 'default' => ''), self::INSURANCE => array('title' => __('Insurance', 'octolize-royal-mail-shipping'), 'label' => __('Include optional compensation', 'octolize-royal-mail-shipping'), 'type' => 'checkbox', 'description' => __('If enabled, the order total value will be considered as a compensation amount. Please mind that some economy services might be not available if the value of the purchased goods exceeds a certain limit and therefore they require to be shipped with other services instead.', 'octolize-royal-mail-shipping'), 'desc_tip' => \false, 'default' => 'no'), self::REMOVE_TAX => array('title' => __('Tax', 'octolize-royal-mail-shipping'), 'label' => __('Remove the tax', 'octolize-royal-mail-shipping'), 'type' => 'checkbox', 'description' => __('Tick this checkbox in order to strip the 20% tax value from the shipping rates coming from Royal Mail.', 'octolize-royal-mail-shipping'), 'desc_tip' => \false, 'default' => 'no'));
        return $connection_fields + $fields + $instance_fields;
    }
}
