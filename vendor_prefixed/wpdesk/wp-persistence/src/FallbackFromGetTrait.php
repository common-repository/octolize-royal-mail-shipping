<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Persistence;

use OctolizeShippingRoyalMailVendor\Psr\Container\NotFoundExceptionInterface;
trait FallbackFromGetTrait
{
    public function get_fallback(string $id, $fallback = null)
    {
        try {
            return $this->get($id);
        } catch (NotFoundExceptionInterface $e) {
            return $fallback;
        }
    }
}
