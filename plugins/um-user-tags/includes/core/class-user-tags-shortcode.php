<?php
namespace um_ext\um_user_tags\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Tags_Shortcode
 *
 * @package um_ext\um_user_tags\core
 */
class User_Tags_Shortcode {

	var $tax_request = false;

	/**
	 * User_Tags_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_tags', array( &$this, 'ultimatemember_tags' ) );

		add_filter( 'request', array( &$this, 'taxonomy_template' ), 10, 1 );
		add_filter( 'um_prepare_user_query_args', array( &$this, 'change_query' ), 10, 2 );
		add_action( 'um_pre_users_query', array( &$this, 'change_query_meta' ), 10, 3 );
		add_action( 'um_member_directory_before_query', array( &$this, 'member_directory_before_query' ), 10 );
	}


	/**
	 * Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_tags( $args = array() ) {
		$defaults = array(
			'term_id'       => 0,
			'user_field'    => 0,
			'number'        => 0,
			'orderby'       => 'count',
			'order'         => 'desc'
		);
		$args = wp_parse_args( $args, $defaults );
		extract( $args );

		if ( ! $args['term_id'] ) {
			return '';
		}

		if ( $args['orderby'] != 'count' ) {
			$args['order'] = 'asc';
		}

		wp_enqueue_script( 'um-user-tags' );
		wp_enqueue_style( 'um-user-tags' );

		$terms = UM()->User_Tags()->get_localized_terms( array(
			'parent'    => $args['term_id'],
			'number'    => $args['number'],
			'orderby'   => $args['orderby'],
			'order'     => $args['order']
		) );

		$tags = get_option( 'um_user_tags_filters', array() );

		ob_start();

		if ( empty( $terms ) || empty( $tags ) ) {
			_e( 'There are no tags to display.', 'um-user-tags' );
		} else {
			//calculate count of members in tag
			//if there are more then 1 field for 1 parent tag - use new logic with parse users with current user_tags
			//else use old logic with $term->count

			$calculate_count = count( $tags ) !== count( array_unique( array_values( $tags ) ) );

			if ( apply_filters( 'ultimatemember_tags_calculate_count', $calculate_count, $tags, $terms ) ) {
				foreach ( $terms as $term ) {
					$tag = $args['user_field'];

					$users = get_users( array(
						'meta_query' => array(

							array(
								'key'       => $tag,
								'value'     => $term->term_id,
								'compare'   => '=',
							),
							array(
								'key'       => $tag,
								'value'     => $term->slug,
								'compare'   => '=',
							),
							array(
								'key'       => $tag,
								'value'     => trim( serialize( intval( $term->term_id ) ) ),
								'compare'   => 'LIKE',
							),
							array(
								'key'       => $tag,
								'value'     => trim( serialize( strval( $term->term_id ) ) ),
								'compare'   => 'LIKE',
							),
							array(
								'key'       => $tag,
								'value'     => trim( serialize( strval( $term->slug ) ) ),
								'compare'   => 'LIKE',
							),
							'relation' => 'OR',
						),
						'fields' => 'ids'
					) );

					$term->count = ! empty( $users ) ? count( $users ) : 0;
				}

				if ( $args['orderby'] == 'count' ) {
					usort( $terms, function ( $a, $b ) {
						if ( $a->count == $b->count ) {
							return 0 ;
						}
						return ( $a->count < $b->count ) ? 1 : -1;
					} );
				}
			}

			$metakey = $args['user_field'];

			$t_args = compact( 'args', 'tags', 'terms', 'metakey' );
			UM()->get_template( 'tags-widget.php', um_user_tags_plugin, $t_args, true );
		}

		$output = ob_get_clean();
		return $output;
	}


	/**
	 * Replace query if load permalink of Topic Tag or Forum Category
	 *
	 * @param $query_request
	 *
	 * @return array
	 */
	function taxonomy_template( $query_request ) {
		if ( isset( $query_request['taxonomy'] ) && 'um_user_tag' == $query_request['taxonomy'] ) {

			if ( empty( $query_request['term'] ) ) {
				return $query_request;
			}

			$term = get_term_by( 'slug', $query_request['term'], 'um_user_tag' );
			if ( UM()->external_integrations()->is_wpml_active() ) {
				$language_codes = UM()->external_integrations()->get_languages_codes();

				if ( $language_codes['default'] != $language_codes['current'] ) {
					global $sitepress;

					$english_id = wpml_object_id_filter( $term->term_id, 'um_user_tag', true, $sitepress->get_default_language() );

					remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
					$term = get_term_by( 'id', $english_id, 'um_user_tag' );
					add_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1, 1 );

					if ( $term->parent == 0 ) {
						return $query_request;
					}

					$query_request['term'] = $term->slug;
				}
			} else {
				if ( $term->parent == 0 ) {
					return $query_request;
				}
			}

			$base = '';
			if ( ! UM()->external_integrations()->is_wpml_active() ) {
				$members_page = get_post( UM()->config()->permalinks['members'] );
			} else {
				if ( function_exists( 'icl_get_current_language' ) && icl_get_current_language() != icl_get_default_language() ) {
					$lang_post_id = icl_object_id( UM()->config()->permalinks['members'], 'page', true, icl_get_current_language() );
					$members_page = get_post( $lang_post_id );
				}
			}

			if ( ! empty( $members_page ) ) {
				$url = get_home_url( get_current_blog_id() );
				$base = str_replace( trailingslashit( $url ), '', get_permalink( $members_page ) );
			}

			$this->tax_request = $query_request['term'];

			$query_request = array(
				'page' => '',
				'pagename' => $base,
			);

			add_action( 'loop_start', array( &$this, 'replace_post_data' ) );
		}

		return $query_request;
	}


	/**
	 * Replace post data on term page loading
	 */
	function replace_post_data() {
		add_filter( 'the_title', array( &$this, 'tax_title' ), 10, 2 );
		add_filter( 'the_content', array( &$this, 'tax_content' ), 7, 1 );
		add_filter( 'post_class', array( &$this, 'tax_class' ), 10, 3 );
	}


	/**
	 * Replace page title if load CPT terms
	 *
	 * @param $title
	 * @param $post_id
	 *
	 * @return string
	 * @throws \Exception
	 */
	function tax_title( $title, $post_id ) {

		if ( ! empty( $this->tax_request ) && isset( UM()->config()->permalinks['members'] ) && $post_id == UM()->config()->permalinks['members'] ) {
			$directory_id = UM()->options()->get( 'user_tags_base_directory' );

			if ( empty( $directory_id ) ) {
				return $title;
			}

			$term = get_term_by( 'slug', $this->tax_request, 'um_user_tag' );

			if ( empty( $term ) || empty( $_GET['tag_field'] ) ) {
				return $title;
			}

			$data = UM()->fields()->get_field( sanitize_key( $_GET['tag_field'] ) );

			$title = ! empty( $data['label'] ) ? $data['label'] : $data['title'];
			$title .= ': ' . $term->name;

			$title = stripslashes( $title );
		}

		return $title;
	}


	/**
	 * Replace page content if load CPT terms
	 * @param $content
	 *
	 * @return string
	 */
	function tax_content( $content ) {
		if ( ! empty( $this->tax_request ) ) {
			$directory_id = UM()->options()->get( 'user_tags_base_directory' );

			if ( empty( $directory_id ) ) {
				return $content;
			}

			$term = get_term_by( 'slug', $this->tax_request, 'um_user_tag' );

			if ( UM()->external_integrations()->is_wpml_active() ) {
				$language_codes = UM()->external_integrations()->get_languages_codes();

				if ( $language_codes['default'] != $language_codes['current'] ) {
					global $sitepress;

					$english_id = wpml_object_id_filter( $term->term_id, 'um_user_tag', true, $sitepress->get_default_language() );

					remove_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1 );
					$term = get_term_by( 'id', $english_id, 'um_user_tag' );
					add_filter( 'get_term', array( $sitepress, 'get_term_adjust_id' ), 1, 1 );
				}
			}

			if ( empty( $term ) || empty( $_GET['tag_field'] ) ) {
				return $content;
			}

			$tag_field = sanitize_key( $_GET['tag_field'] );

			wp_localize_script( 'um_members', 'um_user_tags', array(
				'user_tag' => $term->term_id,
				'user_tag_field' => $tag_field
			) );

			preg_match( '/\[ultimatemember\s+form_id=[\'"]?(\d+)[\'"]?/im', $content, $matches );
			if ( isset( $matches[1] ) && is_numeric( $matches[1] ) ) {
				$content = preg_replace( '/\[ultimatemember\s+form_id=[\'"]?\d+[\'"]?/im', '[ultimatemember form_id="' . $directory_id . '" user_tag="' . $term->term_id . '" user_tag_field="' . $tag_field . '"', $content );
			} else {
				$content = '[ultimatemember form_id="' . $directory_id . '" user_tag="' . $term->term_id . '" user_tag_field="' . $tag_field . '" /]';
			}
		}
		return $content;
	}


	/**
	 * Replace page classes if load CPT terms
	 *
	 * @param $classes
	 * @param $class
	 * @param $post_id
	 *
	 * @return array
	 */
	function tax_class( $classes, $class, $post_id ) {
		if ( ! empty( $this->tax_request ) ) {
			$classes[] = 'um-user-tags';
		}
		return $classes;
	}


	/**
	 * @param array $query_args
	 * @param $directory_data
	 *
	 * @return array
	 */
	function change_query( $query_args, $directory_data ) {
		if ( empty( $_REQUEST['user_tag'] ) ) {
			return $query_args;
		}

		if ( empty( $_REQUEST['user_tag_field'] ) ) {
			return $query_args;
		}

		$user_tag = absint( $_REQUEST['user_tag'] );

		$term = get_term( $user_tag );

		$arr_meta_query = array(
			array(
				'key'       => sanitize_key( $_REQUEST['user_tag_field'] ),
				'value'     => trim( $user_tag ),
				'compare'   => '=',
			),
			array(
				'key'       => sanitize_key( $_REQUEST['user_tag_field'] ),
				'value'     => serialize( strval( $user_tag ) ),
				'compare'   => 'LIKE',
			),
			array(
				'key'       => sanitize_key( $_REQUEST['user_tag_field'] ),
				'value'     => serialize( intval( $user_tag ) ),
				'compare'   => 'LIKE',
			),
			'relation'      => 'OR'
		);

		if ( ! empty( $term ) ) {
			$arr_meta_query[] = array(
				'key'       => sanitize_key( $_REQUEST['user_tag_field'] ),
				'value'     => serialize( strval( $term->slug ) ),
				'compare'   => 'LIKE',
			);
		}

		$query_args['meta_query']['user_tag'] = $arr_meta_query;

		add_filter( 'um_member_directory_ignore_empty_filters', '__return_true' );

		return $query_args;
	}


	/**
	 * @param $query
	 * @param $directory_data
	 * @param $sortby
	 */
	function change_query_meta( $query, $directory_data, $sortby ) {
		if ( empty( $_REQUEST['user_tag'] ) ) {
			return;
		}

		if ( empty( $_REQUEST['user_tag_field'] ) ) {
			return;
		}

		global $wpdb;

		$user_tag = absint( $_REQUEST['user_tag'] );
		$user_tag_field = sanitize_key( $_REQUEST['user_tag_field'] );

		$where = array(
			$wpdb->prepare( 'umm_usertag.um_value = %s', $user_tag ),
			$wpdb->prepare( 'umm_usertag.um_value LIKE %s', '%' . serialize( strval( $user_tag ) ) . '%' ),
			$wpdb->prepare( 'umm_usertag.um_value LIKE %s', '%' . serialize( intval( $user_tag ) ) . '%' ),
		);

		$term = get_term( $user_tag );
		if ( ! empty( $term ) ) {
			$where[] = $wpdb->prepare( 'umm_usertag.um_value LIKE %s', '%' . serialize( strval( $term->slug ) ) . '%' );
		}

		$query->joins[] = $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata umm_usertag ON ( umm_usertag.user_id = u.ID AND umm_usertag.um_key = %s )", $user_tag_field );
		$query->where_clauses[] = '(' . implode( ' OR ', $where ) . ')';
	}

	/**
	 *
	 */
	function member_directory_before_query() {
		if ( empty( $_REQUEST['user_tag'] ) ) {
			return;
		}

		if ( empty( $_REQUEST['user_tag_field'] ) ) {
			return;
		}

		add_filter( 'um_member_directory_ignore_empty_filters', '__return_true' );
	}
}