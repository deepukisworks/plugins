<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class um_activity_trending_tags
 */
class um_activity_trending_tags extends WP_Widget {


	/**
	 * um_activity_trending_tags constructor.
	 */
	function __construct() {
		
		parent::__construct(
		
		// Base ID of your widget
		'um_activity_trending_tags', 

		// Widget name will appear in UI
		__( 'Ultimate Member - Trending Hashtags', 'um-activity' ),

		// Widget description
		array( 'description' => __( 'Shows your trending hashtags', 'um-activity' ), ) 
		
		);
	
	}


	/**
	 * Creating widget front-end
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ( ! empty( $_GET['legacy-widget-preview'] ) && defined( 'IFRAME_REQUEST' ) && IFRAME_REQUEST ) {
			return;
		}

		UM()->Activity_API()->enqueue()->enqueue_scripts();

		$title = apply_filters( 'widget_title', $instance['title'] );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		// This is where you run the code and display the output
		if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) {
			echo do_shortcode('[ultimatemember_trending_hashtags /]');
		} else {
			echo apply_shortcodes('[ultimatemember_trending_hashtags /]');
		}
		echo $args['after_widget'];
	}


	/**
	 * Widget Backend
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Trending', 'um-activity' );
		} ?>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'um-activity' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<?php 
	}


	/**
	 * Updating widget replacing old instances with new
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}