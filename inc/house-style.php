<?php
/**
 * House style: no em dashes in anything visitors read.
 * Applied when content is displayed, so existing and future posts (including
 * auto-published ones) follow it without editing the stored text.
 *
 * @package FeraTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replace em dashes with natural punctuation.
 *
 * @param string $t Text or HTML.
 * @return string
 */
function fera_dedash( $t ) {
	if ( ! is_string( $t ) || false === strpos( $t, "\u{2014}" ) ) {
		return $t;
	}
	// "Title | Fera Solution" style separators.
	$t = preg_replace( '/\s+\x{2014}\s+(Fera Solution)\b/u', ' | $1', $t );
	// Inside headings a colon reads best.
	$t = preg_replace_callback(
		'/(<h[1-6][^>]*>)(.*?)(<\/h[1-6]>)/su',
		static function ( $m ) {
			return $m[1] . preg_replace( '/\s*\x{2014}\s*/u', ': ', $m[2] ) . $m[3];
		},
		$t
	);
	// Before a capital letter or number: new sentence; otherwise a comma.
	$t = preg_replace_callback(
		'/(\S)\s*\x{2014}\s*(\S)/u',
		static function ( $m ) {
			$sentence = preg_match( '/[\p{Lu}\d]/u', $m[2] ) && preg_match( '/[\p{Ll}\).!?]/u', $m[1] );
			return $m[1] . ( $sentence ? ( preg_match( '/[.!?]/', $m[1] ) ? ' ' : '. ' ) : ', ' ) . $m[2];
		},
		$t
	);
	return str_replace( "\u{2014}", '-', $t );
}

if ( ! is_admin() ) {
	foreach ( array( 'the_content', 'the_title', 'the_excerpt', 'get_the_excerpt', 'single_post_title', 'widget_text_content', 'comment_text', 'document_title', 'aioseo_title', 'aioseo_description' ) as $fera_hook ) {
		add_filter( $fera_hook, 'fera_dedash', 99 );
	}
	add_filter(
		'aioseo_facebook_tags',
		static function ( $tags ) {
			return is_array( $tags ) ? array_map( static fn( $v ) => is_string( $v ) ? fera_dedash( $v ) : $v, $tags ) : $tags;
		},
		99
	);
	add_filter(
		'aioseo_twitter_tags',
		static function ( $tags ) {
			return is_array( $tags ) ? array_map( static fn( $v ) => is_string( $v ) ? fera_dedash( $v ) : $v, $tags ) : $tags;
		},
		99
	);
}
