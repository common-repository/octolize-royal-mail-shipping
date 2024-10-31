<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Forms\Sanitizer;

use OctolizeShippingRoyalMailVendor\WPDesk\Forms\Sanitizer;
class NoSanitize implements Sanitizer
{
    public function sanitize($value)
    {
        return $value;
    }
}
