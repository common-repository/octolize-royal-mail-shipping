<?php
/**
 * Plugin main class.
 *
 * @package Octolize\Shipping\RoyalMail;
 */

namespace Octolize\Shipping\RoyalMail;

use Octolize\Shipping\RoyalMail\Beacon\Beacon;
use Octolize\Shipping\RoyalMail\Beacon\BeaconDisplayStrategy;
use OctolizeShippingRoyalMailVendor\Octolize\Onboarding\PluginUpgrade\MessageFactory\LiveRatesFsRulesTable;
use OctolizeShippingRoyalMailVendor\Octolize\Onboarding\PluginUpgrade\PluginUpgradeMessage;
use OctolizeShippingRoyalMailVendor\Octolize\Onboarding\PluginUpgrade\PluginUpgradeOnboardingFactory;
use OctolizeShippingRoyalMailVendor\Octolize\RoyalMailShippingService\RoyalMailShippingService;
use OctolizeShippingRoyalMailVendor\Octolize\ShippingExtensions\ShippingExtensions;
use OctolizeShippingRoyalMailVendor\Octolize\Tracker\TrackerInitializer;
use OctolizeShippingRoyalMailVendor\WPDesk\AbstractShipping\Settings\SettingsValuesAsArray;
use OctolizeShippingRoyalMailVendor\WPDesk\Logger\SimpleLoggerFactory;
use OctolizeShippingRoyalMailVendor\WPDesk\Notice\AjaxHandler;
use OctolizeShippingRoyalMailVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use OctolizeShippingRoyalMailVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use OctolizeShippingRoyalMailVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use OctolizeShippingRoyalMailVendor\WPDesk\RepositoryRating\DisplayStrategy\ShippingMethodDisplayDecision;
use OctolizeShippingRoyalMailVendor\WPDesk\RepositoryRating\RatingPetitionNotice;
use OctolizeShippingRoyalMailVendor\WPDesk\RepositoryRating\RepositoryRatingPetitionText;
use OctolizeShippingRoyalMailVendor\WPDesk\RepositoryRating\TextPetitionDisplayer;
use OctolizeShippingRoyalMailVendor\WPDesk\RepositoryRating\TimeWatcher\ShippingMethodGlobalSettingsWatcher;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\Assets;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\OrderMetaData\AdminOrderMetaDataDisplay;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\OrderMetaData\FrontOrderMetaDataDisplay;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\OrderMetaData\SingleAdminOrderMetaDataInterpreterImplementation;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\PluginShippingDecisions;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\RoyalMail\RoyalMailAdminOrderMetaDataDisplay;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\RoyalMail\RoyalMailShippingMethod;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShippingBuilder\WooCommerceShippingMetaDataBuilder;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\ShopSettings;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\Ups\MetaDataInterpreters\FallbackAdminMetaDataInterpreter;
use OctolizeShippingRoyalMailVendor\WPDesk\WooCommerceShipping\Ups\MetaDataInterpreters\PackedPackagesAdminMetaDataInterpreter;
use OctolizeShippingRoyalMailVendor\WPDesk_Plugin_Info;
use OctolizeShippingRoyalMailVendor\Psr\Log\LoggerAwareInterface;
use OctolizeShippingRoyalMailVendor\Psr\Log\LoggerAwareTrait;
use OctolizeShippingRoyalMailVendor\Psr\Log\NullLogger;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @package Octolize\OctolizeShippingRoyalMail
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface, HookableCollection {

	use LoggerAwareTrait;
	use HookableParent;

	/**
	 * Scripts version.
	 *
	 * @var string
	 */
	private $scripts_version = '1';

	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		if ( defined( 'OCTOLIZE_ROYAL_MAIL_SHIPPING_VERSION' ) ) {
			$this->scripts_version = OCTOLIZE_ROYAL_MAIL_SHIPPING_VERSION . '.' . $this->scripts_version;
		}
		parent::__construct( $plugin_info );
		$this->plugin_url       = $this->plugin_info->get_plugin_url();
		$this->plugin_namespace = $this->plugin_info->get_text_domain();
	}

	/**
	 * Returns true when debug mode is on.
	 *
	 * @return bool
	 */
	private function is_debug_mode() {
		$global_royal_mail_settings = $this->get_global_royal_mail_settings();

		return isset( $global_royal_mail_settings['debug_mode'] ) && 'yes' === $global_royal_mail_settings['debug_mode'];
	}


	/**
	 * Get global Royal Mail settings.
	 *
	 * @return string[]
	 */
	private function get_global_royal_mail_settings() {
		/** @phpstan-ignore-next-line */
		return get_option( 'woocommerce_' . RoyalMailShippingService::UNIQUE_ID . '_settings', [] );
	}

	/**
	 * Init plugin
	 *
	 * @return void
	 */
	public function init() {
		$global_royal_mail_settings = new SettingsValuesAsArray( $this->get_global_royal_mail_settings() );

		$origin_country = $this->get_origin_country_code( $global_royal_mail_settings );

		$this->setLogger( $this->is_debug_mode() ? ( new SimpleLoggerFactory( 'royal-mail' ) )->getLogger() : new NullLogger() );

		// @phpstan-ignore-next-line.
		$royal_mail_service = apply_filters( 'octolize_shipping_royal_mail_shipping_service', new RoyalMailShippingService( $this->logger, new ShopSettings( RoyalMailShippingService::UNIQUE_ID ), $origin_country ) );

		$this->add_hookable(
			new Assets( $this->get_plugin_url() . 'vendor_prefixed/wpdesk/wp-woocommerce-shipping/assets', 'royal-mail' )
		);
		$this->init_repository_rating();

		$admin_meta_data_interpreter = new AdminOrderMetaDataDisplay( RoyalMailShippingService::UNIQUE_ID );
		$admin_meta_data_interpreter->add_interpreter(
			new SingleAdminOrderMetaDataInterpreterImplementation(
				WooCommerceShippingMetaDataBuilder::SERVICE_TYPE,
				__( 'Service Code', 'octolize-royal-mail-shipping' )
			)
		);
		$admin_meta_data_interpreter->add_interpreter( new FallbackAdminMetaDataInterpreter() );
		$admin_meta_data_interpreter->add_hidden_order_item_meta_key( WooCommerceShippingMetaDataBuilder::COLLECTION_POINT );
		$admin_meta_data_interpreter->add_interpreter( new PackedPackagesAdminMetaDataInterpreter() );
		$this->add_hookable( $admin_meta_data_interpreter );

		$meta_data_interpreter = new FrontOrderMetaDataDisplay( RoyalMailShippingService::UNIQUE_ID );
		$this->add_hookable( $meta_data_interpreter );

		// @phpstan-ignore-next-line.
		$plugin_shipping_decisions = new PluginShippingDecisions( $royal_mail_service, $this->logger );

		RoyalMailShippingMethod::set_plugin_shipping_decisions( $plugin_shipping_decisions );

		$this->add_hookable( new RoyalMailAdminOrderMetaDataDisplay( RoyalMailShippingService::UNIQUE_ID ) );

		$this->add_hookable( new ShippingExtensions( $this->plugin_info ) );

		$this->init_tracker();

		$this->init_upgrade_onboarding();

		parent::init();
	}

	private function init_upgrade_onboarding() {
		$upgrade_onboarding = new PluginUpgradeOnboardingFactory(
			$this->plugin_info->get_plugin_name(),
			$this->plugin_info->get_version(),
			$this->plugin_info->get_plugin_file_name()
		);
		$upgrade_onboarding->add_upgrade_message(
			new PluginUpgradeMessage(
				'1.4.0',
				$this->plugin_info->get_plugin_url() . '/assets/images/icon-recipe-edit.svg',
				__( 'We have added new services', 'octolize-royal-mail-shipping' ),
				sprintf( __( 'We\'re thrilled to announce that with this update of the Royal Mail Live Rates plugin, we\'ve incorporated two important changes:%1$s - Pricing has been updated to reflect the latest changes.%1$s - Two new services are available: Tracked 24 and Tracked 48.%1$s%1$sWe\'re confident that this release will help you better fit the needs of your customers.', 'octolize-royal-mail-shipping' ), '<br/>' ),
				'',
				''
			)
		);
		$upgrade_onboarding->add_upgrade_message( ( new LiveRatesFsRulesTable() )->create_message( '2.0.0', $this->plugin_info->get_plugin_url() ) );
		$upgrade_onboarding->create_onboarding();
	}

	/**
	 * @return void
	 */
	private function init_tracker() {
		$this->add_hookable( TrackerInitializer::create_from_plugin_info_for_shipping_method( $this->plugin_info, RoyalMailShippingService::UNIQUE_ID ) );
	}


	/**
	 * Show repository rating notice when time comes.
	 *
	 * @return void
	 */
	private function init_repository_rating() {
		$this->add_hookable( new AjaxHandler( trailingslashit( $this->get_plugin_url() ) . 'vendor_prefixed/wpdesk/wp-notice/assets' ) );

		$time_tracker = new ShippingMethodGlobalSettingsWatcher( RoyalMailShippingService::UNIQUE_ID );
		$this->add_hookable( $time_tracker );
		$this->add_hookable(
			new RatingPetitionNotice(
				$time_tracker,
				RoyalMailShippingService::UNIQUE_ID,
				__( 'Royal Mail Live Rates for WooCommerce', 'octolize-royal-mail-shipping' ),
				'https://octol.io/rate-rm'
			)
		);

		$this->add_hookable(
			new TextPetitionDisplayer(
				'woocommerce_after_settings_shipping',
				new ShippingMethodDisplayDecision( new \WC_Shipping_Zones(), RoyalMailShippingService::UNIQUE_ID ),
				new RepositoryRatingPetitionText(
					'Octolize',
					__( 'Royal Mail Live Rates for WooCommerce', 'octolize-royal-mail-shipping' ),
					'https://octol.io/rate-rm',
					'center'
				)
			)
		);

		$beacon = new Beacon(
			new BeaconDisplayStrategy(),
			trailingslashit( $this->get_plugin_url() ) . 'vendor_prefixed/wpdesk/wp-helpscout-beacon/assets/'
		);
		$beacon->hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		parent::hooks();

		add_filter( 'woocommerce_shipping_methods', [ $this, 'woocommerce_shipping_methods_filter' ], 20, 1 );

		add_filter(
			'pre_option_woocommerce_settings_shipping_recommendations_hidden',
			function () {
				return 'yes';
			}
		);

		$this->add_hookable( new SettingsSidebar() );

		$this->hooks_on_hookable_objects();
	}

	/**
	 * Adds shipping method to Woocommerce.
	 *
	 * @param string[] $methods Methods.
	 *
	 * @return string[]
	 */
	public function woocommerce_shipping_methods_filter( $methods ) {
		$methods[ RoyalMailShippingService::UNIQUE_ID ] = RoyalMailShippingMethod::class;

		return $methods;
	}

	/**
	 * Quick links on plugins page.
	 *
	 * @param string[] $links .
	 *
	 * @return string[]
	 */
	public function links_filter( $links ) {
		$docs_link    = 'https://octol.io/rm-docs';
		$support_link = 'https://octol.io/rm-support';
		$settings_url = \admin_url( 'admin.php?page=wc-settings&tab=shipping&section=' . RoyalMailShippingService::UNIQUE_ID );

		$external_attributes = ' target="_blank" ';

		$plugin_links = [
			'<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'octolize-royal-mail-shipping' ) . '</a>',
			'<a href="' . esc_url( $docs_link ) . '"' . $external_attributes . '>' . __( 'Docs', 'octolize-royal-mail-shipping' ) . '</a>',
			'<a href="' . esc_url( $support_link ) . '"' . $external_attributes . '>' . __( 'Support', 'octolize-royal-mail-shipping' ) . '</a>',
		];

		if ( ! defined( 'OCTOLIZE_ROYAL_MAIL_SHIPPING_PRO_VERSION' ) ) {
			$upgrade_link   = 'https://octol.io/rm-upgrade';
			$plugin_links[] = '<a target="_blank" href="' . $upgrade_link . '" style="color:#d64e07;font-weight:bold;">' . __( 'Buy PRO', 'octolize-royal-mail-shipping' ) . '</a>';
		}

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Get origin country code.
	 *
	 * @param SettingsValuesAsArray $global_royal_mail_settings .
	 *
	 * @return string
	 */
	private function get_origin_country_code( $global_royal_mail_settings ) {
		$origin_country_code_with_state = get_option( 'woocommerce_default_country', '' );

		/** @phpstan-ignore-next-line */
		$origin_country = explode( ':', $origin_country_code_with_state );

		return $origin_country[0];
	}

}
