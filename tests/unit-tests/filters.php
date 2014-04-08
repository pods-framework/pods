<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_filters
 */
class Tests_Filters extends Pods_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_the_content() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_before_download_content', $wp_filter['the_content'][10] );
		$this->assertarrayHasKey( 'pods_after_download_content', $wp_filter['the_content'][10] );
		$this->assertarrayHasKey( 'pods_filter_success_page_content', $wp_filter['the_content'][10] );
		$this->assertarrayHasKey( 'pods_microdata_wrapper', $wp_filter['the_content'][10] );
		$this->assertarrayHasKey( 'pods_microdata_title', $wp_filter['the_title'][10] );
	}

	public function test_wp_head() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_version_in_header', $wp_filter['wp_head'][10] );
	}

	public function test_template_redirect() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_disable_jetpack_og_on_checkout', $wp_filter['template_redirect'][10] );
		$this->assertarrayHasKey( 'pods_block_attachments', $wp_filter['template_redirect'][10] );
		$this->assertarrayHasKey( 'pods_process_cart_endpoints', $wp_filter['template_redirect'][100] );
	}

	public function test_init() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_get_actions', $wp_filter['init'][10] );
		$this->assertarrayHasKey( 'pods_post_actions', $wp_filter['init'][10] );
		$this->assertarrayHasKey( 'pods_add_rewrite_endpoints', $wp_filter['init'][10] );
		$this->assertarrayHasKey( 'pods_no_gateway_error', $wp_filter['init'][10] );
		$this->assertarrayHasKey( 'pods_listen_for_paypal_ipn', $wp_filter['init'][10] );
		$this->assertarrayHasKey( 'pods_setup_download_taxonomies', $wp_filter['init'][0] );
		$this->assertarrayHasKey( 'pods_register_post_type_statuses', $wp_filter['init'][10] );
		$this->assertarrayHasKey( 'pods_setup_pods_post_types', $wp_filter['init'][1] );
		$this->assertarrayHasKey( 'pods_process_download', $wp_filter['init'][100] );
	}

	public function test_admin_init() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_register_settings', $wp_filter['admin_init'][10] );
	}

	public function test_dashboard_widget() {
		global $wp_filter;
		$this->markTestIncomplete('This check kills phpunit');
		$this->assertarrayHasKey( 'pods_register_dashboard_widgets', $wp_filter['wp_dashboard_setup'][10] );
	}

	public function test_delete_post() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_remove_download_logs_on_delete', $wp_filter['delete_post'][10] );
	}

	public function test_admin_enqueue_scripts() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_load_admin_scripts', $wp_filter['admin_enqueue_scripts'][100] );
	}

	public function test_upload_mimes() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_allowed_mime_types', $wp_filter['upload_mimes'][10] );
	}

	public function test_widgets_init() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_register_widgets', $wp_filter['widgets_init'][10] );
	}

	public function test_wp_enqueue_scripts() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_load_scripts', $wp_filter['wp_enqueue_scripts'][10] );
		$this->assertarrayHasKey( 'pods_register_styles', $wp_filter['wp_enqueue_scripts'][10] );
	}

	public function test_ajax() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_ajax_remove_from_cart', $wp_filter['wp_ajax_pods_remove_from_cart'][10] );
		$this->assertarrayHasKey( 'pods_ajax_remove_from_cart', $wp_filter['wp_ajax_nopriv_pods_remove_from_cart'][10] );
		$this->assertarrayHasKey( 'pods_ajax_add_to_cart', $wp_filter['wp_ajax_pods_add_to_cart'][10] );
		$this->assertarrayHasKey( 'pods_ajax_add_to_cart', $wp_filter['wp_ajax_nopriv_pods_add_to_cart'][10] );
		$this->assertarrayHasKey( 'pods_ajax_apply_discount', $wp_filter['wp_ajax_pods_apply_discount'][10] );
		$this->assertarrayHasKey( 'pods_ajax_apply_discount', $wp_filter['wp_ajax_nopriv_pods_apply_discount'][10] );
		$this->assertarrayHasKey( 'pods_load_checkout_login_fields', $wp_filter['wp_ajax_nopriv_checkout_login'][10] );
		$this->assertarrayHasKey( 'pods_load_checkout_register_fields', $wp_filter['wp_ajax_nopriv_checkout_register'][10] );
		$this->assertarrayHasKey( 'pods_ajax_get_download_title', $wp_filter['wp_ajax_pods_get_download_title'][10] );
		$this->assertarrayHasKey( 'pods_ajax_get_download_title', $wp_filter['wp_ajax_nopriv_pods_get_download_title'][10] );
		$this->assertarrayHasKey( 'pods_check_for_download_price_variations', $wp_filter['wp_ajax_pods_check_for_download_price_variations'][10] );
		$this->assertarrayHasKey( 'pods_load_ajax_gateway', $wp_filter['wp_ajax_pods_load_gateway'][10] );
		$this->assertarrayHasKey( 'pods_load_ajax_gateway', $wp_filter['wp_ajax_nopriv_pods_load_gateway'][10] );
		$this->assertarrayHasKey( 'pods_print_errors', $wp_filter['pods_ajax_checkout_errors'][10] );
		$this->assertarrayHasKey( 'pods_process_purchase_form', $wp_filter['wp_ajax_pods_process_checkout'][10] );
		$this->assertarrayHasKey( 'pods_process_purchase_form', $wp_filter['wp_ajax_nopriv_pods_process_checkout'][10] );
	}

	public function test_pods_after_download_content() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_append_purchase_link', $wp_filter['pods_after_download_content'][10] );
		$this->assertarrayHasKey( 'pods_show_added_to_cart_messages', $wp_filter['pods_after_download_content'][10] );
	}

	public function test_pods_purchase_link_top() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_purchase_variable_pricing', $wp_filter['pods_purchase_link_top'][10] );
	}

	public function test_pods_downloads_excerpt() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_downloads_default_excerpt', $wp_filter['pods_downloads_excerpt'][10] );
	}

	public function test_pods_downloads_content() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_downloads_default_content', $wp_filter['pods_downloads_content'][10] );
	}

	public function test_pods_purchase_form() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_show_purchase_form', $wp_filter['pods_purchase_form'][10] );
	}

	public function test_pods_purchase_form_after_user_info() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_user_info_fields', $wp_filter['pods_purchase_form_after_user_info'][10] );
	}

	public function test_pods_cc_form() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_get_cc_form', $wp_filter['pods_cc_form'][10] );
	}

	public function test_pods_after_cc_fields() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_default_cc_address_fields', $wp_filter['pods_after_cc_fields'][10] );
	}

	public function test_pods_purchase_form_register_fields() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_get_register_fields', $wp_filter['pods_purchase_form_register_fields'][10] );
	}

	public function test_pods_purchase_form_login_fields() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_get_login_fields', $wp_filter['pods_purchase_form_login_fields'][10] );
	}

	public function test_pods_payment_mode_select() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_payment_mode_select', $wp_filter['pods_payment_mode_select'][10] );
	}

	public function test_pods_purchase_form_before_cc_form() {
		global $wp_filter;
		// No actions connected to pods_purchase_form_before_cc_form by default
		$this->assertTrue( true );
		//$this->assertarrayHasKey( 'pods_discount_field', $wp_filter['pods_purchase_form_before_cc_form'][10] );
	}

	public function test_pods_purchase_form_after_cc_form() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_checkout_tax_fields', $wp_filter['pods_purchase_form_after_cc_form'][999] );
		$this->assertarrayHasKey( 'pods_checkout_submit', $wp_filter['pods_purchase_form_after_cc_form'][9999] );
	}

	public function test_pods_purchase_form_before_submit() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_print_errors', $wp_filter['pods_purchase_form_before_submit'][10] );
		$this->assertarrayHasKey( 'pods_checkout_final_total', $wp_filter['pods_purchase_form_before_submit'][999] );
	}

	public function test_pods_checkout_form_top() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_discount_field', $wp_filter['pods_checkout_form_top'][-1] );
		$this->assertarrayHasKey( 'pods_show_payment_icons', $wp_filter['pods_checkout_form_top'][10] );
		$this->assertarrayHasKey( 'pods_agree_to_terms_js', $wp_filter['pods_checkout_form_top'][10] );
	}

	public function test_pods_empty_cart() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_empty_checkout_cart', $wp_filter['pods_cart_empty'][10] );
	}

	public function test_pods_add_to_cart() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_process_add_to_cart', $wp_filter['pods_add_to_cart'][10] );
	}

	public function test_pods_remove() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_process_remove_from_cart', $wp_filter['pods_remove'][10] );
	}

	public function test_pods_purchase_collection() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_process_collection_purchase', $wp_filter['pods_purchase_collection'][10] );
	}

	public function test_pods_format_amount_decimals() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_currency_decimal_filter', $wp_filter['pods_format_amount_decimals'][10] );
	}

	public function test_pods_paypal_cc_form() {
		global $wp_filter;
		$this->assertarrayHasKey( '__return_false', $wp_filter['pods_paypal_cc_form'][10] );
	}

	public function test_pods_gateway_paypal() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_process_paypal_purchase', $wp_filter['pods_gateway_paypal'][10] );
	}

	public function test_pods_verify_paypal_ipn() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_process_paypal_ipn', $wp_filter['pods_verify_paypal_ipn'][10] );
	}

	public function test_pods_paypal_web_accept() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_process_paypal_web_accept_and_cart', $wp_filter['pods_paypal_web_accept'][10] );
	}

	public function test_pods_manual_cc_form() {
		global $wp_filter;
		$this->assertarrayHasKey( '__return_false', $wp_filter['pods_manual_cc_form'][10] );
	}

	public function test_pods_gateway_manual() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_manual_payment', $wp_filter['pods_gateway_manual'][10] );
	}

	public function test_pods_remove_cart_discount() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_remove_cart_discount', $wp_filter['pods_remove_cart_discount'][10] );
	}

	public function test_comments_clauses() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_hide_payment_notes', $wp_filter['comments_clauses'][10] );
	}

	public function test_pods_update_payment_status() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_complete_purchase', $wp_filter['pods_update_payment_status'][100] );
		$this->assertarrayHasKey( 'pods_record_status_change', $wp_filter['pods_update_payment_status'][100] );
		$this->assertarrayHasKey( 'pods_clear_user_history_cache', $wp_filter['pods_update_payment_status'][10] );
	}

	public function test_pods_edit_payment() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_update_edited_purchase', $wp_filter['pods_edit_payment'][10] );
	}

	public function test_pods_delete_payment() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_trigger_purchase_delete', $wp_filter['pods_delete_payment'][10] );
	}

	public function test_pods_upgrade_payments() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_update_old_payments_with_totals', $wp_filter['pods_upgrade_payments'][10] );
	}

	public function test_pods_cleanup_file_symlinks() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_cleanup_file_symlinks', $wp_filter['pods_cleanup_file_symlinks'][10] );
	}

	public function test_pods_download_price() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_format_amount', $wp_filter['pods_download_price'][10] );
		$this->assertarrayHasKey( 'pods_currency_filter', $wp_filter['pods_download_price'][20] );
	}

	public function test_admin_head() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_admin_downloads_icon', $wp_filter['admin_head'][10] );
	}

	public function test_enter_title_here() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_change_default_title', $wp_filter['enter_title_here'][10] );
	}

	public function test_post_updated_messages() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_updated_messages', $wp_filter['post_updated_messages'][10] );
	}

	public function test_load_edit_php() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_remove_post_types_order', $wp_filter['load-edit.php'][10] );
	}

	public function test_pods_settings_misc() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_append_no_cache_param', $wp_filter['pods_settings_misc'][-1] );
	}

	public function test_pods_admin_sale_notice() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_admin_email_notice', $wp_filter['pods_admin_sale_notice'][10] );
	}

	public function test_pods_purchase_receipt() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_email_default_formatting', $wp_filter['pods_purchase_receipt'][10] );
		$this->assertarrayHasKey( 'pods_apply_email_template', $wp_filter['pods_purchase_receipt'][20] );
	}

	public function test_pods_email_settings() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_email_template_preview', $wp_filter['pods_email_settings'][10] );
	}

	public function test_pods_email_template_default() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_default_email_template', $wp_filter['pods_email_template_default'][10] );
	}

	public function test_pods_purchase_receipt_default() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_default_email_styling', $wp_filter['pods_purchase_receipt_default'][10] );
	}

	public function test_pods_view_receipt() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_render_receipt_in_browser', $wp_filter['pods_view_receipt'][10] );
	}

	public function test_pods_email_links() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_resend_purchase_receipt', $wp_filter['pods_email_links'][10] );
	}

	public function test_pods_send_test_email() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_send_test_email', $wp_filter['pods_send_test_email'][10] );
	}

	public function test_pods_purchase() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_process_purchase_form', $wp_filter['pods_purchase'][10] );
	}

	public function test_pods_user_login() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_process_login_form', $wp_filter['pods_user_login'][10] );
	}

	public function test_pods_edit_user_profile() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_process_profile_editor_updates', $wp_filter['pods_edit_user_profile'][10] );
	}

	public function test_post_class() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_responsive_download_post_class', $wp_filter['post_class'][999] );
	}

}
