<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Forms\Sanitizer;

use OctolizeShippingRoyalMailVendor\WPDesk\Forms\Sanitizer;
class TextFieldSanitizer implements Sanitizer
{
    /** @return string|string[] */
    public function sanitize($value)
    {
        if (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }
        return sanitize_text_field($value);
    }
}
