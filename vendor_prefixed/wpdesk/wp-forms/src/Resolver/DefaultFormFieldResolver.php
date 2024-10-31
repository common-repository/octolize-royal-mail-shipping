<?php

namespace OctolizeShippingRoyalMailVendor\WPDesk\Forms\Resolver;

use OctolizeShippingRoyalMailVendor\WPDesk\View\Renderer\Renderer;
use OctolizeShippingRoyalMailVendor\WPDesk\View\Resolver\DirResolver;
use OctolizeShippingRoyalMailVendor\WPDesk\View\Resolver\Resolver;
/**
 * Use with View to resolver form fields to default templates.
 *
 * @package WPDesk\Forms\Resolver
 */
class DefaultFormFieldResolver implements Resolver
{
    /** @var Resolver */
    private $dir_resolver;
    public function __construct()
    {
        $this->dir_resolver = new DirResolver(__DIR__ . '/../../templates');
    }
    public function resolve($name, Renderer $renderer = null): string
    {
        return $this->dir_resolver->resolve($name, $renderer);
    }
}
