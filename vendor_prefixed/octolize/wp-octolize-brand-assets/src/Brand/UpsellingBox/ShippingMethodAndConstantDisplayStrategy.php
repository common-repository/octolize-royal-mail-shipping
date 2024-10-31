<?php

namespace OctolizeShippingRoyalMailVendor\Octolize\Brand\UpsellingBox;

use OctolizeShippingRoyalMailVendor\WPDesk\ShowDecision\AndStrategy;
class ShippingMethodAndConstantDisplayStrategy extends AndStrategy
{
    public function __construct(string $method_id, string $constant)
    {
        parent::__construct(new ConstantShouldShowStrategy($constant));
        $this->addCondition(new ShippingMethodShouldShowStrategy($method_id));
    }
}
