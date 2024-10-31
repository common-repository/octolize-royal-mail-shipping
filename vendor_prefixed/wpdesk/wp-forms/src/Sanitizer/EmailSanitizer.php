<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Forms\Sanitizer;

use OctolizeShippingRoyalMailVendor\WPDesk\Forms\Sanitizer;
class EmailSanitizer implements Sanitizer
{
    public function sanitize($value): string
    {
        return sanitize_email($value);
    }
}
