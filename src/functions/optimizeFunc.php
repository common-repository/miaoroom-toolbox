<?php


if ( mrtb_get_option( 'opt_remove_categories_prefix' ) ) {

	add_filter( 'request', function ( $query_vars ) {
		if ( ! isset( $_GET['page_id'] ) && ! isset( $_GET['pagename'] ) && ! empty( $query_vars['pagename'] ) ) {
			$pagename   = $query_vars['pagename'];
			$categories = get_categories( [ 'hide_empty' => false ] );
			$categories = wp_list_pluck( $categories, 'slug' );

			if ( in_array( $pagename, $categories ) ) {
				$query_vars['category_name'] = $query_vars['pagename'];
				unset( $query_vars['pagename'] );
			}
		}

		return $query_vars;
	} );
	add_filter( 'pre_term_link', function ( $term_link, $term ) {
		if ( $term->taxonomy == 'category' ) {
			return '%category%';
		}

		return $term_link;
	}, 10, 2 );
}

if ( mrtb_get_option( 'opt_admin_show_own_media' ) ) {
	add_action( 'pre_get_posts', function ( $wp_query_obj ) {
		global $current_user, $pagenow;
		if ( ! is_a( $current_user, 'WP_User' ) ) {
			return;
		}
		if ( 'admin-ajax.php' != $pagenow || $_REQUEST['action'] != 'query-attachments' ) {
			return;
		}
		if ( ! current_user_can( 'manage_media_library' ) ) //if( !current_user_can('edit_others_posts') )
		{
			$wp_query_obj->set( 'author', $current_user->ID );
		}

		return;
	} );
}

if ( mrtb_get_option( 'opt_admin_show_own_post' ) ) {
	add_filter( 'parse_query', function ( $wp_query ) {
		if ( strpos( $_SERVER['REQUEST_URI'], '/wp-admin/edit.php' ) !== false ) {
			if ( ! current_user_can( 'edit_others_posts' ) ) {
				$wp_query->set( 'author', get_current_user_id() );
			}
		}
	} );
}

add_action( 'init', function () {
	if ( mrtb_get_option( 'opt_admin_show_own_post_comment' ) ) {

		function mrtb_get_comment_list_by_user( $clauses ) {
			if ( is_admin() ) {
				global $user_ID, $wpdb;
				$clauses['join']  = ", wp_posts";
				$clauses['where'] .= " AND wp_posts.post_author = " . $user_ID . " AND wp_comments.comment_post_ID = wp_posts.ID";
			}

			return $clauses;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			add_filter( 'comments_clauses', 'mrtb_get_comment_list_by_user' );
		}
	}
} );

