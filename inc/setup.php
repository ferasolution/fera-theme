<?php
/**
 * Theme setup: supports, post types, first-run content, template routing.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function () {
		load_theme_textdomain( 'fera-theme', FERA_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_post_type_support( 'page', 'excerpt' );
		register_nav_menus( array( 'primary' => __( 'Primary (reserved)', 'fera-theme' ) ) );
	}
);

/** Services (/services/<slug>/) and Enquiries (private lead inbox). */
add_action(
	'init',
	static function () {
		register_post_type(
			'fera_service',
			array(
				'labels'       => array(
					'name'          => __( 'Services', 'fera-theme' ),
					'singular_name' => __( 'Service', 'fera-theme' ),
					'add_new_item'  => __( 'Add new service', 'fera-theme' ),
					'edit_item'     => __( 'Edit service', 'fera-theme' ),
				),
				'public'       => true,
				'show_in_rest' => true,
				'has_archive'  => false,
				'menu_icon'    => 'dashicons-shield',
				'menu_position' => 21,
				'rewrite'      => array( 'slug' => 'services', 'with_front' => false ),
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			)
		);
		register_post_type(
			'fera_lead',
			array(
				'labels'          => array(
					'name'          => __( 'Enquiries', 'fera-theme' ),
					'singular_name' => __( 'Enquiry', 'fera-theme' ),
					'menu_name'     => __( 'Enquiries', 'fera-theme' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-email-alt',
				'menu_position'   => 22,
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
				'supports'        => array( 'title', 'editor' ),
			)
		);

		if ( get_option( 'fera_theme_seeded' ) !== FERA_THEME_VERSION ) {
			fera_seed_content();
			flush_rewrite_rules( false );
			update_option( 'fera_theme_seeded', FERA_THEME_VERSION, false );
		}
	}
);

add_action(
	'after_switch_theme',
	static function () {
		delete_option( 'fera_theme_seeded' ); // re-run seeding on next init.
	}
);

/**
 * Create the pages/services the design needs. Never overwrites existing content.
 */
function fera_seed_content() {
	$descs = array(
		'about'    => 'Meet Fera Solution LLC, a USA-based digital agency in Herndon, Virginia serving clients worldwide: engineers, designers, writers, marketers and security specialists working as one accountable team.',
		'services' => 'Eight digital services under one roof: cybersecurity, website and software development, digital marketing, graphic design, content writing, e-commerce and social media management.',
		'work'     => 'Selected work from Fera Solution: learning management, hospital management and community portal platforms engineered and run in-house.',
		'contact'  => 'Start a project with Fera Solution — USA office in Herndon, Virginia, serving clients worldwide. Call 337-252-4040 or email sales@ferasolution.com.',
	);
	foreach ( fera_core_pages() as $slug => $title ) {
		if ( ! fera_get_page( $slug, array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ) ) ) {
			wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_excerpt' => $descs[ $slug ],
					'post_content' => '',
				)
			);
		}
	}
	$order = 0;
	foreach ( fera_services() as $s ) {
		++$order;
		$existing = fera_get_service_post( $s['slug'], array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ) );
		if ( $existing ) {
			if ( 'trash' === $existing->post_status ) {
				wp_untrash_post( $existing->ID );
				wp_update_post( array( 'ID' => $existing->ID, 'post_status' => 'publish' ) );
			}
			continue;
		}
		$trashed = get_posts( array( 'post_type' => 'fera_service', 'post_status' => 'trash', 'name' => $s['slug'] . '__trashed', 'posts_per_page' => 1 ) );
		if ( $trashed ) {
			wp_untrash_post( $trashed[0]->ID );
			wp_update_post( array( 'ID' => $trashed[0]->ID, 'post_status' => 'publish', 'post_name' => $s['slug'] ) );
			continue;
		}
		wp_insert_post(
			array(
				'post_type'    => 'fera_service',
				'post_status'  => 'publish',
				'post_title'   => $s['name'],
				'post_name'    => $s['slug'],
				'post_excerpt' => $s['lead'],
				'post_content' => '',
				'menu_order'   => $order,
			)
		);
	}
	// A Blog page for posts when the site has a static front page but no posts page yet.
	if ( 'page' === get_option( 'show_on_front' ) && ! (int) get_option( 'page_for_posts' ) ) {
		$blog    = fera_get_page( 'blog', array( 'publish', 'draft', 'private' ) );
		$blog_id = $blog ? $blog->ID : wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => 'Blog',
				'post_name'    => 'blog',
				'post_excerpt' => 'Insights on cybersecurity, web development, software and digital marketing from the Fera Solution team.',
			)
		);
		if ( $blog_id && ! is_wp_error( $blog_id ) ) {
			update_option( 'page_for_posts', $blog_id );
		}
	}
	// Static front page + blog page, only if the site still shows latest posts on the front.
	if ( 'posts' === get_option( 'show_on_front' ) ) {
		$home = get_page_by_path( 'home' );
		$home_id = $home ? $home->ID : wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Home',
				'post_name'   => 'home',
			)
		);
		$blog = get_page_by_path( 'blog' );
		$blog_id = $blog ? $blog->ID : wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Blog',
				'post_name'   => 'blog',
			)
		);
		if ( $home_id && $blog_id ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_id );
			update_option( 'page_for_posts', $blog_id );
		}
	}
}

/**
 * Route design pages to the theme templates, even if a page builder
 * (e.g. Elementor) assigned its own template to that page earlier.
 */
add_filter(
	'template_include',
	static function ( $template ) {
		if ( is_front_page() && ! is_home() ) {
			return FERA_DIR . '/front-page.php';
		}
		if ( is_page() ) {
			$slug = get_post_field( 'post_name', get_queried_object_id() );
			if ( isset( fera_core_pages()[ $slug ] ) ) {
				return FERA_DIR . '/page-' . $slug . '.php';
			}
		}
		if ( is_singular( 'fera_service' ) ) {
			return FERA_DIR . '/single-fera_service.php';
		}
		return $template;
	},
	99
);

/** Body classes for the design. */
add_filter(
	'body_class',
	static function ( $classes ) {
		$classes[] = 'fera';
		$classes[] = 'route-' . fera_route();
		return $classes;
	}
);
