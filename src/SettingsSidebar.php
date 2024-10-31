<?php

namespace Octolize\Shipping\RoyalMail;

use OctolizeShippingRoyalMailVendor\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Can display settings sidebar.
 */
class SettingsSidebar implements Hookable {

	/**
	 * Hooks.
	 */
	public function hooks() {
		add_action( 'octolize_royal_mail_shipping_settings_sidebar', [ $this, 'display_settings_sidebar_when_no_pro_version' ] );
	}

	/**
	 * Maybe display settings sidebar.
	 *
	 * @return void
	 */
	public function display_settings_sidebar_when_no_pro_version() {
		if ( ! defined( 'OCTOLIZE_ROYAL_MAIL_SHIPPING_PRO_VERSION' ) ) {
			$pro_url  = 'https://octol.io/rm-up-box';
			include __DIR__ . '/views/settings-sidebar-html.php';
		}
	}

}
