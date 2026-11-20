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
		// Main stylesheet is inlined (see wp_head below) to avoid a render-blocking request.
		wp_register_style( 'fera-main', false, array(), fera_ver( 'assets/css/main.css' ) );
		wp_enqueue_style( 'fera-main' );
		$css = (string) file_get_contents( FERA_DIR . '/assets/css/main.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$css = str_replace( 'url(../', 'url(' . FERA_URI . '/assets/', $css );
		wp_add_inline_style( 'fera-main', $css );

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
<script id="fera-config" data-cfasync="false" data-category="necessary">
window.FERA=<?php echo wp_json_encode( apply_filters( 'fera_frontend_config', $config ) ); ?>;
(function(d){d.classList.add('fera-quick','fera-idle');try{if(sessionStorage.getItem('fera-nav'))d.classList.add('fera-nav-in');}catch(e){}})(document.documentElement);
</script>
<script id="fera-chat-setup" data-category="necessary">
window.Tawk_API=window.Tawk_API||{};(function(T,w){var h=T.onLoad,m=T.onChatMinimized,u=T.onUnreadCountChanged;function keep(){try{if(w.feraAgentOpen){T.showWidget();}else{T.hideWidget();}}catch(e){}}T.onLoad=function(){try{T.hideWidget();}catch(e){}if(h)h();};T.onChatMinimized=function(){try{T.hideWidget();}catch(e){}if(m)m();};T.onUnreadCountChanged=function(n){if(n>0)keep();if(u)u(n);};T.onChatMessageAgent=keep;T.onChatMessageSystem=keep;})(window.Tawk_API,window);
</script>
<link rel="preload" href="<?php echo esc_url( FERA_URI . '/assets/fonts/geist-latin-wght-normal.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?php echo esc_url( FERA_URI . '/assets/fonts/bodoni-moda-latin-wght-italic.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
		<?php
		// Fetch the logo the 3D scene samples early (the 3D engine itself is not preloaded: it would delay first paint on mobile).
		if ( fera_is_design_view() ) {
			printf( '<link rel="preload" as="image" type="image/webp" href="%s">' . "\n", esc_url( FERA_URI . '/assets/img/markd-560.webp' ) );
		}
		?>
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
		if ( preg_match( '/^(elementor(?!-admin-bar|-common|-icons)|widget-|e-animation|e-swiper|swiper|contact-form-7)/', $h ) ) {
			wp_dequeue_style( $h );
		}
	}
	foreach ( (array) ( $wp_scripts->queue ?? array() ) as $h ) {
		if ( preg_match( '/^(elementor-frontend|elementor-pro-frontend|elementor-waypoints|swiper|contact-form-7|swv|wpcf7-recaptcha|google-recaptcha)$/', $h ) ) {
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

/**
 * Speed: print every classic script in the footer (they no longer block the
 * first paint). Dependencies follow automatically. Logged-in users keep the
 * default so wp-admin tools in the toolbar behave as usual.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}
		global $wp_scripts;
		$move = static function ( $handle ) use ( &$move, $wp_scripts ) {
			if ( empty( $wp_scripts->registered[ $handle ] ) || 1 === $wp_scripts->get_data( $handle, 'group' ) ) {
				return;
			}
			// Some plugins print inline code in <head> that calls their script right away, leave those in place.
			if ( preg_match( '#official-facebook-pixel|facebook-pixel#', (string) $wp_scripts->registered[ $handle ]->src . ' ' . $handle ) ) {
				return;
			}
			// The consent banner should appear early (it is often the largest text on mobile): keep it in <head>, deferred.
			if ( false !== strpos( (string) $wp_scripts->registered[ $handle ]->src, '/fera-consent/' ) ) {
				$wp_scripts->add_data( $handle, 'strategy', 'defer' );
				return;
			}
			$wp_scripts->add_data( $handle, 'group', 1 );
			foreach ( (array) $wp_scripts->registered[ $handle ]->deps as $dep ) {
				$move( $dep );
			}
		};
		foreach ( (array) $wp_scripts->queue as $h ) {
			$move( $h );
		}
	},
	10000
);

/** Plugin styles the design pages never use. */
add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( ! fera_is_design_view() ) {
			return;
		}
		global $wp_styles;
		foreach ( (array) $wp_styles->queue as $h ) {
			$src = (string) ( $wp_styles->registered[ $h ]->src ?? '' );
			if ( preg_match( '/^(jetpack-forms|grunion|jetpack_css|aioseo-table-of-contents|aioseo-toc|wp-block-library|global-styles|mediaelement|wp-mediaelement)/', $h ) || preg_match( '#table-of-contents|jetpack-forms|paypal-payment|mediaelement#', $src ) ) {
				wp_dequeue_style( $h );
			}
		}
	},
	10001
);

/**
 * Inline the consent banner stylesheet (it is tiny) so it no longer blocks first paint.
 */
add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_admin() ) {
			return;
		}
		$styles = wp_styles();
		foreach ( (array) $styles->queue as $handle ) {
			$src = isset( $styles->registered[ $handle ] ) ? (string) $styles->registered[ $handle ]->src : '';
			if ( ! preg_match( '#/plugins/fera-consent/([\w\-/]+\.css)#', $src, $m ) ) {
				continue;
			}
			$file = WP_PLUGIN_DIR . '/fera-consent/' . $m[1];
			if ( is_readable( $file ) && filesize( $file ) < 40000 ) {
				$css = (string) file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
				wp_dequeue_style( $handle );
				wp_register_style( 'fera-consent-inline', false, array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
				wp_enqueue_style( 'fera-consent-inline' );
				wp_add_inline_style( 'fera-consent-inline', $css );
			}
		}
	},
	10000
);

/**
 * The Meta pixel plugin prints an inline FacebookSignal.init()/initPixel() call that runs
 * immediately, while the consent plugin holds the Meta script back until marketing consent.
 * That inline call is put behind the same consent gate here, and it waits for the Meta
 * script to be ready, so the pixel starts correctly after consent and nothing errors before.
 */
add_action(
	'wp_head',
	static function () {
		if ( ! is_admin() ) {
			ob_start();
		}
	},
	-1000
);
add_action(
	'wp_head',
	static function () {
		if ( is_admin() ) {
			return;
		}
		$html = (string) ob_get_clean();
		$html = preg_replace_callback(
			'#<script(?![^>]*type=["\']text/plain)[^>]*>(\s*FacebookSignal\.init\([\s\S]*?)</script>#',
			static function ( $m ) {
				return '<script data-fc-category="marketing" data-fc-kind="script" data-fc-type="text/javascript" type="text/plain">'
					. '(function(){var n=0;(function r(){if(window.FacebookSignal&&window.FacebookSignal.initPixel){' . $m[1] . '}else if(n++<300){setTimeout(r,50);}})();})();'
					. '</script>';
			},
			$html
		);
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- re-emitting wp_head output.
	},
	PHP_INT_MAX
);
