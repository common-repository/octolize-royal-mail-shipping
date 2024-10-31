<?php
/**
 * Settings sidebar.
 *
 * @package Octolize\OctolizeRoyalMailShipping
 */

/**
 * Params.
 *
 * @var string $pro_url
 */
?>
<div class="wpdesk-metabox">
	<div class="wpdesk-stuffbox">
		<h3 class="title"><?php esc_html_e( 'Get Royal Mail WooCommerce Live Rates PRO!', 'octolize-royal-mail-shipping' ); ?></h3>
		<div class="inside">
			<div class="main">
				<ul>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Handling Fees', 'octolize-royal-mail-shipping' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Automatic Box Packing', 'octolize-royal-mail-shipping' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Premium Support', 'octolize-royal-mail-shipping' ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Multicurrency Support', 'octolize-royal-mail-shipping' ); ?></li>
				</ul>

				<a class="button button-primary" href="<?php echo esc_url( $pro_url ); ?>"
				   target="_blank"><?php esc_html_e( 'Upgrade Now &rarr;', 'octolize-royal-mail-shipping' ); ?></a>
			</div>
		</div>
	</div>
</div>
