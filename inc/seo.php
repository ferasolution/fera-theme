<?php
/**
 * SEO: structured data for the design pages, and basic meta tags only when
 * no SEO plugin is active (All in One SEO normally handles titles/meta/sitemaps).
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

/** Is an SEO plugin handling meta tags? */
function fera_seo_plugin_active() {
	return defined( 'AIOSEO_VERSION' ) || function_exists( 'aioseo' ) || defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' );
}

/** Organisation facts used in schema. */
function fera_org() {
	return array(
		'@type'         => 'ProfessionalService',
		'@id'           => home_url( '/#fera-business' ),
		'name'          => 'Fera Solution LLC',
		'alternateName' => 'Fera Solution',
		'url'           => home_url( '/' ),
		'logo'          => FERA_URI . '/assets/img/icon-512.png',
		'image'         => FERA_URI . '/assets/img/og-image.jpg',
		'description'   => 'USA-based digital agency headquartered in Herndon, Virginia, serving clients worldwide with cybersecurity, website and software development, digital marketing, graphic design, content writing, e-commerce and social media management.',
		'telephone'     => '+1-337-252-4040',
		'email'         => 'sales@ferasolution.com',
		'address'       => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'Herndon',
			'addressRegion'   => 'VA',
			'addressCountry'  => 'US',
		),
		'areaServed'    => 'Worldwide',
		'sameAs'        => array(
			'https://www.facebook.com/FeraSolution/',
			'https://www.instagram.com/ferasolution/',
			'https://www.linkedin.com/company/ferasolution/',
		),
		'contactPoint'  => array(
			array(
				'@type'             => 'ContactPoint',
				'contactType'       => 'sales',
				'telephone'         => '+1-337-252-4040',
				'email'             => 'sales@ferasolution.com',
				'areaServed'        => 'Worldwide',
				'availableLanguage' => 'English',
			),
			array(
				'@type'       => 'ContactPoint',
				'contactType' => 'customer support',
				'telephone'   => '+1-337-445-5717',
				'url'         => 'https://wa.me/13374455717',
				'email'       => 'support@ferasolution.com',
				'areaServed'  => 'Worldwide',
			),
		),
		'knowsAbout'    => array( 'Cybersecurity', 'Website development', 'Software development', 'Digital marketing', 'SEO', 'Graphic design', 'Content writing', 'E-commerce', 'Social media management' ),
	);
}

/** FAQ schema from [question, answer] pairs. */
function fera_faq_schema( $pairs ) {
	$items = array();
	foreach ( $pairs as $qa ) {
		$items[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $qa[0] ),
			'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $qa[1] ) ),
		);
	}
	return array( '@type' => 'FAQPage', 'mainEntity' => $items );
}

add_action(
	'wp_head',
	static function () {
		$graph = array();
		if ( is_front_page() ) {
			$org                    = fera_org();
			$org['hasOfferCatalog'] = array(
				'@type'           => 'OfferCatalog',
				'name'            => 'Digital services',
				'itemListElement' => array_map(
					static function ( $s ) {
						return array(
							'@type'       => 'Offer',
							'itemOffered' => array( '@type' => 'Service', 'name' => $s['name'], 'url' => fera_service_url( $s['slug'] ) ),
						);
					},
					fera_services()
				),
			);
			$graph[] = $org;
			$graph[] = fera_faq_schema(
				array(
					array( 'Where is Fera Solution based, and do you work internationally?', 'Our office is in Herndon, Virginia, USA, and we serve clients worldwide. Wherever you are, we work across time zones through video calls, shared project previews and a dedicated point of contact.' ),
					array( 'What services do you offer?', 'Eight services under one roof: cybersecurity, website development, software development, digital marketing, graphic design, content writing, e-commerce solutions and social account management.' ),
					array( 'Can one team handle my whole project?', 'Yes. Strategy, design, development, security and marketing are handled by one accountable team, so nothing gets lost between agencies or freelancers.' ),
					array( 'Will my website be secure and fast?', 'Yes. We started in cybersecurity, so SSL, security headers, hardened logins, backups and performance optimisation are part of every build, not an extra.' ),
					array( 'How do we get started?', 'Send a short brief or book a free consultation. We reply within one business day with clear next steps.' ),
				)
			);
		} elseif ( is_singular( 'fera_service' ) ) {
			$s = fera_service( get_post_field( 'post_name', get_queried_object_id() ) );
			if ( $s ) {
				$graph[] = array(
					'@type'       => 'Service',
					'name'        => $s['name'],
					'serviceType' => $s['name'],
					'description' => $s['lead'],
					'url'         => get_permalink(),
					'areaServed'  => 'Worldwide',
					'provider'    => array( '@id' => home_url( '/#fera-business' ), '@type' => 'ProfessionalService', 'name' => 'Fera Solution LLC' ),
				);
				if ( ! empty( $s['faq'] ) ) {
					$graph[] = fera_faq_schema( $s['faq'] );
				}
			}
		}
		if ( $graph ) {
			echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
		}

		if ( fera_seo_plugin_active() ) {
			return;
		}
		// Fallback meta when no SEO plugin is installed.
		$desc = '';
		if ( is_front_page() ) {
			$desc = 'Fera Solution is a USA-based digital agency (Herndon, Virginia) serving clients worldwide: web and software development, cybersecurity, digital marketing, design, content, e-commerce and social media.';
		} elseif ( is_singular() ) {
			$desc = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ), 28, '…' );
		}
		$desc = trim( wp_strip_all_tags( (string) $desc ) );
		if ( function_exists( 'fera_seo_meta' ) && fera_seo_meta() ) {
			$desc = fera_seo_meta()['desc'];
		}
		$url  = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
		if ( $desc ) {
			if ( ! ( function_exists( 'fera_seo_meta' ) && fera_seo_meta() ) ) {
				printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
			}
			printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
		}
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
		printf( '<meta property="og:type" content="website"><meta property="og:site_name" content="Fera Solution"><meta property="og:url" content="%s"><meta property="og:title" content="%s"><meta property="og:image" content="%s"><meta name="twitter:card" content="summary_large_image">' . "\n", esc_url( $url ), esc_attr( wp_get_document_title() ), esc_url( FERA_URI . '/assets/img/og-image.jpg' ) );
	},
	5
);

/** Readable title separator when no SEO plugin sets one. */
add_filter(
	'document_title_separator',
	static function () {
		return '|';
	}
);

/** Services in the WordPress core sitemap are on by default; hide the private lead type. */
add_filter(
	'wp_sitemaps_post_types',
	static function ( $types ) {
		unset( $types['fera_lead'] );
		return $types;
	}
);
