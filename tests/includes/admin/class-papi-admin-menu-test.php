<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Unit tests covering `Papi_Admin_Menu` class.
 *
 * @package Papi
 */
class Papi_Admin_Menu_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$_GET  = [];
		$_POST = [];
		$this->menu = new Papi_Admin_Menu();

		tests_add_filter( 'papi/settings/directories', function () {
			return [1,  PAPI_FIXTURE_DIR . '/page-types'];
		} );
	}

	public function tearDown() {
		parent::tearDown();
		unset( $_GET, $_POST, $this->menu );
	}

	public function test_admin_bar_menu() {
		global $wp_post_types;
		$post_type = 'page';
		$labels = $wp_post_types[$post_type]->labels;

		$_GET['page_type'] = 1;
		$this->menu->admin_bar_menu();
		$this->assertEquals( 'Add New Page', $labels->add_new_item );
		$this->assertEquals( 'Edit Page', $labels->edit_item );
		$this->assertEquals( 'View Page', $labels->view_item );

		$_GET['page_type'] = 'faq-page-type';
		$this->menu->admin_bar_menu();
		$this->assertEquals( 'Add New Page', $labels->add_new_item );
		$this->assertEquals( 'Edit Page', $labels->edit_item );
		$this->assertEquals( 'View Page', $labels->view_item );

		$_GET['post_type'] = $post_type;
		$this->menu->admin_bar_menu();
		$this->assertEquals( 'Add New FAQ page', $labels->add_new_item );
		$this->assertEquals( 'Edit FAQ page', $labels->edit_item );
		$this->assertEquals( 'View FAQ page', $labels->view_item );
	}

	public function test_admin_bar_menu_2() {
		global $wp_post_types;
		$post_type = 'post';
		$labels = $wp_post_types[$post_type]->labels;
		$_SERVER['REQUEST_URI'] = 'http://site.com/?page=papi/options/header-option-type';

		$_GET['post_type'] = $post_type;
		$this->menu->admin_bar_menu();
		$this->assertEquals( 'Add New Post', $labels->add_new_item );
		$this->assertEquals( 'Edit Post', $labels->edit_item );
		$this->assertEquals( 'View Post', $labels->view_item );
		$_SERVER['REQUEST_URI'] = '';
	}

	public function test_page_items_menu() {
		$this->assertNull( $this->menu->page_items_menu() );
	}

	public function test_post_types_menu() {
		global $submenu;
		$submenu = [];
		$submenu['edit.php'] = [
			5  => [
				'All Posts',
				'edit_posts',
				'edit.php'
			],
			10 => [
				'Add New',
				'edit_posts',
				'post-new.php'
			]
		];
		$submenu['edit.php?post_type=page'] = [
			5  => [
				'All Pages',
				'edit_pages',
				'edit.php?post_type=page'
			],
			10 => [
				'Add New',
				'edit_pages',
				'post-new.php?post_type=page'
			]
		];

		$this->assertNull( $this->menu->post_types_menu() );
		$this->assertEquals( 'edit.php?page=papi-add-new-page,post', $submenu['edit.php'][10][2] );
		$this->assertEquals( 'edit.php?post_type=page&page=papi-add-new-page,page', $submenu['edit.php?post_type=page'][10][2] );
	}

	public function test_post_types_menu_2() {
		global $submenu;
		$submenu = [];
		$submenu['edit.php'] = [
			5  => [
				'All Posts',
				'edit_posts',
				'edit.php'
			],
			10 => [
				'Add New',
				'edit_posts',
				'post-new.php'
			]
		];
		$submenu['edit.php?post_type=page'] = [
			5  => [
				'All Pages',
				'edit_pages',
				'edit.php?post_type=page'
			],
			10 => [
				'Add New',
				'edit_pages',
				'post-new.php?post_type=page'
			]
		];
		$submenu['edit.php?post_type=book'] = [
			5  => [
				'All Pages',
				'edit_pages',
				'edit.php?post_type=book'
			],
			10 => [
				'Add New',
				'edit_pages',
				'post-new.php?post_type=book'
			]
		];

		tests_add_filter( 'papi/settings/only_page_type_post', function () {
			return 'post-page-type';
		} );

		papi_test_register_book_post_type();

		$this->assertNull( $this->menu->post_types_menu() );
		$this->assertEquals( 'post-new.php?page_type=post-page-type&post_type=post', $submenu['edit.php'][10][2] );
		$this->assertEquals( 'edit.php?post_type=page&page=papi-add-new-page,page', $submenu['edit.php?post_type=page'][10][2] );
		$this->assertEquals( 'post-new.php?page_type=book-page-type&post_type=book', $submenu['edit.php?post_type=book'][10][2] );
	}

	public function test_render_view() {
		$_GET['page'] = '';
		$this->menu->render_view();
		$this->expectOutputRegex( '/\<h1\>Papi\s\-\s404\<\/h1\>/' );

		$_GET['page'] = 'papi-add-new-page,page';
		$this->menu->render_view();
		$this->expectOutputRegex( '/Add New Page/' );
	}

	public function test_setup_actions_admin() {
		global $current_screen;

		$this->assertNull( $current_screen );

		$current_screen = WP_Screen::get( 'admin_init' );

		$menu = new Papi_Admin_Menu();

		$this->assertEquals( 10, has_action( 'admin_init', [$menu, 'admin_bar_menu'] ) );
		$this->assertEquals( 10, has_action( 'admin_menu', [$menu, 'page_items_menu'] ) );
		$this->assertEquals( 10, has_action( 'admin_menu', [$menu, 'post_types_menu'] ) );

		$current_screen = null;
	}

	public function test_setup_actions() {
		$this->assertEquals( 10, has_action( 'admin_bar_menu', [$this->menu, 'admin_bar_menu'] ) );
	}
}
