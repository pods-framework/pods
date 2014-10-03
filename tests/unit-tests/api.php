<?php
namespace Pods_Unit_Tests;
use \Pods_Roles;

/**
 * @group pods_api
 */

class Tests_API extends Pods_UnitTestCase {
	protected $_rewrite = null;

	protected $query = null;

	protected $_post = null;

	protected $_api_output = null;

	protected $_api_output_sales = null;

	protected $_user_id = null;

	public function setUp() {
		parent::setUp();

		global $wp_rewrite, $wp_query;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		$roles = new Pods_Roles;
		$roles->add_roles();
		$roles->add_caps();

		Pods()->api->add_endpoint( $wp_rewrite );

		$this->_rewrite = $wp_rewrite;
		$this->_query = $wp_query;

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$this->_user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->_user_id );

		$_variable_pricing = array(
			array(
				'name' => 'Simple',
				'amount' => 20
			),
			array(
				'name' => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name' => 'File 1',
				'file' => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name' => 'File 2',
				'file' => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'pods_price' => '0.00',
			'_variable_pricing' => 1,
			'_pods_price_options_mode' => 'on',
			'pods_variable_prices' => array_values( $_variable_pricing ),
			'pods_download_files' => array_values( $_download_files ),
			'_pods_download_limit' => 20,
			'_pods_hide_purchase_link' => 1,
			'pods_product_notes' => 'Purchase Notes',
			'_pods_product_type' => 'default',
			'_pods_download_earnings' => 129.43,
			'_pods_download_sales' => 59,
			'_pods_download_limit_override_1' => 1
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );


		$user = get_userdata(1);

		$user_info = array(
			'id' => $user->ID,
			'email' => $user->user_email,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'discount' => 'none'
		);

		$download_details = array(
			array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 1
				)
			)
		);

		$price = '100.00';

		$total = 0;

		$prices = get_post_meta( $download_details[0]['id'], 'pods_variable_prices', true );
		$item_price = $prices[1]['amount'];

		$total += $item_price;

		$cart_details = array(
			array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'id' => $this->_post->ID,
					'options' => array(
						'price_id' => 1
					)
				),
				'price' =>  100,
				'quantity' => 1
			)
		);

		$purchase_data = array(
			'price' => number_format( (float) $total, 2 ),
			'date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'publish'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';

		$payment_id = pods_insert_payment( $purchase_data );

		$this->_api_output = Pods()->api->get_products();
		$this->_api_output_sales = Pods()->api->get_recent_sales();

		global $wp_query;
		$wp_query->query_vars['format'] = 'override';
	}

	public function tearDown() {
		parent::tearDown();
		remove_action( 'pods_api_output_override_xml', array( $this, 'override_api_xml_format' ) );
	}

	public function test_endpoints() {
		$this->assertEquals('pods-api', $this->_rewrite->endpoints[0][1]);
	}

	public function test_query_vars() {
		global $wp_filter;

		foreach ( $wp_filter['query_vars'][10] as $arr ) :

			if ( 'query_vars' == $arr['function'][1] ) {
				$this->assertTrue( true );
			}

		endforeach;

		$out = Pods()->api->query_vars();
		$this->assertEquals( 'token', $out[0] );
		$this->assertEquals( 'key', $out[1] );
		$this->assertEquals( 'query', $out[2] );
		$this->assertEquals( 'type', $out[3] );
		$this->assertEquals( 'product', $out[4] );
		$this->assertEquals( 'number', $out[5] );
		$this->assertEquals( 'date', $out[6] );
		$this->assertEquals( 'startdate', $out[7] );
		$this->assertEquals( 'enddate', $out[8] );
		$this->assertEquals( 'customer', $out[9] );
		$this->assertEquals( 'discount', $out[10] );
		$this->assertEquals( 'format', $out[11] );
	}

	public function test_get_products() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'id', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'slug', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'title', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'create_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'modified_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'status', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'link', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'content', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'thumbnail', $out['products'][0]['info'] );

		$this->assertEquals( $this->_post->ID, $out['products'][0]['info']['id'] );
		$this->assertEquals( 'test-download', $out['products'][0]['info']['slug'] );
		$this->assertEquals( 'Test Download', $out['products'][0]['info']['title'] );
		$this->assertEquals( 'publish', $out['products'][0]['info']['status'] );
		$this->assertEquals( 'Post content 1', $out['products'][0]['info']['content'] );
		$this->assertEquals( '', $out['products'][0]['info']['thumbnail'] );
	}

	public function test_get_product_stats() {
		$out = $this->_api_output;
		// This one fails and haven't figured out why
		$this->markTestIncomplete( 'This test needs to be fixed. The stats key doesn\'t exist due to not being able to correctly check the user\'s permissions' );
		$this->assertArrayHasKey( 'stats', $out['products'][0] );
		$this->assertArrayHasKey( 'total', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'monthly_average', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['monthly_average'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['monthly_average'] );

		$this->assertEquals( '59', $out['products'][0]['stats']['total']['sales'] );
		$this->assertEquals( '129.43', $out['products'][0]['stats']['total']['earnings'] );
		$this->assertEquals( '59', $out['products'][0]['stats']['monthly_average']['sales'] );
		$this->assertEquals( '129.43', $out['products'][0]['stats']['monthly_average']['earnings'] );
	}

	public function test_get_products_pricing() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'pricing', $out['products'][0] );
		$this->assertArrayHasKey( 'simple', $out['products'][0]['pricing'] );
		$this->assertArrayHasKey( 'advanced', $out['products'][0]['pricing'] );

		$this->assertEquals( '20', $out['products'][0]['pricing']['simple'] );
		$this->assertEquals( '100', $out['products'][0]['pricing']['advanced'] );
	}

	public function test_get_products_files() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'files', $out['products'][0]) ;

		foreach ( $out['products'][0]['files'] as $file ) {
			$this->assertArrayHasKey( 'name', $file );
			$this->assertArrayHasKey( 'file', $file );
			$this->assertArrayHasKey( 'condition', $file );
		}

		$this->assertEquals( 'File 1', $out['products'][0]['files'][0]['name'] );
		$this->assertEquals( 'http://localhost/file1.jpg', $out['products'][0]['files'][0]['file'] );
		$this->assertEquals( 0, $out['products'][0]['files'][0]['condition'] );
		$this->assertEquals( 'File 2', $out['products'][0]['files'][1]['name'] );
		$this->assertEquals( 'http://localhost/file2.jpg', $out['products'][0]['files'][1]['file'] );
		$this->assertEquals( 'all', $out['products'][0]['files'][1]['condition'] );
	}


	public function test_get_products_notes() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'notes', $out['products'][0] );
		$this->assertEquals( 'Purchase Notes', $out['products'][0]['notes'] );
	}

	public function test_get_recent_sales() {
		$out = $this->_api_output_sales;
		$this->assertArrayHasKey( 'sales', $out );
		$this->assertArrayHasKey( 'ID', $out['sales'][0] );
		$this->assertArrayHasKey( 'key', $out['sales'][0] );
		$this->assertArrayHasKey( 'subtotal', $out['sales'][0] );
		$this->assertArrayHasKey( 'tax', $out['sales'][0] );
		$this->assertArrayHasKey( 'fees', $out['sales'][0] );
		$this->assertArrayHasKey( 'total', $out['sales'][0] );
		$this->assertArrayHasKey( 'gateway', $out['sales'][0] );
		$this->assertArrayHasKey( 'email', $out['sales'][0] );
		$this->assertArrayHasKey( 'date', $out['sales'][0] );
		$this->assertArrayHasKey( 'products', $out['sales'][0] );
		$this->assertArrayHasKey( 'name', $out['sales'][0]['products'][0] );
		$this->assertArrayHasKey( 'price', $out['sales'][0]['products'][0] );
		$this->assertArrayHasKey( 'price_name', $out['sales'][0]['products'][0] );

		$this->assertEquals( 100.00, $out['sales'][0]['subtotal'] );
		$this->assertEquals( 0, $out['sales'][0]['tax'] );
		$this->assertEquals( 100.00, $out['sales'][0]['total'] );
		$this->assertEquals( '', $out['sales'][0]['gateway'] );
		$this->assertEquals( 'admin@example.org', $out['sales'][0]['email'] );
		$this->assertEquals( 'Test Download', $out['sales'][0]['products'][0]['name'] );
		$this->assertEquals( 100, $out['sales'][0]['products'][0]['price'] );
		$this->assertEquals( 'Advanced', $out['sales'][0]['products'][0]['price_name'] );
	}

	public function test_update_key() {
		$_POST['pods_set_api_key'] = 1;
		Pods()->api->update_key( $this->_user_id );
		$this->assertNotEmpty( get_user_meta( $this->_user_id, 'pods_user_public_key', true ) );
		$this->assertNotEmpty( get_user_meta( $this->_user_id, 'pods_user_secret_key', true ) );
	}

	public function test_get_user() {
		$_POST['pods_set_api_key'] = 1;
		Pods()->api->update_key( $this->_user_id );
		$this->assertEquals( $this->_user_id, Pods()->api->get_user( get_user_meta( $this->_user_id, 'pods_user_public_key', true ) ) );
	}

	public function test_get_customers() {
		$out = Pods()->api->get_customers();

		$this->assertArrayHasKey( 'customers', $out );
		$this->assertArrayHasKey( 'info', $out['customers'][0] );
		$this->assertArrayHasKey( 'id', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'username', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'display_name', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'first_name', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'last_name', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'email', $out['customers'][0]['info'] );
		$this->assertArrayHasKey( 'stats', $out['customers'][0] );
		$this->assertArrayHasKey( 'total_purchases', $out['customers'][0]['stats'] );
		$this->assertArrayHasKey( 'total_spent', $out['customers'][0]['stats'] );
		$this->assertArrayHasKey( 'total_downloads', $out['customers'][0]['stats'] );

		$this->assertEquals( 1, $out['customers'][0]['info']['id'] );
		$this->assertEquals( 'admin', $out['customers'][0]['info']['username'] );
		$this->assertEquals( '', $out['customers'][0]['info']['first_name'] );
		$this->assertEquals( '', $out['customers'][0]['info']['last_name'] );
		$this->assertEquals( 'admin@example.org', $out['customers'][0]['info']['email'] );
		$this->assertEquals( 1, $out['customers'][0]['stats']['total_purchases'] );
		$this->assertEquals( 100.0, $out['customers'][0]['stats']['total_spent'] );
		$this->assertEquals( 0, $out['customers'][0]['stats']['total_downloads'] );
	}

	public function test_missing_auth() {
		$this->markTestIncomplete('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		//Pods()->api->missing_auth();
		//$out = Pods()->api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'You must specify both a token and API key!', $out['error'] );

	}

	public function test_invalid_auth() {
		$this->markTestIncomplete('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		//Pods()->api->invalid_auth();
		//$out = Pods()->api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'Your request could not be authenticated!', $out['error'] );
	}

	public function test_invalid_key() {
		$this->markTestIncomplete('Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error)');
		//$out = Pods()->api->invalid_key();
		//$out = Pods()->api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'Invalid API key!', $out['error'] );
	}

	public function test_process_query() {
		global $wp_query;

		$_POST['pods_set_api_key'] = 1;

		Pods()->api->update_key( $this->_user_id );

		$wp_query->query_vars['pods-api'] = 'products';
		$wp_query->query_vars['key'] = get_user_meta( $this->_user_id, 'pods_user_public_key', true );
		$wp_query->query_vars['token'] = hash( 'md5', get_user_meta( $this->_user_id, 'pods_user_secret_key', true ) . get_user_meta( $this->_user_id, 'pods_user_public_key', true ) );

		Pods()->api->process_query();

		$out = Pods()->api->get_output();

		$this->assertArrayHasKey( 'info', $out['products'][0] );
		$this->assertArrayHasKey( 'id', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'slug', $out['products'][0]['info'] );
		$this->assertEquals( 'test-download', $out['products'][0]['info']['slug'] );
		$this->assertArrayHasKey( 'title', $out['products'][0]['info'] );
		$this->assertEquals( 'Test Download', $out['products'][0]['info']['title'] );
		$this->assertArrayHasKey( 'create_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'modified_date', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'status', $out['products'][0]['info'] );
		$this->assertEquals( 'publish', $out['products'][0]['info']['status'] );
		$this->assertArrayHasKey( 'link', $out['products'][0]['info'] );
		$this->assertArrayHasKey( 'content', $out['products'][0]['info'] );
		$this->assertEquals( 'Post content 1', $out['products'][0]['info']['content'] );
		$this->assertArrayHasKey( 'thumbnail', $out['products'][0]['info'] );

		$this->markTestIncomplete( 'This test needs to be fixed. The stats key doesn\'t exist due to not being able to correctly check the user\'s permissions' );
		$this->assertArrayHasKey( 'stats', $out['products'][0] );
		$this->assertArrayHasKey( 'total', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['total'] );
		$this->assertEquals( 59, $out['products'][0]['stats']['total']['sales'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['total'] );
		$this->assertEquals( 129.43, $out['products'][0]['stats']['total']['earnings'] );
		$this->assertArrayHasKey( 'monthly_average', $out['products'][0]['stats'] );
		$this->assertArrayHasKey( 'sales', $out['products'][0]['stats']['monthly_average'] );
		$this->assertEquals( 59, $out['products'][0]['stats']['monthly_average']['sales'] );
		$this->assertArrayHasKey( 'earnings', $out['products'][0]['stats']['monthly_average'] );
		$this->assertEquals( 129.43, $out['products'][0]['stats']['monthly_average']['earnings'] );

		$this->assertArrayHasKey( 'pricing', $out['products'][0] );
		$this->assertArrayHasKey( 'simple', $out['products'][0]['pricing'] );
		$this->assertEquals( 20, $out['products'][0]['pricing']['simple'] );
		$this->assertArrayHasKey( 'advanced', $out['products'][0]['pricing'] );
		$this->assertEquals( 100, $out['products'][0]['pricing']['advanced'] );

		$this->assertArrayHasKey( 'files', $out['products'][0] );
		$this->assertArrayHasKey( 'name', $out['products'][0]['files'][0] );
		$this->assertArrayHasKey( 'file', $out['products'][0]['files'][0] );
		$this->assertArrayHasKey( 'condition', $out['products'][0]['files'][0] );
		$this->assertArrayHasKey( 'name', $out['products'][0]['files'][1] );
		$this->assertArrayHasKey( 'file', $out['products'][0]['files'][1] );
		$this->assertArrayHasKey( 'condition', $out['products'][0]['files'][1] );
		$this->assertEquals( 'File 1', $out['products'][0]['files'][0]['name'] );
		$this->assertEquals( 'http://localhost/file1.jpg', $out['products'][0]['files'][0]['file'] );
		$this->assertEquals( 0, $out['products'][0]['files'][0]['condition'] );
		$this->assertEquals( 'File 2', $out['products'][0]['files'][1]['name'] );
		$this->assertEquals( 'http://localhost/file2.jpg', $out['products'][0]['files'][1]['file'] );
		$this->assertEquals( 'all', $out['products'][0]['files'][1]['condition'] );

		$this->assertArrayHasKey( 'notes', $out['products'][0] );
		$this->assertEquals( 'Purchase Notes', $out['products'][0]['notes'] );
	}

}

