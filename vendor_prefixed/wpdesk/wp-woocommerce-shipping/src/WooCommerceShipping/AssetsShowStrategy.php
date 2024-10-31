<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping;

use OctolizeShippingRoyalMailVendor\WPDesk\ShowDecision\ShouldShowStrategy;
/**
 * Can decide when to add assets to frontend.
 */
class AssetsShowStrategy implements ShouldShowStrategy
{
    const MANAGE_WOOCOMMERCE = 'manage_woocommerce';
    /**
     * @inheritDoc
     */
    public function shouldDisplay(): bool
    {
        return current_user_can(self::MANAGE_WOOCOMMERCE) || is_cart() || is_checkout();
    }
}
