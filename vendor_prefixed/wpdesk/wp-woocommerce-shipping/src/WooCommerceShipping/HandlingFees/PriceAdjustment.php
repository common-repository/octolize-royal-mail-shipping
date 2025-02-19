<?php

/**
 * Handling fees adjustment.
 *
 * @package WPDesk\WooCommerceShipping\HandlingFees
 */
namespace OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\HandlingFees;

/**
 * Interface for price adjustments.
 */
interface PriceAdjustment
{
    /**
     * @param float $price
     *
     * @return float
     */
    public function apply_on_price($price);
}
