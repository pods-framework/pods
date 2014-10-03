<?php
namespace Pods_Unit_Tests;

class Tests_Pods extends Pods_UnitTestCase {
	protected $object;

	public function setUp() {
		parent::setUp();
		$this->object = Pods();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_pods_instance() {
		$this->assertClassHasStaticAttribute( 'instance', 'Pods' );
	}

	/**
	 * @covers Pods::setup_constants
	 */
	public function test_constants() {
		// Plugin Folder URL
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_url( __FILE__ ) );
		$this->assertSame( PODS_PLUGIN_URL, $path );

		// Plugin Folder Path
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_path( __FILE__ ) );
		$this->assertSame( PODS_PLUGIN_DIR, $path );

		// Plugin Root File
		$path = str_replace( 'tests/unit-tests/', '', plugin_dir_path( __FILE__ ) );
		$this->assertSame( PODS_PLUGIN_FILE, $path.'init.php' );
	}

	/**
	 * @covers Pods::includes
	 */
	public function test_includes() {
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/settings/register-settings.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/install.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/actions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/deprecated-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/ajax-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/template-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/checkout/template.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/checkout/functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/cart/template.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/cart/functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/cart/actions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/class-pods-api.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/class-pods-cache-helper.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/class-pods-fees.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/class-pods-html-elements.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/class-pods-logging.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/class-pods-session.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/class-pods-roles.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/class-pods-stats.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/formatting.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/widgets.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/mime-types.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/gateways/functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/gateways/paypal-standard.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/gateways/manual.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/discount-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/payments/functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/payments/actions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/payments/class-payment-stats.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/payments/class-payments-query.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/misc-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/download-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/scripts.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/post-types.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/plugin-compatibility.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/emails/functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/emails/template.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/emails/actions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/error-tracking.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/user-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/query-filters.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/tax-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/process-purchase.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/login-register.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/add-ons.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/admin-actions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/admin-notices.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/admin-pages.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/export-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/thickbox.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/upload-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/downloads/dashboard-columns.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/downloads/metabox.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/downloads/contextual-help.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/discounts/contextual-help.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/payments/payments-history.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/reporting/reports.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/reporting/pdf-reports.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/reporting/graphing.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/settings/display-settings.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/class-pods-heartbeat.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/admin/welcome.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/process-download.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/shortcodes.php' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'includes/theme-compatibility.php' );

		/** Check Assets Exist */
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/css/chosen.css' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/css/colorbox.css' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/css/pods-admin.css' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/css/jquery-ui-classic.css' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/css/jquery-ui-fresh.css' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/fonts/padlock.eot' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/fonts/padlock.svg' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/fonts/padlock.ttf' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/fonts/padlock.woff' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderBottomCenter.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderBottomLeft.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderBottomRight.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderMiddleLeft.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderMiddleRight.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderTopCenter.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderTopLeft.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderTopRight.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/border.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/controls.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/loading.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/loading_background.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/colorbox/overlay.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/images/icons/americanexpress.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/images/icons/discover.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/images/icons/iphone.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/images/icons/mastercard.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/images/icons/paypal.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/images/icons/visa.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/css/chosen-sprite.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/pods-badge.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/pods-cpt-2x.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/pods-cpt.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/pods-icon-2x.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/pods-icon.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/pods-logo.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/pods-media.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/loading.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/images/loading.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/media-button.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/images/tick.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/ui-icons_21759b_256x240.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/ui-icons_333333_256x240.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/ui-icons_999999_256x240.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/ui-icons_cc0000_256x240.png' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/images/xit.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'templates/images/xit.gif' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/js/admin-scripts.js' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/js/chosen.jquery.min.js' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/js/pods-ajax.js' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/js/pods-checkout-global.js' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/js/jquery.colorbox-min.js' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/js/jquery.creditCardValidator.js' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/js/jquery.flot.js' );
		$this->assertFileExists( PODS_PLUGIN_DIR . 'assets/js/jquery.validate.min.js' );
	}
}