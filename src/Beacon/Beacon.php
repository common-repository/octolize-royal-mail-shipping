<?php

namespace Octolize\Shipping\RoyalMail\Beacon;

/**
 * Can display Help Scout beacon.
 */
class Beacon extends \OctolizeShippingRoyalMailVendor\WPDesk\Beacon\Beacon {

	/**
	 * Beacon constructor.
	 *
	 * @param BeaconDisplayStrategy $strategy .
	 * @param string                $assets_url .
	 */
	public function __construct( BeaconDisplayStrategy $strategy, $assets_url ) {
		parent::__construct(
			'374f5877-86e9-4e4c-8a43-b262eeda6e3d',
			$strategy,
			$assets_url,
			'hs-beacon-search',
			'data:image/png;base64,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'
		);
		$this->confirmation_message = __( 'When you click OK, our HelpScout beacon will open where you can start a conversation with us. The beacon loads the chat form and also potentially sets cookies.', 'octolize-royal-mail-shipping' );
	}

}
