<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Unit tests covering property bool.
 *
 * @package Papi
 */

class Papi_Property_Bool_Test extends WP_UnitTestCase {

	/**
	 * Setup the test.
	 */

	public function setUp() {
		parent::setUp();

		$this->post_id = $this->factory->post->create();

		$this->property = papi_property( [
			'type'  => 'bool',
			'title' => 'Add url?',
			'slug'  => 'add_url'
		] );
	}

	/**
	 * Tear down test.
	 */

	public function tearDown() {
		parent::tearDown();
		unset( $this->post_id, $this->property );
	}

	/**
	 * Test output to check if property slug exists and the property type value.
	 */

	public function test_output() {
		papi_render_property( $this->property );
		$this->expectOutputRegex( '/name=\"' . papi_get_property_type_key( $this->property->slug ) . '\"' );
		$this->expectOutputRegex( '/data\-property=\"' . $this->property->type . '\"/' );
	}

	/**
	 * Test property options.
	 */

	public function test_property_options() {
		$this->assertEquals( 'bool', $this->property->type );
		$this->assertEquals( 'Add url?', $this->property->title );
		$this->assertEquals( 'papi_add_url', $this->property->slug );
	}

	/**
	 * Test save property value.
	 */

	public function test_save_property_value() {
		$handler = new Papi_Admin_Post_Handler();

		// Create post data.
		$_POST = papi_test_create_property_post_data( [
			'slug'  => $this->property->slug,
			'type'  => $this->property,
			'value' => true
		], $_POST );

		// Save the property using the handler.
		$handler->save_property( $this->post_id );

		// Test get the value with papi_field function.
		$expected = true;
		$actual   = papi_field( $this->post_id, $this->property->slug );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test `update_value` method.
	 */

	public function test_update_value() {
		$property_class = papi_get_property_type( 'bool' );
		$this->assertFalse( $property_class->update_value( 'false', '', 0 ) );
	}

}
