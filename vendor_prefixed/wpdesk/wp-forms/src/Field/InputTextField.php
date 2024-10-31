<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Forms\Field;

use OctolizeShippingRoyalMailVendor\WPDesk\Forms\Sanitizer;
use OctolizeShippingRoyalMailVendor\WPDesk\Forms\Sanitizer\TextFieldSanitizer;
class InputTextField extends BasicField
{
    public function get_sanitizer(): Sanitizer
    {
        return new TextFieldSanitizer();
    }
    public function get_template_name(): string
    {
        return 'input-text';
    }
}
