<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 *
 * @param int $job_id
 */
function um_activity_new_jobboardwp_job( $job_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( get_post_type( $job_id ) != 'jb-job' || get_post_status( $job_id ) != 'publish' ) {
		return;
	}

	if ( ! UM()->options()->get( 'activity-new-jobboardwp-job' ) ) {
		return;
	}

	$job = get_post( $job_id );

	$user_id = $job->post_author;
	um_fetch_user( $user_id );
	$author_name = um_user( 'display_name' );
	$author_profile = um_user_profile_url();

	$post_id = UM()->Activity_API()->api()->save(
		array(
			'template'          => 'new-jobboardwp-job',
			'wall_id'           => 0,
			'author'            => $user_id,
			'author_name'       => $author_name,
			'author_profile'    => $author_profile,
			'post_title'        => '<span class="post-title">' . $job->post_title . '</span>',
			'post_url'          => get_permalink( $job ),
			'post_content'      => $job->post_content,
		)
	);

	update_user_meta( $user_id,'um_activity_published_job_' . $job_id, $post_id );
}
add_action( 'save_post', 'um_activity_new_jobboardwp_job', 9999, 1 );


/**
 * @param int $job_id
 */
function um_activity_jobboardwp_delete_job( $job_id ) {
	$post = get_post( $job_id );

	if ( $post->post_type != 'jb-job' ) {
		return;
	}

	global $wpdb;
	$published_job_post = $wpdb->get_row( $wpdb->prepare(
		"SELECT * 
			FROM {$wpdb->usermeta} 
			WHERE meta_key = %s",
		'um_activity_published_job_' . $job_id
	) );

	if ( ! empty( $published_job_post ) ) {
		wp_delete_post( $published_job_post->meta_value );
		delete_user_meta( $published_job_post->user_id, 'um_activity_published_job_' . $job_id );
	}

	$filled_job_post = $wpdb->get_row( $wpdb->prepare(
		"SELECT * 
			FROM {$wpdb->usermeta} 
			WHERE meta_key = %s",
		'um_activity_filled_job_' . $job_id
	) );

	if ( ! empty( $filled_job_post ) ) {
		wp_delete_post( $filled_job_post->meta_value );
		delete_user_meta( $filled_job_post->user_id, 'um_activity_filled_job_' . $job_id );
	}
}
add_action( 'delete_post', 'um_activity_jobboardwp_delete_job', 9999, 1 );


/**
 * @param $job_id
 * @param $job
 */
function um_activity_job_is_filled( $job_id, $job ) {
	if ( ! UM()->options()->get( 'activity-jobboardwp-job-filled' ) ) {
		return;
	}

	$user_id = $job->post_author;
	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	$post_id = UM()->Activity_API()->api()->save(
		array(
			'template'          => 'jobboardwp-job-filled',
			'wall_id'           => 0,
			'author'            => $user_id,
			'author_name'       => $author_name,
			'author_profile'    => $author_profile,
			'post_title'        => '<span class="post-title">' . $job->post_title . '</span>',
			'post_url'          => get_permalink( $job ),
			'post_excerpt'      => $job->post_content,
		)
	);

	update_user_meta( $user_id,'um_activity_filled_job_' . $job_id, $post_id );

}
add_action( 'jb_fill_job', 'um_activity_job_is_filled', 9999, 2 );


/**
 * @param $job_id
 * @param $job
 */
function um_activity_job_is_unfilled( $job_id, $job ) {
	global $wpdb;

	$filled_job_post = $wpdb->get_row( $wpdb->prepare(
		"SELECT * 
			FROM {$wpdb->usermeta} 
			WHERE meta_key = %s",
		'um_activity_filled_job_' . $job_id
	) );

	if ( ! empty( $filled_job_post ) ) {
		wp_delete_post( $filled_job_post->meta_value );
		delete_user_meta( $filled_job_post->user_id, 'um_activity_filled_job_' . $job_id );
	}
}
add_action( 'jb_unfill_job', 'um_activity_job_is_unfilled', 9999, 2 );


/**
 * New user follow
 *
 * @param $user_id1
 * @param $user_id2
 */
function um_activity_new_follow( $user_id1, $user_id2 ) {
	if ( ! UM()->options()->get('activity-new-follow') ) {
		return;
	}

	um_fetch_user( $user_id2 );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	um_fetch_user( $user_id1 );

	UM()->Activity_API()->api()->save(
		array(
			'template'          => 'new-follow',
			'wall_id'           => 0,
			'author'            => $user_id2,
			'related_id'        => $user_id1,
			'author_name'       => $author_name,
			'author_profile'    => $author_profile,
			'user_name'         => um_user('display_name' ),
			'user_profile'      => um_user_profile_url(),
			'user_photo'        => get_avatar( $user_id1, 80 ),
		)
	);
}
add_action( 'um_followers_after_user_follow', 'um_activity_new_follow', 9999, 2 );


/**
 * Undo new follow
 *
 * @param $user_id1
 * @param $user_id2
 */
function um_activity_new_unfollow( $user_id1, $user_id2 ) {
	if ( ! UM()->options()->get( 'activity-new-follow' ) ) {
		return;
	}

	$args = array(
		'post_type' => 'um_activity',
	);

	$args['meta_query'][] = array('key' => '_user_id','value' => $user_id2,'compare' => '=');
	$args['meta_query'][] = array('key' => '_related_id','value' => $user_id1,'compare' => '=');
	$args['meta_query'][] = array('key' => '_action','value' => 'new-follow','compare' => '=');
	$get = new WP_Query( $args );
	if ( $get->found_posts == 0 ) return;
	foreach( $get->posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}
add_action( 'um_followers_after_user_unfollow', 'um_activity_new_unfollow', 9999, 2 );


/**
 * New user registration
 *
 * @param $user_id
 */
function um_activity_new_user( $user_id ) {
	if ( empty( $user_id ) || ! UM()->user()->is_approved( $user_id ) ) {
		return;
	}
	if ( ! UM()->options()->get('activity-new-user') ) {
		return;
	}

	$author_data = get_userdata( $user_id );
	$author_name = $author_data->display_name;
	$author_profile = um_user_profile_url( $user_id );

	UM()->Activity_API()->api()->save(
		array(
			'template' => 'new-user',
			'wall_id' => 0,
			'author' => $user_id,
			'author_name' => $author_name,
			'author_profile' => $author_profile
		)
	);

}
add_action('um_after_user_is_approved','um_activity_new_user', 90, 1 );


/* new forum topic */
function um_activity_new_topic( $topic_id = 0 ) {
	if ( ! UM()->options()->get('activity-new-topic') ) {
		return;
	}

	$user_id = bbp_get_topic_author_id( $topic_id );

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	if ( bbp_get_topic_content( $topic_id ) ) {
		$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( strip_shortcodes( bbp_get_topic_content( $topic_id ) ), $num_words = 25, $more = null ) . '</span>';
	} else {
		$post_excerpt = '';
	}

	$post_id = UM()->Activity_API()->api()->save(
		array(
			'template' => 'new-topic',
			'wall_id' => 0,
			'author' => $user_id,
			'author_name' => $author_name,
			'author_profile' => $author_profile,
			'post_title' => '<span class="post-title">' . bbp_get_topic_title( $topic_id ) . '</span>',
			'post_url' => bbp_get_topic_permalink( $topic_id ),
			'post_excerpt' => $post_excerpt,
		)
	);

	update_user_meta( $user_id,'bbpress_topic_'.$topic_id, $post_id );

}
add_action('bbp_new_topic', 'um_activity_new_topic', 9999, 1 );


/**
 * @param $topic_id
 */
function um_activity_bbp_delete_topic( $topic_id ) {
	global $wpdb;

	$bbpress_topic = $wpdb->get_row( $wpdb->prepare(
		"SELECT * 
			FROM {$wpdb->usermeta} 
			WHERE meta_key = %s",
			'bbpress_topic_' . $topic_id
		) );
		
	wp_delete_post( $bbpress_topic->meta_value );
		
	delete_user_meta( $bbpress_topic->user_id, 'bbpress_topic_'.$topic_id );
}
add_action( 'bbp_delete_topic','um_activity_bbp_delete_topic', 10, 1 );


/**
 * @param int $post_ID
 * @param \WP_Post $post
 * @param bool $update
 */
function um_activity_new_forumwp_post( $post_ID, $post, $update ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! function_exists( 'FMWP' ) ) {
		return;
	}

	if ( ! in_array( $post->post_type, [ 'fmwp_topic', 'fmwp_reply' ] ) ) {
		return;
	}

	if ( $post->post_status == 'auto-draft' ) {
		return;
	}

	if ( $post->post_type == 'fmwp_topic' ) {
		$f_id = FMWP()->common()->topic()->get_forum_id( $post_ID );
		if ( empty( $f_id ) ) {
			return;
		}
	}

	if ( $post->post_status == 'publish' ) {
		switch ( $post->post_type ) {
			case 'fmwp_topic':

				if ( ! UM()->options()->get( 'activity-new-forumwp-topic' ) ) {
					return;
				}

				$user_id = FMWP()->common()->topic()->get_author_id( $post_ID );

				$topic = $post;

				um_fetch_user( $user_id );
				$author_name = um_user('display_name');
				$author_profile = um_user_profile_url();

				if ( ! empty( $topic->post_content ) && empty( $topic->post_password ) ) {
					$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( strip_shortcodes( $topic->post_content ), $num_words = 25, $more = null ) . '</span>';
				} else {
					$post_excerpt = '';
				}

				$activity_post_id = UM()->Activity_API()->api()->save(
					array(
						'template'          => 'new-forumwp-topic',
						'wall_id'           => 0,
						'author'            => $user_id,
						'author_name'       => $author_name,
						'author_profile'    => $author_profile,
						'post_title'        => '<span class="post-title">' . $topic->post_title . '</span>',
						'post_url'          => get_permalink( $post_ID ),
						'post_excerpt'      => $post_excerpt,
						'related_id'        => $post_ID,
					)
				);

				update_user_meta( $user_id,'forumwp_topic_' . $post_ID, $activity_post_id );

				break;
			case 'fmwp_reply':

				if ( ! UM()->options()->get( 'activity-new-forumwp-reply' ) ) {
					return;
				}

				$user_id = FMWP()->common()->reply()->get_author_id( $post_ID );

				$reply = $post;

				$topic_id = FMWP()->common()->reply()->get_topic_id( $post_ID );
				$topic = get_post( $topic_id );

				um_fetch_user( $user_id );
				$author_name = um_user('display_name');
				$author_profile = um_user_profile_url();

				if ( ! empty( $reply->post_content ) && empty( $topic->post_password ) ) {
					$post_excerpt = '<span class="post-excerpt">' . __( 'Says: ', 'um-activity' ) . wp_trim_words( strip_shortcodes( $reply->post_content ), $num_words = 25, $more = null ) . '</span>';
				} else {
					$post_excerpt = '';
				}

				$activity_post_id = UM()->Activity_API()->api()->save(
					array(
						'template'          => 'new-forumwp-reply',
						'wall_id'           => 0,
						'author'            => $user_id,
						'author_name'       => $author_name,
						'author_profile'    => $author_profile,
						'post_title'        => '<span class="post-title">' . $topic->post_title . '</span>',
						'post_url'          => FMWP()->common()->reply()->get_link( $post_ID ),
						'post_excerpt'      => $post_excerpt,
						'related_id'        => $post_ID,
					)
				);

				update_user_meta( $user_id,'forumwp_reply_' . $post_ID, $activity_post_id );

				break;
		}
	} else {
		if ( $update ) {
			switch ( $post->post_type ) {
				case 'fmwp_reply':
					global $wpdb;

					$forumwp_topic = $wpdb->get_row( $wpdb->prepare(
						"SELECT * 
						FROM {$wpdb->usermeta} 
						WHERE meta_key = %s",
						'forumwp_reply_' . $post->ID
					) );

					wp_delete_post( $forumwp_topic->meta_value, true );

					delete_user_meta( $forumwp_topic->user_id, 'forumwp_reply_' . $post->ID );
					break;
				case 'fmwp_topic':
					global $wpdb;

					$forumwp_topic = $wpdb->get_row( $wpdb->prepare(
						"SELECT * 
						FROM {$wpdb->usermeta} 
						WHERE meta_key = %s",
						'forumwp_topic_' . $post->ID
					) );

					wp_delete_post( $forumwp_topic->meta_value, true );

					delete_user_meta( $forumwp_topic->user_id, 'forumwp_topic_' . $post->ID );
					break;
			}
		}
	}
}
add_action( 'save_post', 'um_activity_new_forumwp_post', 999999, 3 );


/**
 * @param int $post_id
 */
function um_activity_forumwp_delete_post( $post_id ) {
	$post = get_post( $post_id );

	if ( isset( $post->post_type ) && in_array( $post->post_type, ['fmwp_topic', 'fmwp_reply'] ) ) {
		switch ( $post->post_type ) {
			case 'fmwp_reply':
				global $wpdb;

				$forumwp_topic = $wpdb->get_row( $wpdb->prepare(
					"SELECT * 
					FROM {$wpdb->usermeta} 
					WHERE meta_key = %s",
					'forumwp_reply_' . $post->ID
				) );

				wp_delete_post( $forumwp_topic->meta_value, true );

				delete_user_meta( $forumwp_topic->user_id, 'forumwp_reply_' . $post->ID );
				break;
			case 'fmwp_topic':
				global $wpdb;

				$forumwp_topic = $wpdb->get_row( $wpdb->prepare(
					"SELECT * 
					FROM {$wpdb->usermeta} 
					WHERE meta_key = %s",
					'forumwp_topic_' . $post->ID
				) );

				wp_delete_post( $forumwp_topic->meta_value, true );

				delete_user_meta( $forumwp_topic->user_id, 'forumwp_topic_' . $post->ID );
				break;
		}
	}
}
add_action( 'delete_post', 'um_activity_forumwp_delete_post', 9999, 1 );


/**
 * @param array $posts
 * @param WP_Query $query
 *
 * @return array
 */
function um_activity_forumwp_privacy( $posts, $query ) {
	if ( ! function_exists( 'FMWP' ) ) {
		return $posts;
	}
	if ( FMWP()->is_request( 'admin' ) && ! FMWP()->is_request( 'ajax' ) ) {
		return $posts;
	}

	$filtered_posts = array();

	//if empty
	if ( empty( $posts ) ) {
		return $posts;
	}

	//other filter
	foreach ( $posts as $post ) {

		if ( $post->post_type != 'um_activity' ) {
			$filtered_posts[] = $post;
			continue;
		}

		$action = get_post_meta( $post->ID, '_action', true );
		if ( ! in_array( $action, [ 'new-forumwp-topic', 'new-forumwp-reply' ] ) ) {
			$filtered_posts[] = $post;
			continue;
		}

		$fmwp_post_id = get_post_meta( $post->ID, '_related_id', true );
		if ( empty( $fmwp_post_id ) ) {
			$filtered_posts[] = $post;
			continue;
		}

		if ( $action == 'new-forumwp-topic' ) {
			$topic = get_post( $fmwp_post_id );

			if ( $topic->post_status !== 'publish' ) {
				$can_view = false;
				if ( current_user_can( 'manage_fmwp_topics_all' ) ) {
					if ( $topic->post_status == 'pending' || $topic->post_status == 'private' ) {
						$can_view = true;
					}
				} else {
					if ( $topic->post_status == 'pending' && $topic->post_author == get_current_user_id() ) {
						$can_view = true;
					}
				}
			} else {
				$can_view = true;
			}

			if ( $can_view ) {
				$forum_id = FMWP()->common()->topic()->get_forum_id( $fmwp_post_id );
				$forum = get_post( $forum_id );

				if ( in_array( $forum->post_status, [ 'private', 'pending' ] ) && current_user_can( 'manage_fmwp_forums_all' ) ) {
					$filtered_posts[] = $post;
					continue;
				} elseif ( $forum->post_status == 'publish' ) {
					if ( current_user_can( 'manage_fmwp_forums_all' ) ) {
						$filtered_posts[] = $post;
						continue;
					} else {
						$visibility = get_post_meta( $forum_id, 'fmwp_visibility', true );
						if ( $visibility == 'public' ) {
							$filtered_posts[] = $post;
							continue;
						}
					}
				}
			}
		} elseif ( $action == 'new-forumwp-reply' ) {
			if ( FMWP()->user()->can_view_reply( get_current_user_id(), $fmwp_post_id ) ) {
				$filtered_posts[] = $post;
				continue;
			}
		}
	}

	return $filtered_posts;
}
add_filter( 'the_posts', 'um_activity_forumwp_privacy', 99, 2 );


/* blog post is unpublished */
function um_activity_new_blog_post_undo( $new_status, $old_status, $post ) {
	if ( 'post' !== $post->post_type ) {
		return;
	}

	if ( ! UM()->options()->get('activity-new-post') ) {
		return;
	}

	if ( 'publish' !== $new_status && 'publish' === $old_status ) {
		$args = array(
			'post_type' => 'um_activity',
		);

		$args['meta_query'][] = array('key' => '_related_id','value' => $post->ID,'compare' => '=');
		$args['meta_query'][] = array('key' => '_action','value' => 'new-post','compare' => '=');
		$get = new WP_Query( $args );
		if ( $get->found_posts == 0 ) return;
		foreach( $get->posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}
}
add_action( 'transition_post_status', 'um_activity_new_blog_post_undo', 10, 3 );


/* new blog post */
function um_activity_new_blog_post( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( get_post_type( $post_id ) != 'post' ) {
		return;
	}
	if ( !isset( $_POST['original_post_status'] ) ) {
		return;
	}

	if ( sanitize_key( $_POST['post_status'] ) == 'publish' && sanitize_key( $_POST['original_post_status'] ) == 'publish' ) {
		return;
	}

	if ( ! UM()->options()->get('activity-new-post') ) {
		return;
	}

	$post = get_post( $post_id );
	$user_id = $post->post_author;

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	$post_image = '';
	$post_image_url = UM()->Activity_API()->api()->get_post_image_url( $post_id );
	if ( $post_image_url ) {
		$post_image_alt = basename( $post_image_url );
		$post_image = '<span class="post-image"><img src="' . esc_attr( $post_image_url ) . '" alt="' . esc_attr( $post_image_alt ) . '" title="' . esc_attr( str_replace( "#", '', get_the_title( $post_id ) ) ) . '" class="um-activity-featured-img" /></span>';
	}

	if ( $post->post_content ) {
		$post_excerpt = strip_shortcodes( $post->post_content );
		$post_excerpt = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $post_excerpt);
		
		$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( $post_excerpt, $num_words = 25, $more = null ) . '</span>';
	} else {
		$post_excerpt = '';
	}

	UM()->Activity_API()->api()->save(
		array(
			'template' => 'new-post',
			'wall_id' => $user_id,
			'related_id' => $post_id,
			'author' => $user_id,
			'author_name' => $author_name,
			'author_profile' => $author_profile,
			'post_title' => '<span class="post-title">' . $post->post_title . '</span>',
			'post_url' => get_permalink( $post_id ),
			'post_excerpt' => $post_excerpt,
			'post_image' => $post_image,
		)
	);

}
add_action('publish_post', 'um_activity_new_blog_post');


/**
 * Updated blog post
 *
 * @param $post_id
 * @param $post_after
 * @param $post_before
 *
 * @return void|int
 */
function um_activity_update_blog_post( $post_id, $post_after, $post_before ) {

	if ( 'post' !== $post_before->post_type ) {
		return;
	}

	$args = [
		'post_type'     => 'um_activity',
		'meta_query'    => [
			[
				'key'       => '_related_id',
				'value'     => $post_id,
				'compare'   => '=',
			],
			[
				'key'       => '_action',
				'value'     => 'new-post',
				'compare'   => '=',
			],
		],
	];

	$get = new WP_Query( $args );

	if ( $get->found_posts == 0 ) {
		return;
	}

	foreach ( $get->posts as $post ) {
		$author_id = UM()->Activity_API()->api()->get_author( $post_id );
		$author_data = get_userdata( $author_id );
		$author_name = $author_data->display_name;
		$author_profile = um_user_profile_url( $author_id );

		$post_image = '';
		$post_image_url = UM()->Activity_API()->api()->get_post_image_url( $post_id );
		if ( $post_image_url ) {
			$post_image_alt = basename( $post_image_url );
			$post_image = '<span class="post-image"><img src="' . esc_attr( $post_image_url ) . '" alt="' . esc_attr( $post_image_alt ) . '" title="' . esc_attr( str_replace( "#", '', get_the_title( $post_id ) ) ) . '" class="um-activity-featured-img" /></span>';
		}

		if ( $post_after->post_content ) {
			$post_excerpt = strip_shortcodes( $post_after->post_content );
			$post_excerpt = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $post_excerpt);
			$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( $post_excerpt, $num_words = 25, $more = null ) . '</span>';
		} else {
			$post_excerpt = '';
		}

		$has_updated = UM()->Activity_API()->api()->save(
			array(
				'template'       => 'new-post',
				'author'         => $author_id,
				'author_name'    => $author_name,
				'author_profile' => $author_profile,
				'post_title'     => '<span class="post-title">' . $post_after->post_title . '</span>',
				'post_url'       => get_permalink( $post_after->ID ),
				'post_excerpt'   => $post_excerpt,
				'post_image'     => $post_image,
			),
			true,
			$post->ID
		);

	}
}
add_action( 'post_updated', 'um_activity_update_blog_post', 10, 3 );


/* new product */
function um_activity_new_woo_product( $post_id ) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if ( get_post_type( $post_id ) != 'product' || get_post_status( $post_id ) != 'publish' ) return;

	if ( ! UM()->options()->get('activity-new-product') )
		return;

	$post = get_post($post_id);
	if( $post->post_modified_gmt != $post->post_date_gmt ) return;

	if ( !isset( $_POST['original_post_status'] ) ) return;
	if( sanitize_key( $_POST['post_status'] ) == 'publish' && sanitize_key( $_POST['original_post_status'] ) == 'publish' ) {
		return;
	}

	$product = new WC_Product( $post_id );
	$user_id = $post->post_author;

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	$post_image = '';
	$post_image_url = UM()->Activity_API()->api()->get_post_image_url( $post_id );
	if ( $post_image_url ) {
		$post_image_alt = basename( $post_image_url );
		$post_image = '<span class="post-image"><img src="' . esc_attr( $post_image_url ) . '" alt="' . esc_attr( $post_image_alt ) . '" title="' . esc_attr( str_replace( "#", '', get_the_title( $post_id ) ) ) . '" class="um-activity-featured-img" /></span>';
	}

	if ( $post->post_excerpt ) {
		$post_excerpt = '<span class="post-excerpt">' . strip_tags( apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ) . '</span>';
	} elseif ( $post->post_content ) {
		$post_excerpt = '<span class="post-excerpt">' . wp_trim_words( strip_shortcodes( $post->post_content ), $num_words = 25, $more = null ) . '</span>';
	} else  {
		$post_excerpt = '';
	}

	UM()->Activity_API()->api()->save(
		array(
			'template' => 'new-product',
			'wall_id' => $user_id,
			'author' => $user_id,
			'author_name' => $author_name,
			'author_profile' => $author_profile,
			'post_title' => '<span class="post-title">' . $post->post_title . '</span>',
			'post_url' => get_permalink( $post_id ),
			'post_excerpt' => $post_excerpt,
			'post_image' => $post_image,
			'price' => '<span class="post-price">' . $product->get_price_html() . '</span>',
		)
	);

}
add_action('save_post', 'um_activity_new_woo_product', 99999, 1 );


/**
 * Remove 'deleted forum topic' from the activties
 *
 * @param int $postid
 */
function um_activity_remove_forum_post( $postid ) {
	global $wpdb;

	if ( function_exists( 'bbp_get_topic_post_type' ) ) {
		$post = get_post( $postid );

		if ( $post && ! is_wp_error( $post ) && bbp_get_topic_post_type() == $post->post_type ) {
			$permalink = get_permalink( $post->ID );

			$activities = $wpdb->get_col( $wpdb->prepare(
			"SELECT ID 
				FROM {$wpdb->posts} 
				WHERE post_status='publish' AND 
					  post_content LIKE %s AND 
					  post_content LIKE %s ",
				'%just created a new forum%',
				"%{$permalink}%"
			) );

			if ( ! empty( $activities ) ) {
				foreach ( $activities as $activityId ) {
					wp_delete_post( $activityId );
				}
			}
		}
	}
}
add_action( 'before_delete_post', 'um_activity_remove_forum_post', 10, 1 );


/**
 * Delete user activities on account deletion
 * Delete user activity posts when user is deleted
 *
 * @param int $user_id
 */
function um_activity_delete_user_activities( $user_id ) {
	$user_activities = get_posts( [
		'post_type'     => 'um_activity',
		'numberposts'   => -1,
		'author'        => $user_id,
		'fields'        => 'ids',
	] );

	if ( ! empty( $user_activities ) ) {
		foreach ( $user_activities as $activity_id ) {
			wp_delete_post( $activity_id, true );
		}
	}

	$user_posts = get_posts( [
		'post_type'     => 'um_activity',
		'numberposts'   => -1,
		'meta_query'    => [
			'relation'  => 'OR',
			[
				'key'       => '_user_id',
				'value'     => $user_id,
				'compare'   => '=',
			],
		],
		'fields'        => 'ids',
	] );

	if ( ! empty( $user_posts ) ) {
		foreach ( $user_posts as $user_post_id ) {
			wp_delete_post( $user_post_id, true );
		}
	}
}
add_action( 'um_delete_user', 'um_activity_delete_user_activities', 10, 1 );


/**
 * Submit form answer
 *
 * @param $entry
 * @param $form
 */
function um_gform_after_submission( $entry, $form ) {

	if ( ! UM()->options()->get( 'activity-new-gform-submission' ) )
		return;

	$post_excerpt = '';
	foreach ( $form['fields'] as $field ) {
		if ( ! empty( $entry[$field->id] ) ) {
			$post_excerpt .=  $field->label . ': ' . $entry[$field->id] . '<br />';
		}
	}

	$user_id = $entry['created_by'];

	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	UM()->Activity_API()->api()->save(
		array(
			'template'          => 'new-gform-submission',
			'wall_id'           => $user_id,
			'author'            => $user_id,
			'author_name'       => $author_name,
			'author_profile'    => $author_profile,
			'post_title'        => '<span class="post-title">' . $form['title'] . ' ' . __( 'Answer', 'um-activity' ) . '</span>',
			'post_url'          => $entry['source_url'],
			'post_excerpt'      => $post_excerpt,
		)
	);
}
add_action( 'gform_after_submission', 'um_gform_after_submission', 10, 2 );


/**
 * Create new Gravity Form via wp-admin
 *
 * @param $form_meta
 * @param bool $is_new
 */
function um_activity_new_gform( $form_meta, $is_new ) {

	if ( ! UM()->options()->get( 'activity-new-gform' ) ) {
		return;
	}

	if ( ! $is_new ) {
		return;
	}

	$user_id = get_current_user_id();
	um_fetch_user( $user_id );
	$author_name = um_user('display_name');
	$author_profile = um_user_profile_url();

	UM()->Activity_API()->api()->save(
		array(
			'template'          => 'new-gform',
			'wall_id'           => $user_id,
			'related_id'        => $form_meta['id'],
			'author'            => $user_id,
			'author_name'       => $author_name,
			'author_profile'    => $author_profile,
			'post_title'        => '<span class="post-title">' . $form_meta['title'] . '</span>',
			'post_url'          => get_permalink( $form_meta['id'] ),
			'post_excerpt'      => $form_meta['description'],
			'post_image'        => '',
		)
	);
}
add_action( 'gform_after_save_form', 'um_activity_new_gform', 10, 2 );


/* add activity */
function um_activity_after_user_verified( $user_id ) {
	if ( ! UM()->options()->get( 'activity-verified-account' ) )
		return;

	um_fetch_user( $user_id );
	$author_name = um_user( 'display_name' );
	$author_profile = um_user_profile_url();
	$user_photo = get_avatar( $user_id, 24 );

	$file = um_verified_users_path . 'templates/verified-account.php';
	$theme_file = get_stylesheet_directory() . '/ultimate-member/um-verified-users/verified-account.php';
	if ( file_exists( $theme_file ) ) {
		$file = $theme_file;
	}

	UM()->Activity_API()->api()->save(
		array(
			'template'       => 'verified-account',
			'wall_id'        => 0,
			'author'         => $user_id,
			'author_name'    => $author_name,
			'author_profile' => $author_profile,
			'user_photo'     => $user_photo,
			'related_id'     => $user_id,
			'custom_path'    => $file,
			'verified'       => UM()->Verified_Users_API()->api()->verified_badge()
		)
	);

}
add_action( 'um_after_user_is_verified', 'um_activity_after_user_verified', 90, 1 );

/* remove activity */
function um_activity_after_user_unverified( $user_id ) {
	if ( ! UM()->options()->get( 'activity-verified-account' ) ) {
		return;
	}

	$args = array(
		'post_type' => 'um_activity',
	);

	$args['meta_query'][] = array( 'key' => '_user_id', 'value' => $user_id, 'compare' => '=' );
	$args['meta_query'][] = array( 'key' => '_related_id', 'value' => $user_id, 'compare' => '=' );
	$args['meta_query'][] = array( 'key' => '_action', 'value' => 'verified-account', 'compare' => '=' );
	$get = new WP_Query( $args );
	if ( $get->found_posts == 0 ) {
		return;
	}
	foreach ( $get->posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}
add_action( 'um_after_user_is_unverified', 'um_activity_after_user_unverified', 90, 1 );
