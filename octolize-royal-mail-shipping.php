<?php
/**
 * Plugin Name: Royal Mail Live Rates
 * Plugin URI: https://wordpress.org/plugins/octolize-royal-mail-shipping/
 * Description: Royal Mail WooCommerce shipping methods with real-time calculated shipping rates based on the official Royal Mail current pricing.
 * Version: 2.0.1
 * Author: Octolize
 * Author URI: https://octol.io/rm-author
 * Text Domain: octolize-australia-post-shipping
 * Domain Path: /lang/
 * Requires at least: 6.4
 * Tested up to: 6.7
 * WC requires at least: 9.0
 * WC tested up to: 9.4
 * Requires PHP: 7.4
 *
 * Copyright 2019 WP Desk Ltd.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package Octolize\Shipping\AustraliaPost
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/* THIS VARIABLE CAN BE CHANGED AUTOMATICALLY */
$plugin_version = '2.0.1';

$plugin_name        = 'Octolize Royal Mail Shipping';
$plugin_class_name  = '\Octolize\Shipping\RoyalMail\Plugin';
$plugin_text_domain = 'octolize-royal-mail-shipping';
$product_id         = 'Octolize Royal Mail Shipping';
$plugin_file        = __FILE__;
$plugin_dir         = __DIR__;

define( $plugin_class_name, $plugin_version );
define( 'OCTOLIZE_ROYAL_MAIL_SHIPPING_VERSION', $plugin_version );

$requirements = [
	'php'          => '7.4',
	'wp'           => '4.9',
	'repo_plugins' => [
		[
			'name'      => 'woocommerce/woocommerce.php',
			'nice_name' => 'WooCommerce',
			'version'   => '5.0',
		],
	],
];

require __DIR__ . '/vendor_prefixed/wpdesk/wp-plugin-flow-common/src/plugin-init-php52-free.php';
