<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Forms\Validator;

use OctolizeShippingRoyalMailVendor\WPDesk\Forms\Validator;
class NoValidateValidator implements Validator
{
    public function is_valid($value): bool
    {
        return \true;
    }
    public function get_messages(): array
    {
        return [];
    }
}
