<?php

/**
 * Functionality related to the Customizer.
 */

/**
 * Set up Customizer panels.
 */
function openlab_customizer_setup( $wp_customize ) {
	// Color Scheme
	$wp_customize->remove_section( 'colors' );
	$wp_customize->add_section( 'openlab_section_color_scheme', array(
		'title' => __( 'Color Scheme', 'openlab-theme' ),
	) );

	$wp_customize->add_setting( 'openlab_color_scheme', array(
		'type' => 'theme_mod',
		'default' => 'blue',
		'sanitize_callback' => 'openlab_sanitize_customizer_setting_color_scheme',
	) );

	$color_schemes = openlab_color_schemes();
	$color_scheme_choices = array();
	foreach ( $color_schemes as $color_scheme => $color_scheme_data ) {
		$color_scheme_choices[ $color_scheme ] = $color_scheme_data['label'];
	}

	$wp_customize->add_control(
		'openlab_color_scheme',
		array(
			'label' => __( 'Color Scheme', 'openlab-theme' ),
			'section' => 'openlab_section_color_scheme',
			'type' => 'radio',
			'choices' => $color_scheme_choices,
			'default' => 'blue',
		)
	);

	// Logo
	$wp_customize->add_section( 'openlab_logo', array(
		'title' => __( 'Logo', 'openlab-theme' ),
	) );

	$wp_customize->add_setting( 'openlab_logo', array(
		'type' => 'theme_mod',
		'sanitize_callback' => 'openlab_sanitize_customizer_setting_intval',
	) );

	$wp_customize->add_control(
		new WP_Customize_Cropped_Image_Control(
			$wp_customize,
			'openlab_logo',
			array(
				'label'         => __( 'Logo', 'openlab-theme' ),
				'section'       => 'openlab_logo',
				'height'        => 63,
				'width'         => 185,
				'flex_height'   => false,
				'flex_width'    => true,
				'button_labels' => array(
					'select'       => __( 'Select logo' ),
					'change'       => __( 'Change logo' ),
					'remove'       => __( 'Remove' ),
					'default'      => __( 'Default' ),
					'placeholder'  => __( 'No logo selected' ),
					'frame_title'  => __( 'Select logo' ),
					'frame_button' => __( 'Choose logo' ),
				),
			)
		)
	);

	$wp_customize->selective_refresh->add_partial( 'openlab_logo', array(
		'settings'            => array( 'openlab_logo' ),
		'selector'            => '.custom-logo-link',
		'render_callback'     => 'openlab_get_logo_html',
		'container_inclusive' => true,
	) );

	// Home Page
	$wp_customize->add_panel( 'openlab_home_page', array(
		'title' => __( 'Home Page', 'openlab-theme' ),
	) );

	global $wp_registered_sidebars;
	$openlab_sidebars = array( 'home-main', 'home-sidebar' );
	foreach ( $openlab_sidebars as $sidebar_id ) {
		$sid = 'sidebar-widgets-' . $sidebar_id;
		$section = $wp_customize->get_section( $sid );

		if ( ! $section ) {
			continue;
		}

		$c = clone( $section );
		$wp_customize->remove_section( $sid );

		$c->panel = 'openlab_home_page';
		$wp_customize->add_section( $c );
	}

	// Footer
	$footer_section = $wp_customize->get_section( 'sidebar-widgets-footer' );
	if ( $footer_section ) {
		$c = clone( $footer_section );
		$wp_customize->remove_section( 'sidebar-widgets-footer' );

		$c->panel = '';
		$c->priority = 160;
		$wp_customize->add_section( $c );
	}
}
add_action( 'customize_register', 'openlab_customizer_setup', 200 );

function openlab_customizer_styles() {
	$color_schemes = openlab_color_schemes();
	?>
	<style type="text/css">
		#customize-control-openlab_color_scheme label {
			display: block;
			height: 50px;
		}

		#customize-control-openlab_color_scheme label::before {
			border: 1px solid #666;
			border-radius: 50%;
			content: '';
			display: block;
			float: right;
			height: 25px;
			margin-right: 20px;
			margin-top: -4px;
			width: 25px;
		}

		<?php foreach ( $color_schemes as $color_scheme => $color_scheme_data ) : ?>
			<?php printf(
				"\n" . '#customize-control-openlab_color_scheme label.color-scheme-%s::before {
					background-color: %s
				}',
				esc_attr( $color_scheme ),
				esc_attr( $color_scheme_data['icon_color'] )
			); ?>
		<?php endforeach; ?>
	</style>
	<?php
}
add_action( 'customize_controls_print_styles', 'openlab_customizer_styles' );

function openlab_customizer_scripts() {
	wp_enqueue_script( 'openlab-theme-customizer', get_stylesheet_directory_uri() . '/js/customizer.js', array( 'customize-controls' ) );
}
add_action( 'customize_controls_enqueue_scripts', 'openlab_customizer_scripts' );

function openlab_sanitize_customizer_setting_color_scheme( $setting ) {
	$color_schemes = openlab_color_schemes();

	if ( ! isset( $color_schemes[ $setting ] ) ) {
		$setting = 'blue';
	}

	return $setting;
}

/**
 * Can't pass directly to intval() because Customizer passes more than one param.
 */
function openlab_sanitize_customizer_setting_intval( $setting ) {
	return intval( $setting );
}