<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_mime
 */
class Tests_Templates extends Pods_UnitTestCase {

	protected $_post;

	public function setUp() {
		parent::setUp();

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

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
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_get_purchase_link() {

		$link = pods_get_purchase_link( array( 'download_id' => $this->_post->ID ) );
		$this->assertInternalType( 'string', $link );
		$this->assertContains( '<form id="pods_purchase_' . $this->_post->ID . '" class="pods_download_purchase_form" method="post">', $link );
		$this->assertContains( '<input type="hidden" name="download_id" value="' . $this->_post->ID . '">', $link );

		// The product we created has variable pricing, so ensure the price options render
		$this->assertContains( '<div class="pods_price_options">', $link );
		$this->assertContains( '<span class="pods_price_option_name">', $link );
	}

	public function test_button_colors() {
		$colors = pods_get_button_colors();
		$this->assertInternalType( 'array', $colors );
		$this->assertarrayHasKey( 'white', $colors );
		$this->assertarrayHasKey( 'gray', $colors );
		$this->assertarrayHasKey( 'blue', $colors );
		$this->assertarrayHasKey( 'red', $colors );
		$this->assertarrayHasKey( 'green', $colors );
		$this->assertarrayHasKey( 'yellow', $colors );
		$this->assertarrayHasKey( 'orange', $colors );
		$this->assertarrayHasKey( 'dark-gray', $colors );
		$this->assertarrayHasKey( 'inherit', $colors );
		$this->assertInternalType( 'array', $colors['white'] );
		$this->assertEquals( 'White', $colors['white']['label'] );
	}

	public function test_button_styles() {
		$styles = pods_get_button_styles();
		$this->assertInternalType( 'array', $styles );
		$this->assertarrayHasKey( 'button', $styles );
		$this->assertarrayHasKey( 'plain', $styles );
		$this->assertEquals( 'Button', $styles['button'] );
		$this->assertEquals( 'Plain Text', $styles['plain'] );
	}

	public function test_locate_template() {
		// Test that a file path is found
		$this->assertInternalType( 'string', pods_locate_template( 'history-purchases.php' ) );
	}

	public function test_get_theme_template_paths() {
		$paths = pods_get_theme_template_paths();
		$this->assertInternalType( 'array', $paths );
		$this->assertarrayHasKey( 1, $paths );
		$this->assertarrayHasKey( 10, $paths );
		$this->assertarrayHasKey( 100, $paths );
		$this->assertInternalType( 'string', $paths[1] );
		$this->assertInternalType( 'string', $paths[10] );
		$this->assertInternalType( 'string', $paths[100] );
	}

	public function test_get_templates_dir_name() {
		$this->assertEquals( 'pods_templates/', pods_get_theme_template_dir_name() );
	}
}