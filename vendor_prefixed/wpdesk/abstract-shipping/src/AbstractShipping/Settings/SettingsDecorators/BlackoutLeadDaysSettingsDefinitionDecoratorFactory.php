<?php

/**
 * Class BlackoutLeadDaysSettingsDefinitionDecoratorFactory
 *
 * @package WPDesk\AbstractShipping\Settings\SettingsDecorators
 */
namespace OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Settings\SettingsDecorators;

/**
 * Can create Blackout Lead Days settings decorator.
 */
class BlackoutLeadDaysSettingsDefinitionDecoratorFactory extends AbstractDecoratorFactory
{
    const OPTION_ID = 'blackout_lead_days';
    /**
     * @return string
     */
    public function get_field_id()
    {
        return self::OPTION_ID;
    }
    /**
     * @return array
     */
    protected function get_field_settings()
    {
        return array('title' => __('Blackout Lead Days', 'octolize-royal-mail-shipping'), 'type' => 'multiselect', 'description' => __('Blackout Lead Days are used to define days of the week when shop is not processing orders.', 'octolize-royal-mail-shipping'), 'options' => array('1' => __('Monday', 'octolize-royal-mail-shipping'), '2' => __('Tuesday', 'octolize-royal-mail-shipping'), '3' => __('Wednesday', 'octolize-royal-mail-shipping'), '4' => __('Thursday', 'octolize-royal-mail-shipping'), '5' => __('Friday', 'octolize-royal-mail-shipping'), '6' => __('Saturday', 'octolize-royal-mail-shipping'), '7' => __('Sunday', 'octolize-royal-mail-shipping')), 'custom_attributes' => array('size' => 7), 'class' => 'wc-enhanced-select', 'desc_tip' => \true, 'default' => '');
    }
}
