<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Forms\Field;

use OctolizeShippingRoyalMailVendor\WPDesk\Forms\Serializer\ProductSelectSerializer;
use OctolizeShippingRoyalMailVendor\WPDesk\Forms\Serializer;
class ProductSelect extends SelectField
{
    public function __construct()
    {
        $this->set_multiple();
    }
    public function has_serializer(): bool
    {
        return \true;
    }
    public function get_serializer(): Serializer
    {
        return new ProductSelectSerializer();
    }
    public function get_template_name(): string
    {
        return 'product-select';
    }
}
