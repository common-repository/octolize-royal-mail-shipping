<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Logger;

use OctolizeShippingRoyalMailVendor\Monolog\Logger;
/*
 * @package WPDesk\Logger
 */
interface LoggerFactory
{
    /**
     * Returns created Logger
     *
     * @param string $name
     *
     * @return Logger
     */
    public function getLogger($name);
}
