<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Forms;

use OctolizeShippingRoyalMailVendor\Psr\Container\ContainerInterface;
use OctolizeShippingRoyalMailVendor\WPDesk\Persistence\PersistentContainer;
/**
 * Persistent container support for forms.
 *
 * @package WPDesk\Forms
 */
interface ContainerForm
{
    /**
     * @param ContainerInterface $data
     *
     * @return void
     */
    public function set_data(ContainerInterface $data);
    /**
     * Put data from form into a container.
     *
     * @return void
     */
    public function put_data(PersistentContainer $container);
}
