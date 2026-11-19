<?php
/**
 * Front-end assets and performance clean-up.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

/** Cache-busting version for a theme file. */
function fera_ver( $rel ) {
	$f = FERA_DIR . '/' . $rel;
	return file_exists( $f ) ? (string) filemtime( $f ) : FERA_THEME_VERSION;
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'fera-main', FERA_URI . '/assets/css/main.css', array(), fera_ver( 'assets/css/main.css' ) );

		$src = FERA_URI . '/assets/js/app.js?ver=' . fera_ver( 'assets/js/app.js' );
		if ( function_exists( 'wp_enqueue_script_module' ) ) {
			wp_enqueue_script_module( 'fera-app', $src, array(), null );
		} else {
			add_action(
				'wp_footer',
				static function () use ( $src ) {
					printf( '<script type="module" src="%s"></script>' . "\n", esc_url( $src ) );
				},
				20
			);
		}

		// Design pages don't use block styles; keep them for normal posts/pages.
		if ( fera_is_design_view() ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'classic-theme-styles' );
		}
	},
	20
);

/** Early head output: config, first-paint state, preloads, icons. */
add_action(
	'wp_head',
	static function () {
		$urls = array( 'home' => home_url( '/' ) );
		foreach ( array_keys( fera_core_pages() ) as $slug ) {
			$urls[ $slug ] = fera_page_url( $slug );
		}
		$svc = array();
		foreach ( fera_services() as $s ) {
			$svc[ $s['slug'] ] = fera_service_url( $s['slug'] );
		}
		$config = array(
			'home' => home_url( '/' ),
			'urls' => $urls,
			'svc'  => $svc,
			'rest' => esc_url_raw( rest_url() ),
			'mark' => FERA_URI . '/assets/img/markd-560.webp',
		);
		?>
<meta name="theme-color" content="#070607">
<meta name="color-scheme" content="dark">
<meta name="format-detection" content="telephone=no">
<script>
window.FERA=<?php echo wp_json_encode( $config ); ?>;
(function(d){try{if(sessionStorage.getItem('fera-v'))d.classList.add('fera-quick');if(sessionStorage.getItem('fera-nav'))d.classList.add('fera-nav-in');}catch(e){}})(document.documentElement);
window.Tawk_API=window.Tawk_API||{};(function(T){var h=T.onLoad,m=T.onChatMinimized;T.onLoad=function(){try{T.hideWidget();}catch(e){}if(h)h();};T.onChatMinimized=function(){try{T.hideWidget();}catch(e){}if(m)m();};})(window.Tawk_API);
</script>
<link rel="preload" href="<?php echo esc_url( FERA_URI . '/assets/fonts/geist-latin-wght-normal.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?php echo esc_url( FERA_URI . '/assets/fonts/bodoni-moda-latin-wght-italic.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?php echo esc_url( FERA_URI . '/assets/img/markd-560.webp' ); ?>" as="image" type="image/webp">
		<?php
		if ( ! has_site_icon() ) {
			printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", esc_url( FERA_URI . '/assets/img/icon-32.png' ) );
			printf( '<link rel="icon" href="%s" sizes="192x192">' . "\n", esc_url( FERA_URI . '/assets/img/icon-192.png' ) );
			printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( FERA_URI . '/assets/img/icon-192.png' ) );
		}
	},
	1
);

/* ---------- WordPress head clean-up (speed) ---------- */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'emoji_svg_url', '__return_false' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_deregister_script( 'wp-embed' );
	},
	100
);

/**
 * Design pages are fully styled by the theme. When a page was previously built
 * with Elementor, its kit/page CSS and scripts would override the design
 * (heading colours, fonts, layout), so drop them on those views only.
 */
function fera_strip_builder_assets() {
	if ( is_admin() || ! fera_is_design_view() ) {
		return;
	}
	global $wp_styles, $wp_scripts;
	foreach ( (array) ( $wp_styles->queue ?? array() ) as $h ) {
		if ( preg_match( '/^(elementor(?!-admin-bar|-common|-icons)|widget-|e-animation|e-swiper|swiper)/', $h ) ) {
			wp_dequeue_style( $h );
		}
	}
	foreach ( (array) ( $wp_scripts->queue ?? array() ) as $h ) {
		if ( preg_match( '/^(elementor-frontend|elementor-pro-frontend|elementor-waypoints|swiper)$/', $h ) ) {
			wp_dequeue_script( $h );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'fera_strip_builder_assets', 9999 );
add_action( 'wp_print_styles', 'fera_strip_builder_assets', 1 );
add_action( 'wp_print_footer_scripts', 'fera_strip_builder_assets', 1 );

/** Remove Elementor kit/page body classes on design views (kit CSS is scoped to them). */
add_filter(
	'body_class',
	static function ( $classes ) {
		if ( ! fera_is_design_view() ) {
			return $classes;
		}
		return array_values(
			array_filter(
				$classes,
				static function ( $c ) {
					return 0 !== strpos( $c, 'elementor-' );
				}
			)
		);
	},
	999
);
