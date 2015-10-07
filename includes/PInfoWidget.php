<?php

/**
 * Protein Info Widget
 * User: mbikyaw
 * Date: 5/10/15
 */
class PInfoWidget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'PInfoWidget', // Base ID
			__( 'Protein Info', 'text_domain' ), // Name
			array( 'description' => __( 'Display protein information', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo '<h4>Protein Info</h4>';

	}
}

// register Foo_Widget widget
function register_PInfoWidget() {
	register_widget( 'PInfoWidget' );
}
add_action( 'widgets_init', 'register_PInfoWidget' );
