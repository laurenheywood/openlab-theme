<?php

/**
 * Load custom widgets.
 *
 * @since 1.0.0
 */

// Register custom widgets.
add_action( 'widgets_init', 'openlab_register_widgets' );

/**
 * Register custom widgets.
 *
 * @since 1.0.0
 */
function openlab_register_widgets() {
	$widgets_dir = get_template_directory() . '/lib/widgets/';

	if ( function_exists( 'buddypress' ) ) {
		require_once( $widgets_dir . 'whats-happening.php' );
		register_widget( 'OpenLab_WhatsHappening_Widget' );

		$group_types = cboxol_get_group_types();
		if ( $group_types ) {
			require_once( $widgets_dir . 'group-type.php' );
			foreach ( $group_types as $group_type ) {
				$widget = new OpenLab_Group_Type_Widget( $group_type );
				register_widget( $widget );
			}
		}
	}
}