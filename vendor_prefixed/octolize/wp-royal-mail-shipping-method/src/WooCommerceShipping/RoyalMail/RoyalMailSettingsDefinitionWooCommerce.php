<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\RoyalMail;

use OctolizeShippingRoyalMailVendor\Octolize\RoyalMailShippingService\RoyalMailSettingsDefinition;
/**
 * Can handle global and instance settings for WooCommerce shipping method.
 */
class RoyalMailSettingsDefinitionWooCommerce
{
    private $global_method_fields = [RoyalMailSettingsDefinition::SHIPPING_METHOD_TITLE, RoyalMailSettingsDefinition::ADVANCED_OPTIONS_TITLE, RoyalMailSettingsDefinition::DEBUG_MODE];
    /**
     * Form fields.
     *
     * @var array
     */
    private $form_fields;
    /**
     * RoyalMailSettingsDefinitionWooCommerce constructor.
     *
     * @param array $form_fields Form fields.
     */
    public function __construct(array $form_fields)
    {
        $this->form_fields = $form_fields;
        $this->form_fields[RoyalMailSettingsDefinition::SHIPPING_METHOD_TITLE]['description'] = str_replace(['[shipping_zones_link]'], admin_url('admin.php?page=wc-settings&tab=shipping&section'), $this->form_fields[RoyalMailSettingsDefinition::SHIPPING_METHOD_TITLE]['description']);
    }
    /**
     * Get form fields.
     *
     * @return array
     */
    public function get_form_fields()
    {
        return $this->filter_instance_fields($this->form_fields, \false);
    }
    /**
     * Get instance form fields.
     *
     * @return array
     */
    public function get_instance_form_fields()
    {
        return $this->filter_instance_fields($this->form_fields, \true);
    }
    /**
     * Get global method fields.
     *
     * @return array
     */
    protected function get_global_method_fields()
    {
        return $this->global_method_fields;
    }
    /**
     * Filter instance form fields.
     *
     * @param array $all_fields .
     * @param bool  $instance_fields .
     *
     * @return array
     */
    private function filter_instance_fields(array $all_fields, $instance_fields)
    {
        $fields = array();
        foreach ($all_fields as $key => $field) {
            $is_instance_field = !in_array($key, $this->get_global_method_fields(), \true);
            if ($instance_fields && $is_instance_field || !$instance_fields && !$is_instance_field) {
                $fields[$key] = $field;
            }
        }
        return $fields;
    }
}
