<?php

namespace RepRelCon;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles modifying the Elementor query for an ACF Repeater source.
 *
 * @param \WP_Query $query The query object.
 * @param \Elementor\Widget_Base $widget The widget instance.
 *
 * @return \WP_Query The modified query object.
 */
function handle_acf_repeater_query( $query, $widget ) {
	$repeater_name = $widget->get_settings( 'post_query_acf_repeater_name' );
	if ( empty( $repeater_name ) ) {
		return $query;
	}

	$repeater_data = \get_field( $repeater_name, \get_the_ID() );
	if ( ! $repeater_data || ! is_array( $repeater_data ) ) {
		$query->posts       = [];
		$query->post_count  = 0;
		$query->found_posts = 0;

		return $query;
	}

	$new_posts = [];
	foreach ( $repeater_data as $index => $row ) {
		$post                    = new \stdClass();
		$post->ID                = \get_the_ID() . '-' . $index;
		$post->post_title        = isset( $row['title'] ) ? $row['title'] : 'Item ' . ( $index + 1 );
		$post->post_content      = isset( $row['content'] ) ? $row['content'] : '';
		$post->post_excerpt      = isset( $row['excerpt'] ) ? $row['excerpt'] : '';
		$post->post_status       = 'publish';
		$post->post_type         = 'acf_repeater_item';
		$post                    = new \WP_Post( $post );
		$post->acf_repeater_data = $row;

		$new_posts[] = $post;
	}

	$query->posts       = $new_posts;
	$query->found_posts = \count( $new_posts );
	$query->post_count  = \count( $new_posts );

	return $query;
}

/**
 * Handles modifying the Elementor query for an ACF Relationship source.
 *
 * @param \WP_Query $query The query object.
 * @param \Elementor\Widget_Base $widget The widget instance.
 *
 * @return \WP_Query The modified query object.
 */
function handle_acf_relation_query( $query, $widget ) {
	$relation_name = $widget->get_settings( 'post_query_acf_relation_name' );
	if ( empty( $relation_name ) ) {
		return $query;
	}

	$relation_posts = \get_field( $relation_name, \get_the_ID() );

	if ( empty( $relation_posts ) || ! is_array( $relation_posts ) ) {
		$query->posts       = [];
		$query->post_count  = 0;
		$query->found_posts = 0;

		return $query;
	}

	// The relationship field already returns an array of post objects.
	$query->posts       = $relation_posts;
	$query->found_posts = \count( $relation_posts );
	$query->post_count  = \count( $relation_posts );

	return $query;
}


\add_filter( 'elementor/query/query_results', function( $query, $widget ) {
	$source = $query->get( 'post_type' );

	if ( 'acf_repeater' === $source ) {
		return handle_acf_repeater_query( $query, $widget );
	}

	if ( 'acf_relation' === $source ) {
		return handle_acf_relation_query( $query, $widget );
	}

	return $query;
}, 10, 2 );
