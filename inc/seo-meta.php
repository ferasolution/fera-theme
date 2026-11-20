<?php
/**
 * Curated SEO titles, descriptions and keywords for every designed page.
 *
 * Works with All in One SEO: our values are used unless an editor has written a
 * custom title/description for that page in AIOSEO (theirs always wins).
 * Without an SEO plugin the theme prints the tags itself.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Meta for the current view, or null for views we don't curate (posts, legal pages…).
 *
 * @return array{title:string,desc:string,keys:string}|null
 */
function fera_seo_meta() {
	static $cache = false;
	if ( false !== $cache ) {
		return $cache;
	}
	$m = array(
		'home'       => array(
			'Fera Solution | Web, Software & Cybersecurity Agency USA',
			'USA-based digital agency in Herndon, Virginia serving clients worldwide. Website & software development, cybersecurity, SEO, digital marketing, design and e-commerce.',
			'Fera Solution, digital agency USA, web development company, software development company, cybersecurity services, SEO agency, digital marketing agency, Herndon Virginia, website design, e-commerce development',
		),
		'about'      => array(
			'About Fera Solution | USA Digital Agency in Herndon, Virginia',
			'Meet Fera Solution LLC: engineers, designers, writers, marketers and security specialists in Herndon, Virginia, working as one accountable team for clients worldwide.',
			'about Fera Solution, digital agency Herndon Virginia, USA IT company, web and software team, cybersecurity experts, full-service agency',
		),
		'services'   => array(
			'Digital Services | Web, Software, Security & Marketing | Fera Solution',
			'Eight services under one roof: cybersecurity, website development, software development, digital marketing & SEO, graphic design, content writing, e-commerce and social media.',
			'digital services, website development services, software development services, cybersecurity services, SEO services, digital marketing services, graphic design, content writing, e-commerce, social media management',
		),
		'work'       => array(
			'Our Work & Case Studies | Fera Solution',
			'Platforms we design, build and run: learning management, hospital management and community portals, engineered for speed, security and scale by Fera Solution.',
			'Fera Solution portfolio, case studies, LMS development, hospital management system, community portal, SaaS development, web application development',
		),
		'contact'    => array(
			'Contact Fera Solution | Free Consultation, USA & Worldwide',
			'Start your project with Fera Solution. Call 337-252-4040, WhatsApp 337-445-5717 or email sales@ferasolution.com. Office in Herndon, Virginia; clients worldwide.',
			'contact Fera Solution, hire web developer, free consultation, IT company contact, digital agency USA, WhatsApp Fera Solution',
		),
		'experience' => array(
			'Live Demos | Interactive Website & App Demos | Fera Solution',
			'Try live, interactive demos of websites, apps and dashboards built by Fera Solution, see the speed, motion and quality before you start your own project.',
			'live website demos, web app demo, interactive demos, website examples, Fera Solution demos',
		),
		'blog'       => array(
			'Blog | Cybersecurity, Web & Digital Marketing Insights | Fera Solution',
			'Practical insights on cybersecurity, website development, software and digital growth from the Fera Solution team in Herndon, Virginia.',
			'cybersecurity blog, web development tips, digital marketing insights, SEO tips, software development blog, Fera Solution blog',
		),
	);
	$svc = array(
		'cybersecurity'        => array( 'Cybersecurity Services | Pen Testing & Risk Assessment | Fera Solution', 'cybersecurity services, penetration testing, vulnerability assessment, risk assessment, website security, network security, security hardening, cybersecurity company USA' ),
		'web-development'      => array( 'Website Development Services | WordPress & Custom Sites | Fera Solution', 'website development, web design company, WordPress development, custom website, React development, responsive website, fast website, web development agency USA' ),
		'software-development' => array( 'Custom Software Development | Apps & Dashboards | Fera Solution', 'custom software development, web application development, SaaS development, dashboard development, mobile app development, API development, software company USA' ),
		'digital-marketing'    => array( 'Digital Marketing & SEO Services | Fera Solution', 'digital marketing services, SEO services, technical SEO, Google Ads, PPC management, local SEO, content marketing, digital marketing agency USA' ),
		'graphic-design'       => array( 'Graphic Design & Branding Services | Fera Solution', 'graphic design services, logo design, brand identity, branding agency, social media graphics, UI design, print design' ),
		'content-writing'      => array( 'Content Writing Services | SEO Copy & Blogs | Fera Solution', 'content writing services, SEO content writing, website copywriting, blog writing, product descriptions, copywriter USA' ),
		'ecommerce'            => array( 'E-Commerce Development | WooCommerce & Shopify Stores | Fera Solution', 'e-commerce development, online store development, WooCommerce development, Shopify development, ecommerce website, payment integration' ),
		'social-media'         => array( 'Social Media Management Services | Fera Solution', 'social media management, social media marketing, Instagram management, Facebook marketing, LinkedIn marketing, content calendar, social media agency' ),
	);

	$r   = fera_route();
	$out = null;
	if ( isset( $m[ $r ] ) ) {
		$out = $m[ $r ];
	} elseif ( 'service' === $r ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		$s    = fera_service( $slug );
		if ( $s && isset( $svc[ $slug ] ) ) {
			$lead = wp_strip_all_tags( $s['lead'] );
			if ( strlen( $lead ) > 160 ) {
				$lead = rtrim( wp_html_excerpt( $lead, 157 ), ' ,., -' ) . '…';
			}
			$out = array( $svc[ $slug ][0], $lead, $svc[ $slug ][1] );
		}
	} elseif ( is_home() && ! is_front_page() ) {
		$out = $m['blog'];
	} elseif ( is_page( 'experience' ) || ( function_exists( 'fera_demo_hub_id' ) && is_page( fera_demo_hub_id() ) ) ) {
		$out = $m['experience'];
	}
	$cache = $out ? array( 'title' => $out[0], 'desc' => $out[1], 'keys' => $out[2] ) : null;
	return $cache;
}

/** Has an editor written a custom AIOSEO field for this object? */
function fera_aioseo_custom( $field ) {
	if ( ! function_exists( 'aioseo' ) || ! is_singular() && ! is_home() || is_front_page() ) {
		return false;
	}
	try {
		$id = is_home() ? (int) get_option( 'page_for_posts' ) : get_queried_object_id();
		$md = aioseo()->meta->metaData->getMetaData( get_post( $id ) );
		return $md && ! empty( $md->$field ) && false === strpos( (string) $md->$field, '#' );
	} catch ( \Throwable $e ) {
		return false;
	}
}

/* ---- AIOSEO integration ---- */
add_filter(
	'aioseo_title',
	static function ( $title ) {
		$m = fera_seo_meta();
		return ( $m && ! fera_aioseo_custom( 'title' ) ) ? $m['title'] : $title;
	},
	20
);
add_filter(
	'aioseo_description',
	static function ( $desc ) {
		$m = fera_seo_meta();
		return fera_seo_trim( ( $m && ! fera_aioseo_custom( 'description' ) ) ? $m['desc'] : $desc );
	},
	20
);

/** Keep descriptions within what Google shows (~160 characters), cut at a word. */
function fera_seo_trim( $d ) {
	$d = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $d ) ) );
	if ( mb_strlen( $d ) <= 160 ) {
		return $d;
	}
	$cut = mb_substr( $d, 0, 157 );
	$sp  = mb_strrpos( $cut, ' ' );
	return rtrim( $sp ? mb_substr( $cut, 0, $sp ) : $cut, ' ,.;:, -' ) . '…';
}
foreach ( array( 'aioseo_facebook_tags', 'aioseo_twitter_tags' ) as $fera_hook ) {
	add_filter(
		$fera_hook,
		static function ( $tags ) {
			if ( ! is_array( $tags ) ) {
				return $tags;
			}
			// Every page gets a share image (post thumbnail first, then the brand image).
			$img = ( is_singular() && has_post_thumbnail() ) ? get_the_post_thumbnail_url( null, 'large' ) : FERA_URI . '/assets/img/og-image.jpg';
			if ( isset( $tags['og:type'] ) && empty( $tags['og:image'] ) ) {
				$tags['og:image']        = $img;
				$tags['og:image:secure_url'] = $img;
				if ( ! ( is_singular() && has_post_thumbnail() ) ) {
					$tags['og:image:width']  = 1200;
					$tags['og:image:height'] = 630;
				}
				$tags['og:image:alt'] = get_bloginfo( 'name' );
			}
			if ( isset( $tags['twitter:card'] ) ) {
				$tags['twitter:card'] = 'summary_large_image';
				if ( empty( $tags['twitter:image'] ) ) {
					$tags['twitter:image'] = $img;
				}
			}
			foreach ( array( 'og:description', 'twitter:description' ) as $k ) {
				if ( isset( $tags[ $k ] ) ) {
					$tags[ $k ] = fera_seo_trim( $tags[ $k ] );
				}
			}
			$m = fera_seo_meta();
			if ( ! $m ) {
				return $tags;
			}
			foreach ( array( 'og:title', 'twitter:title' ) as $k ) {
				if ( isset( $tags[ $k ] ) && ! fera_aioseo_custom( 'og_title' ) ) {
					$tags[ $k ] = $m['title'];
				}
			}
			foreach ( array( 'og:description', 'twitter:description' ) as $k ) {
				if ( isset( $tags[ $k ] ) && ! fera_aioseo_custom( 'og_description' ) ) {
					$tags[ $k ] = $m['desc'];
				}
			}
			return $tags;
		},
		20
	);
}
// The theme prints keywords itself (below), so AIOSEO's copy is dropped to avoid duplicates.
add_filter(
	'aioseo_keywords',
	static function ( $k ) {
		return fera_seo_meta() ? '' : $k;
	},
	20
);

/* ---- Without an SEO plugin: document title ---- */
add_filter(
	'pre_get_document_title',
	static function ( $t ) {
		if ( fera_seo_plugin_active() ) {
			return $t;
		}
		$m = fera_seo_meta();
		return $m ? $m['title'] : $t;
	},
	20
);

/* ---- Keywords, geo and (fallback) description tags ---- */
add_action(
	'wp_head',
	static function () {
		$m = fera_seo_meta();
		if ( $m ) {
			printf( '<meta name="keywords" content="%s">' . "\n", esc_attr( $m['keys'] ) );
			if ( ! fera_seo_plugin_active() ) {
				printf( '<meta name="description" content="%s">' . "\n", esc_attr( $m['desc'] ) );
			}
		}
		if ( is_front_page() || 'contact' === fera_route() ) {
			echo '<meta name="geo.region" content="US-VA"><meta name="geo.placename" content="Herndon, Virginia">' . "\n";
		}
	},
	2
);

/* ---- Breadcrumb schema for inner design pages ---- */
add_action(
	'wp_head',
	static function () {
		if ( is_front_page() || is_404() || fera_seo_plugin_active() ) {
			return; // AIOSEO already outputs a BreadcrumbList.
		}
		$items = array( array( 'Home', home_url( '/' ) ) );
		if ( is_singular( 'fera_service' ) ) {
			$items[] = array( 'Services', fera_page_url( 'services' ) );
			$items[] = array( get_the_title(), get_permalink() );
		} elseif ( is_singular( 'post' ) ) {
			if ( get_option( 'page_for_posts' ) ) {
				$items[] = array( 'Blog', get_permalink( get_option( 'page_for_posts' ) ) );
			}
			$items[] = array( get_the_title(), get_permalink() );
		} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
			$items[] = array( 'Blog', get_permalink( get_option( 'page_for_posts' ) ) );
		} elseif ( is_page() ) {
			$items[] = array( get_the_title(), get_permalink() );
		} else {
			return;
		}
		$list = array();
		foreach ( $items as $i => $it ) {
			$list[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags( $it[0] ), 'item' => $it[1] );
		}
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
	},
	6
);

/* ---- Site verification tags (moved here from the WPCode snippet) ---- */
add_action(
	'wp_head',
	static function () {
		echo '<meta name="facebook-domain-verification" content="3lbrq5yf4gppz2jjsag3u2a1eru09w">' . "\n";
		echo '<meta name="gridinsoft-key" content="60occkd25ccl96jkn0ztpnu5wrfr97sx9wqmf75bu487ddvllvk4ddjeikvylk1u">' . "\n";
	},
	1
);
