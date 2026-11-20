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
		'home' => array(
			'Cybersecurity & Web Development Company USA | Fera Solution',
			'Fera Solution is a USA IT company in Herndon, VA. We build secure websites and custom software, and grow brands with SEO and digital marketing. Free quote.',
			'IT company USA, cybersecurity company, web development company, software development company, digital marketing agency, SEO services, website design, IT services Virginia, Fera Solution',
		),
		'about' => array(
			'About Us | USA IT & Cybersecurity Company | Fera Solution',
			'Meet Fera Solution, a USA-based IT and cybersecurity company in Herndon, Virginia. One team of engineers, designers and marketers serving clients worldwide.',
			'about Fera Solution, IT company Herndon Virginia, cybersecurity company USA, web development team, software engineers, digital agency USA',
		),
		'services' => array(
			'IT Services: Security, Web, Software & SEO | Fera Solution',
			'Cybersecurity, website and software development, SEO, digital marketing, design, content, e-commerce and social media. All IT services in one USA team.',
			'IT services, cybersecurity services, website development services, software development services, SEO services, digital marketing services, graphic design, content writing, e-commerce development, social media management',
		),
		'work' => array(
			'Portfolio & Case Studies | LMS, HMS & SaaS | Fera Solution',
			'See platforms Fera Solution designed and built: learning management, hospital management and community portals. Fast, secure SaaS for real businesses.',
			'Fera Solution portfolio, case studies, LMS development, hospital management system, SaaS development, web application development, custom software projects',
		),
		'contact' => array(
			'Contact Us | Free IT & Web Consultation | Fera Solution',
			'Talk to Fera Solution about your website, software or security project. Call 337-252-4040, WhatsApp 337-445-5717 or email sales@ferasolution.com.',
			'contact Fera Solution, free consultation, hire web developer, cybersecurity consultation, IT company contact, get a quote, web design quote',
		),
		'experience' => array(
			'Live Website & App Demos | Fera Solution',
			'Explore live demos of websites, web apps and dashboards built by Fera Solution. Click around, test the speed and see the quality before you start.',
			'live website demos, web app demos, website examples, dashboard demo, interactive demos, Fera Solution work',
		),
		'blog' => array(
			'IT, Cybersecurity & Marketing Blog | Fera Solution',
			'Practical guides on cybersecurity, web development, software, SEO and digital marketing for growing businesses, from the Fera Solution team.',
			'cybersecurity blog, IT blog, web development tips, SEO tips, digital marketing guide, small business security, Fera Solution blog',
		),
		'privacy-policy' => array(
			'Privacy Policy | Fera Solution',
			'How Fera Solution LLC collects, uses and protects information on ferasolution.com, including cookies, analytics and your privacy choices.',
			'Fera Solution privacy policy, cookie policy, data protection',
		),
		'terms-and-conditions' => array(
			'Terms & Conditions | Fera Solution',
			'The terms for using ferasolution.com and working with Fera Solution LLC on website, software, cybersecurity and marketing projects.',
			'Fera Solution terms and conditions, website terms, service terms',
		),
	);
	$svc = array(
		'cybersecurity' => array( 'Cybersecurity Services USA | Pen Testing | Fera Solution', 'Penetration testing, vulnerability assessments and security hardening for websites, networks and apps. Find and fix risks before attackers do. Free quote.', 'cybersecurity services, cybersecurity company USA, penetration testing, vulnerability assessment, website security audit, network security, security hardening, managed security' ),
		'web-development' => array( 'Website Development Company USA | Web Design | Fera Solution', 'Fast, secure and SEO-ready websites built on WordPress or custom code. Responsive design that turns visitors into customers. Get a free website quote.', 'website development company, web design company USA, WordPress development, custom website development, responsive web design, business website, website redesign' ),
		'software-development' => array( 'Custom Software Development Company USA | Fera Solution', 'Custom web apps, SaaS platforms, dashboards and integrations built around your business. Clean code you own, delivered in short, visible cycles.', 'custom software development, software development company USA, web application development, SaaS development, dashboard development, API integration, business automation' ),
		'digital-marketing' => array( 'SEO & Digital Marketing Agency USA | Fera Solution', 'SEO, Google Ads, local SEO and content marketing that bring qualified leads. Clear monthly reports and strategies built for measurable growth.', 'SEO services, digital marketing agency USA, local SEO, Google Ads management, PPC services, technical SEO, content marketing, lead generation' ),
		'graphic-design' => array( 'Graphic Design & Logo Design Services | Fera Solution', 'Logos, brand identity, social media graphics and marketing design that make your business look professional and consistent everywhere.', 'graphic design services, logo design, brand identity design, branding agency, social media graphics, marketing materials, business card design' ),
		'content-writing' => array( 'SEO Content Writing Services | Blogs & Copy | Fera Solution', 'SEO blog posts, website copy and product descriptions written by people, not templates. Content that ranks on Google and turns readers into leads.', 'content writing services, SEO content writing, website copywriting, blog writing services, product descriptions, copywriting agency' ),
		'ecommerce' => array( 'E-Commerce Website Development USA | Fera Solution', 'Online stores on Shopify or WooCommerce with secure payments, fast checkout and easy product management. Start selling online with a free quote.', 'ecommerce website development, online store development, Shopify development, WooCommerce development, payment gateway integration, ecommerce design' ),
		'social-media' => array( 'Social Media Management Services | Fera Solution', 'Content calendars, posts, reels and community management for Facebook, Instagram and LinkedIn. Grow followers and leads with a consistent brand.', 'social media management, social media marketing services, Instagram management, Facebook marketing, LinkedIn marketing, content calendar, social media agency' ),
	);

	$r   = fera_route();
	$out = null;
	if ( isset( $m[ $r ] ) ) {
		$out = $m[ $r ];
	} elseif ( 'service' === $r ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( isset( $svc[ $slug ] ) ) {
			$out = $svc[ $slug ];
		}
	} elseif ( is_home() && ! is_front_page() ) {
		$out = $m['blog'];
	} elseif ( is_page( 'experience' ) || ( function_exists( 'fera_demo_hub_id' ) && is_page( fera_demo_hub_id() ) ) ) {
		$out = $m['experience'];
	} elseif ( is_page() && isset( $m[ get_post_field( 'post_name', get_queried_object_id() ) ] ) && ! in_array( get_post_field( 'post_name', get_queried_object_id() ), array( 'home', 'blog' ), true ) ) {
		$out = $m[ get_post_field( 'post_name', get_queried_object_id() ) ];
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
