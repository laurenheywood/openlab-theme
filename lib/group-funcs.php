<?php
/**
 * Library of group-related functions
 */

/**
 * Custom template loader for my-{grouptype}
 */
function openlab_mygroups_template_loader( $template ) {
	if ( is_page() ) {
		switch ( get_query_var( 'pagename' ) ) {
			case 'my-courses' :
			case 'my-clubs' :
			case 'my-projects' :
				get_template_part( 'buddypress/groups/index' );
				break;
		}
	}

	return $template;
}

add_filter( 'template_include', 'openlab_mygroups_template_loader' );

/**
 * This function consolidates the group privacy settings in one spot for easier updating
 */
function openlab_group_privacy_settings( $group_type ) {
	global $bp;

	// If this is a cloned group/site, fetch the clone source's details
	$clone_source_group_status = $clone_source_blog_status = '';
	if ( bp_is_group_create() ) {
		$new_group_id = bp_get_new_group_id();
		if ( 'course' === $group_type ) {
			$clone_source_group_id = groups_get_groupmeta( $new_group_id, 'clone_source_group_id' );
			$clone_source_site_id = groups_get_groupmeta( $new_group_id, 'clone_source_blog_id' );

			$clone_source_group = groups_get_group( array( 'group_id' => $clone_source_group_id ) );
			$clone_source_group_status = $clone_source_group->status;

			$clone_source_blog_status = get_blog_option( $clone_source_site_id, 'blog_public' );
		}
	}
	?>

	<div class="panel panel-default">
		<div class="panel-heading semibold"><?php esc_html_e( 'Privacy Settings', 'openlab-theme' ); ?></div>

		<div class="radio group-profile panel-body">

			<?php if ( bp_is_group_create() ) : ?>
				<p id="privacy-settings-tag-b"><?php esc_html_e( 'These settings affect how others view your group\'s Profile.', 'openlab-theme' ); ?> <?php esc_html_e( 'You may change these settings later in the group\'s Profile Settings.', 'openlab-theme' ); ?></p>
			<?php else : ?>
				<p class="privacy-settings-tag-c"><?php esc_html_e( 'These settings affect how others view your group\'s Profile.', 'openlab-theme' ); ?></p>
			<?php endif; ?>

			<?php
			$new_group_status = bp_get_new_group_status();
			if ( ! $new_group_status ) {
				$new_group_status = ! empty( $clone_source_group_status ) ? $clone_source_group_status : 'public';
			}
			?>
			<div class="row">
				<div class="col-sm-23 col-sm-offset-1">
					<label><input type="radio" name="group-status" value="public" <?php checked( 'public', $new_group_status ) ?> /><?php esc_html_e( 'Public', 'openlab-theme' ); ?></label>
					<ul>
						<li><?php esc_html_e( 'Profile and related content and activity will be visible to the public.', 'openlab-theme' ); ?></li>
						<li><?php printf( esc_html__( 'Will be listed in the "%s" directory, in search results, and may be displayed on the home page.', 'openlab-theme' ), esc_html( $group_type->get_label( 'plural' ) ) ); ?></li>
						<li><?php _e( 'Any site member may join this group.', 'openlab-theme' ); ?></li>
					</ul>

					<label><input type="radio" name="group-status" value="private" <?php checked( 'private', $new_group_status ) ?> /><?php esc_html_e( 'Private', 'openlab-theme' ) ?></label>
					<ul>
						<li><?php esc_html_e( 'Profile and related content and activity will only be visible to members of the group.', 'buddypress' ) ?></li>
						<li><?php printf( esc_html__( 'Will be listed in the "%s" directory, in search results, and may be displayed on the home page.', 'openlab-theme' ), esc_html( $group_type->get_label( 'plural' ) ) ); ?></li>
						<li><?php esc_html_e( 'Only site members who request membership and are accepted may join this group.', 'openlab-theme' ) ?></li>
					</ul>

					<label><input type="radio" name="group-status" value="hidden" <?php checked( 'hidden', $new_group_status ) ?> /><?php esc_html_e( 'Hidden', 'openlab-theme' ) ?></label>
					<ul>
						<li><?php esc_html_e( 'Profile, related content, and activity will only be visible only to members of the group.', 'openlab-theme' ) ?></li>
						<li><?php printf( esc_html__( 'Will NOT be listed in the "%s" directory, in search results, or on the home page.', 'openlab-theme' ), esc_html( $group_type->get_label( 'plural' ) ) ); ?></li>
						<li><?php esc_html_e( 'Only site members who are invited may join this group.', 'openlab-theme' ) ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<?php /* Site privacy markup */ ?>

	<?php if ( $site_id = openlab_get_site_id_by_group_id() ) : ?>
		<div class="panel panel-default">
			<div class="panel-heading semibold"><?php esc_html_e( 'Associated Site', 'openlab-theme' ) ?></div>
			<div class="panel-body">
				<p class="privacy-settings-tag-c"><?php esc_html_e( 'These settings affect how others view your associated site.', 'openlab-theme' ) ?></p>
				<?php openlab_site_privacy_settings_markup( $site_id ) ?>
			</div>
		</div>
	<?php endif ?>

	<?php if ( $bp->current_action == 'admin' ) : ?>
		<?php do_action( 'bp_after_group_settings_admin' ); ?>
		<p><input class="btn btn-primary" type="submit" value="<?php esc_html_e( 'Save Changes', 'openlab-theme' ) ?> &#xf138;" id="save" name="save" /></p>
		<?php wp_nonce_field( 'groups_edit_group_settings' ); ?>
	<?php elseif ( $bp->current_action == 'create' ) : ?>
		<?php wp_nonce_field( 'groups_create_save_group-settings' ) ?>
		<?php
	endif;
}

function openlab_groups_pagination_links() {
	global $groups_template;

	$base = add_query_arg( array(
		'grpage' => '%#%',
		'num' => $groups_template->pag_num,
		'sortby' => $groups_template->sort_by,
		'order' => $groups_template->order,
	) );

	if ( isset( $_GET['search'] ) ) {
		$base = add_query_arg( 's', urldecode( wp_unslash( $_GET['search'] ) ), $base );
	}

	$pagination = paginate_links(array(
		'base' => $base,
		'format' => '',
		'total' => ceil( (int) $groups_template->total_group_count / (int) $groups_template->pag_num ),
		'current' => $groups_template->pag_page,
		'prev_text' => _x( '<i class="fa fa-angle-left" aria-hidden="true"></i><span class="sr-only">Previous</span>', 'Group pagination previous text', 'buddypress' ),
		'next_text' => _x( '<i class="fa fa-angle-right" aria-hidden="true"></i><span class="sr-only">Next</span>', 'Group pagination next text', 'buddypress' ),
		'mid_size' => 3,
		'type' => 'list',
		'before_page_number' => '<span class="sr-only">Page</span>',
	));

	$pagination = str_replace( 'page-numbers', 'page-numbers pagination', $pagination );

	// for screen reader only text - current page
	$pagination = str_replace( 'current\'><span class="sr-only">Page', 'current\'><span class="sr-only">Current Page', $pagination );

	return $pagination;
}

function openlab_forum_pagination() {
	global $forum_template;

	$pagination = paginate_links(array(
		'base' => add_query_arg( array( 'p' => '%#%', 'n' => $forum_template->pag_num ) ),
		'format' => '',
		'total' => ceil( (int) $forum_template->total_topic_count / (int) $forum_template->pag_num ),
		'current' => $forum_template->pag_page,
		'prev_text' => _x( '<i class="fa fa-angle-left" aria-hidden="true"></i>', 'Forum pagination previous text', 'buddypress' ),
		'next_text' => _x( '<i class="fa fa-angle-right" aria-hidden="true"></i>', 'Forum pagination next text', 'buddypress' ),
		'mid_size' => 3,
		'type' => 'list',
	));

	$pagination = str_replace( 'page-numbers', 'page-numbers pagination', $pagination );
	return $pagination;
}

/*
 * Redirect to users profile after deleting a group
 */
add_action( 'groups_group_deleted', 'openlab_delete_group', 20 );

/**
 * After portfolio delete, redirect to user profile page
 */
function openlab_delete_group() {
	bp_core_redirect( bp_loggedin_user_domain() );
}

/**
 * This function prints out the departments for the course archives ( non ajax )
 *
 * @param string $school The id of the school to return a course list for
 * @param string $department The id of the deparment currently selected in
 *        the dropdown.
 */
function openlab_return_course_list( $school, $department ) {

	$list = '<option value="dept_all" ' . selected( '', $department ) . ' >All Departments</option>';

	// Sanitize. If no value is found, don't return any
	// courses
	if ( ! in_array( $school, array( 'tech', 'studies', 'arts' ) ) ) {
		return $list;
	}

	$depts = openlab_get_department_list( $school, 'short' );

	foreach ( $depts as $dept_name => $dept_label ) {
		$list .= '<option value="' . esc_attr( $dept_name ) . '" ' . selected( $department, $dept_name, false ) . '>' . esc_attr( $dept_label ) . '</option>';
	}

	return $list;
}

// a variation on bp_groups_pagination_count() to match design
function cuny_groups_pagination_count() {
	global $bp, $groups_template;

	$start_num = intval( ( $groups_template->pag_page - 1 ) * $groups_template->pag_num ) + 1;
	$from_num = bp_core_number_format( $start_num );
	$to_num = bp_core_number_format( ( $start_num + ( $groups_template->pag_num - 1 ) > $groups_template->total_group_count ) ? $groups_template->total_group_count : $start_num + ( $groups_template->pag_num - 1 ) );
	$total = bp_core_number_format( $groups_template->total_group_count );

	/* @todo Proper localization with _n() */
	echo sprintf( __( '%1$s to %2$s (of %3$s total)', 'openlab-theme' ), $from_num, $to_num, $total );
}

/**
 * Markup for groupblog privacy settings
 */
function openlab_site_privacy_settings_markup( $site_id = 0 ) {
	global $blogname, $current_site;

	if ( ! $site_id ) {
		$site_id = get_current_blog_id();
	}

	$blog_name = get_blog_option( $site_id, 'blogname' );
	$blog_public = get_blog_option( $site_id, 'blog_public' );
	?>

	<div class="radio group-site">

		<h5><?php _e( 'Public', 'buddypress' ) ?></h5>
		<p id="search-setting-note" class="italics note"><?php esc_html_e( 'Note: These options will NOT block access to your site. It is up to search engines to honor your request.', 'openlab-theme' ); ?></p>
		<div class="row">
			<div class="col-sm-23 col-sm-offset-1">
				<p><label for="blog-private1"><input id="blog-private1" type="radio" name="blog_public" value="1" <?php checked( '1', $blog_public ); ?> /><?php _e( 'Allow search engines to index this site. Your site will show up in web search results.', 'openlab-theme' ); ?></label></p>

				<p><label for="blog-private0"><input id="blog-private0" type="radio" name="blog_public" value="0" <?php checked( '0', $blog_public ); ?> /><?php _e( 'Ask search engines not to index this site. Your site should not show up in web search results.', 'openlab-theme' ); ?></label></p>
			</div>
		</div>

		<?php if ( ! cboxol_is_portfolio() && ( ! isset( $_GET['group_type'] ) || 'portfolio' != $_GET['group_type'] ) ) : ?>

			<h5><?php esc_html_e( 'Private', 'openlab-theme' ) ?></h5>
			<div class="row">
				<div class="col-sm-23 col-sm-offset-1">
					<p><label for="blog-private-1"><input id="blog-private-1" type="radio" name="blog_public" value="-1" <?php checked( '-1', $blog_public ); ?>><?php esc_html_e( 'I would like my site to be visible only to members of the network.', 'openlab-theme' ); ?></label></p>

					<p><label for="blog-private-2"><input id="blog-private-2" type="radio" name="blog_public" value="-2" <?php checked( '-2', $blog_public ); ?>><?php esc_html_e( 'I would like my site to be visible to users with a role on the Site.', 'openlab-theme' ); ?></label></p>
				</div>
			</div>

			<h5><?php esc_html_e( 'Hidden', 'openlab-theme' ) ?></h5>
			<div class="row">
				<div class="col-sm-23 col-sm-offset-1">
					<p><label for="blog-private-3"><input id="blog-private-3" type="radio" name="blog_public" value="-3" <?php checked( '-3', $blog_public ); ?>><?php esc_html_e( 'I would like my site to be visible only to site administrators.' ); ?></label></p>
				</div>
			</div>

		<?php else : ?>

			<?php /* Portfolios */ ?>
			<h5><?php esc_html_e( 'Private', 'openlab-theme' ); ?></h5>
			<div class="row">
				<div class="col-sm-23 col-sm-offset-1">
					<p><label for="blog-private-1"><input id="blog-private-1" type="radio" name="blog_public" value="-1" <?php checked( '-1', $blog_public ); ?>><?php esc_html_e( 'I would like my site to be visible only to registered users of the network.', 'openlab-theme' ); ?></label></p>

					<p><label for="blog-private-2"><input id="blog-private-2" type="radio" name="blog_public" value="-2" <?php checked( '-2', $blog_public ); ?>><?php esc_html_e( 'I would like my site to be visible only to registered users that I have granted access.', 'openlab-theme' ); ?></label></p>
					<p class="description private-portfolio-gloss italics note"><?php esc_html_e( 'Note: If you would like your site to be visible to people who are not members of this network, you will need to make your site public.', 'openlab-theme' ); ?></p>

					<p><label for="blog-private-3"><input id="blog-private-3" type="radio" name="blog_public" value="-3" <?php checked( '-3', $blog_public ); ?>><?php esc_html_e( 'I would like my site to be visible only to me.', 'openlab-theme' ); ?></label></p>
				</div>
			</div>

		<?php endif; ?>
	</div>
	<?php
}

function openlab_group_profile_header() {
	global $bp;
	$group_type = cboxol_get_group_group_type( bp_get_current_group_id() );

	?>
	<h1 class="entry-title group-title clearfix"><span class="profile-name hyphenate"><?php echo bp_group_name(); ?></span>
		<span class="profile-type pull-right hidden-xs"><?php echo esc_html( $group_type->get_label( 'singular' ) ); ?></span>
		<button data-target="#sidebar-menu-wrapper" data-backgroundonly="true" class="mobile-toggle direct-toggle pull-right visible-xs" type="button">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button></h1>
	<?php if ( bp_is_group_home() || (bp_is_group_admin_page() && ! $bp->is_item_admin) ) : ?>
		<div class="clearfix">
			<?php if ( ! $group_type->get_is_portfolio() ) : ?>
				<div class="info-line pull-right"><span class="timestamp info-line-timestamp"><span class="fa fa-undo" aria-hidden="true"></span> <?php printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() ) ?></span></div>
			<?php endif; ?>
		</div>
	<?php elseif ( bp_is_group_home() ) : ?>
		<div class="clearfix visible-xs">
			<?php if ( ! $group_type->get_is_portfolio() ) : ?>
				<div class="info-line pull-right"><span class="timestamp info-line-timestamp"><span class="fa fa-undo" aria-hidden="true"></span> <?php printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() ) ?></span></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php
}

add_action( 'bp_before_group_body', 'openlab_group_profile_header' );

function openlab_get_privacy_icon() {

	switch ( bp_get_group_status() ) {
		case 'public':
			$status = '<span class="fa fa-eye" aria-hidden="true"></span>';
			break;
		case 'private':
			$status = '<span class="fa fa-lock" aria-hidden="true"></span>';
			break;
		case 'hidden':
			$status = '<span class="fa fa-eye-slash" aria-hidden="true"></span>';
			break;
		default:
			$status = '<span class="fa fa-eye" aria-hidden="true"></span>';
	}

	return $status;
}

function cuny_group_single() {
	$group_type = cboxol_get_group_group_type( bp_get_current_group_id() );
	$group_slug = bp_get_group_slug();

	// group page vars
	global $bp, $wpdb;
	$group_id = $bp->groups->current_group->id;
	$group_name = $bp->groups->current_group->name;
	$group_description = $bp->groups->current_group->description;
	$html = groups_get_groupmeta( $group_id, 'cboxol_additional_desc_html' );

	$academic_unit_data = cboxol_get_object_academic_unit_data_for_display( array(
		'object_id' => $group_id,
		'object_type' => 'group',
	) );
	?>

	<div class="wrapper-block visible-xs sidebar mobile-group-site-links">
		<?php openlab_bp_group_site_pages( true ); ?>
	</div>

	<?php if ( bp_is_group_home() ) : ?>
		<div id="<?php echo esc_attr( $group_type->get_slug() ); ?>-header" class="group-header row">

			<div id="<?php echo esc_attr( $group_type->get_slug() ); ?>-header-avatar" class="alignleft group-header-avatar col-sm-8 col-xs-12">
				<div class="padded-img darker">
					<img class="img-responsive" src ="<?php echo bp_core_fetch_avatar( array( 'item_id' => $group_id, 'object' => 'group', 'type' => 'full', 'html' => false ) ) ?>" alt="<?php echo esc_attr( $group_name ); ?>"/>
				</div>

				<?php if ( is_user_logged_in() && $bp->is_item_admin ) : ?>
					<div id="group-action-wrapper">
						<a class="btn btn-default btn-block btn-primary link-btn" href="<?php echo bp_group_permalink() . 'admin/edit-details/'; ?>"><i class="fa fa-pencil" aria-hidden="true"></i> <?php esc_html_e( 'Edit Settings', 'openlab-theme' ); ?></a>
						<a class="btn btn-default btn-block btn-primary link-btn" href="<?php echo bp_group_permalink() . 'admin/group-avatar/'; ?>"><i class="fa fa-camera" aria-hidden="true"></i> <?php esc_html_e( 'Change Avatar', 'openlab-theme' ); ?></a>
					</div>
				<?php elseif ( is_user_logged_in() ) : ?>
					<div id="group-action-wrapper">
						<?php do_action( 'bp_group_header_actions' ); ?>
					</div>
				<?php endif; ?>
				<?php openlab_render_message(); ?>
		</div><!-- #<?php echo esc_html( $group_type->get_slug() ) ?>-header-avatar -->

			<div id="<?php echo esc_attr( $group_type->get_slug() ); ?>-header-content" class="col-sm-16 col-xs-24 alignleft group-header-content group-<?php echo $group_id; ?>">

				<?php do_action( 'bp_before_group_header_meta' ) ?>

				<?php if ( $group_type->get_is_course() ) : ?>
					<div class="info-panel panel panel-default no-margin no-margin-top">
						<?php
						$course_code = groups_get_groupmeta( $group_id, 'cboxol_course_code' );
						$section_code = groups_get_groupmeta( $group_id, 'cboxol_section_code' );
						$term = openlab_get_group_term( $group_id );
						?>
						<div class="table-div">
							<?php
							if ( bp_is_group_home() && openlab_group_status_message() != '' ) {

								do_action( 'bp_before_group_status_message' )
								?>

								<div class="table-row row">
									<div class="col-xs-24 status-message italics"><?php echo openlab_group_status_message() ?></div>
								</div>

								<?php
								do_action( 'bp_after_group_status_message' );
							}
							?>

							<?php foreach ( $academic_unit_data as $type => $type_data ) : ?>
								<div class="table-row row">
									<div class="bold col-sm-7">
										<?php echo esc_html( $type_data['label'] ); ?>
									</div>

									<div class="col-sm-17">
										<?php echo esc_html( $type_data['value'] ); ?>
									</div>
								</div>
							<?php endforeach; ?>

							<div class="table-row row">
								<div class="bold col-sm-7"><?php esc_html_e( 'Professor(s)', 'openlab-theme' ); ?></div>
								<div class="col-sm-17 row-content"><?php echo openlab_get_faculty_list() ?></div>
							</div>

							<?php if ( $course_code ) : ?>
								<div class="table-row row">
									<div class="bold col-sm-7"><?php echo esc_html( $group_type->get_label( 'course_code' ) ); ?></div>
									<div class="col-sm-17 row-content"><?php echo esc_html( $course_code ); ?></div>
								</div>
							<?php endif; ?>

							<?php if ( $section_code ) : ?>
								<div class="table-row row">
									<div class="bold col-sm-7"><?php echo esc_html( $group_type->get_label( 'section_code' ) ); ?></div>
									<div class="col-sm-17 row-content"><?php echo esc_html( $section_code ); ?></div>
								</div>
							<?php endif; ?>

							<?php if ( $term ) : ?>
								<div class="table-row row">
									<div class="bold col-sm-7"><?php esc_html_e( 'Semester / Year', 'openlab-theme' ); ?></div>
									<div class="col-sm-17 row-content"><?php echo esc_html( $term ); ?></div>
								</div>
							<?php endif; ?>
							<?php if ( function_exists( 'bpcgc_get_group_selected_terms' ) ) : ?>
								<?php if ( $group_terms = bpcgc_get_group_selected_terms( $group_id, true ) ) : ?>
									<div class="table-row row">
										<div class="bold col-sm-7"><?php esc_html_e( 'Category', 'openlab-theme' ); ?></div>
										<div class="col-sm-17 row-content"><?php echo implode( ', ', wp_list_pluck( $group_terms, 'name' ) ); ?></div>
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<div class="table-row row">
								<div class="bold col-sm-7"><?php esc_html_e( 'Course Description', 'openlab-theme' ); ?></div>
								<div class="col-sm-17 row-content"><?php echo apply_filters( 'the_content', $group_description ); ?></div>
							</div>
						</div>

					</div>

					<?php do_action( 'bp_group_header_meta' ) ?>

				<?php else : ?>

					<div class="info-panel panel panel-default no-margin no-margin-top">
						<div class="table-div">
							<div class="table-row row">
								<div class="col-xs-24 status-message italics"><?php echo openlab_group_status_message() ?></div>
							</div>

							<?php foreach ( $academic_unit_data as $type => $type_data ) : ?>
								<div class="table-row row">
									<div class="bold col-sm-7">
										<?php echo esc_html( $type_data['label'] ); ?>
									</div>

									<div class="col-sm-17">
										<?php echo esc_html( $type_data['value'] ); ?>
									</div>
								</div>
							<?php endforeach; ?>

							<?php
							$group_contacts = groups_get_groupmeta( $group_id, 'group_contact', false );
							?>

							<?php if ( $group_contacts ) : ?>
								<div class="table-row row">
									<div class="bold col-sm-7"><?php echo _n( 'Group Contact', 'Group Contacts', count( $group_contacts ), 'openlab-theme' ); ?></div>
									<div class="col-sm-17 row-content"><?php echo implode( ', ', array_map( 'bp_core_get_userlink', $group_contacts ) ); ?></div>
								</div>
							<?php endif; ?>

							<?php if ( function_exists( 'bpcgc_get_group_selected_terms' ) ) : ?>
								<?php if ( $group_terms = bpcgc_get_group_selected_terms( $group_id, true ) ) : ?>
									<div class="table-row row">
										<div class="bold col-sm-7">Category</div>
										<div class="col-sm-17 row-content"><?php echo implode( ', ', wp_list_pluck( $group_terms, 'name' ) ); ?></div>
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<div class="table-row row">
								<div class="bold col-sm-7"><?php esc_html_e( 'Description', 'openlab-theme' ); ?></div>
								<div class="col-sm-17 row-content"><?php bp_group_description() ?></div>
							</div>

							<?php if ( $group_type->get_is_portfolio() ) : ?>

								<div class="table-row row">
									<div class="bold col-sm-7"><?php esc_html_e( 'Member Profile', 'openlab-theme' ); ?></div>
									<div class="col-sm-17 row-content"><?php echo bp_core_get_userlink( openlab_get_user_id_from_portfolio_group_id( bp_get_group_id() ) ); ?></div>
								</div>

							<?php endif; ?>

						</div>
					</div>

				<?php endif; ?>
			</div><!-- .header-content -->

			<?php do_action( 'bp_after_group_header' ) ?>

		</div><!--<?php echo esc_html( $group_type->get_slug() ); ?>-header -->

	<?php endif; ?>

	<?php
	openlab_group_profile_activity_list();
}

function openlab_render_message() {
	global $bp;

	if ( ! empty( $bp->template_message ) ) :
		$type = ( 'success' == $bp->template_message_type ) ? 'updated' : 'error';
		$content = apply_filters( 'bp_core_render_message_content', $bp->template_message, $type );
		?>

		<div id="message" class="bp-template-notice <?php echo $type; ?> btn btn-default btn-block btn-primary link-btn clearfix">

			<span class="pull-left fa fa-check" aria-hidden="true"></span>
			<?php echo $content; ?>

		</div>

		<?php
		do_action( 'bp_core_render_message' );

	endif;
}

function openlab_group_profile_activity_list() {
	global $wpdb, $bp;
	?>
	<div id="single-course-body">
		<?php
		//
		// control the formatting of left and right side by use of variable $first_class.
		// when it is "first" it places it on left side, when it is "" it places it on right side
		//
		// Initialize it to left side to start with
		//
		$first_class = 'first';
		?>
		<?php $group_slug = bp_get_group_slug(); ?>
		<?php $group_type = cboxol_get_group_group_type( bp_get_current_group_id() ); ?>

		<?php
		$group = groups_get_current_group();
		?>

		<?php if ( bp_is_group_home() ) { ?>

			<?php if ( bp_get_group_status() == 'public' || ( ( bp_get_group_status() == 'hidden' || bp_get_group_status() == 'private' ) && ( bp_is_item_admin() || bp_group_is_member() ) ) ) : ?>
				<?php
				if ( cboxol_site_can_be_viewed() ) {
					openlab_show_site_posts_and_comments();
				}
				?>

				<?php if ( ! $group_type->get_is_portfolio() ) : ?>
					<div class="row group-activity-overview">
						<div class="col-sm-12">
							<div class="recent-discussions">
								<div class="recent-posts">
									<h2 class="title activity-title"><a class="no-deco" href="<?php site_url(); ?>/groups/<?php echo $group_slug; ?>/forum/"><?php esc_html_e( 'Recent Discussions', 'openlab-theme' ); ?><span class="fa fa-chevron-circle-right" aria-hidden="true"></span></a></h2>
									<?php
									$forum_id = null;
									$forum_ids = bbp_get_group_forum_ids( bp_get_current_group_id() );

									// Get the first forum ID
									if ( ! empty( $forum_ids ) ) {
										$forum_id = (int) is_array( $forum_ids ) ? $forum_ids[0] : $forum_ids;
									}
									?>

									<?php if ( $forum_id && bbp_has_topics( 'posts_per_page=3&post_parent=' . $forum_id ) ) : ?>
										<?php while ( bbp_topics() ) : bbp_the_topic(); ?>


											<div class="panel panel-default">
												<div class="panel-body">

													<?php
													$topic_id = bbp_get_topic_id();
													$last_reply_id = bbp_get_topic_last_reply_id( $topic_id );

													// Oh, bbPress.
													$last_reply = get_post( $last_reply_id );
													if ( ! empty( $last_reply->post_content ) ) {
														$last_topic_content = bp_create_excerpt( strip_tags( $last_reply->post_content ), 250, array(
															'ending' => '',
														) );
													}
													?>

													<?php echo openlab_get_group_activity_content( bbp_get_topic_title(), $last_topic_content, bbp_get_topic_permalink() ) ?>

												</div></div>                                            <?php endwhile; ?>
									<?php else : ?>
										<div class="panel panel-default"><div class="panel-body">
												<p><?php _e( 'Sorry, there were no discussion topics found.', 'buddypress' ) ?></p>
											</div></div>
									<?php endif; ?>
								</div><!-- .recent-post -->
							</div>
						</div>
						<?php $first_class = ''; ?>
						<div class="col-sm-12">
							<div id="recent-docs">
								<div class="recent-posts">
									<h2 class="title activity-title"><a class="no-deco" href="<?php site_url(); ?>/groups/<?php echo $group_slug; ?>/docs/"><?php esc_html_e( 'Recent Docs', 'openlab-theme' ); ?><span class="fa fa-chevron-circle-right" aria-hidden="true"></span></a></h2>
									<?php
									$docs_arg = array(
										'posts_per_page' => '3',
										'post_type' => 'bp_doc',
										'tax_query' =>
										array(
											array(
												'taxonomy' => 'bp_docs_associated_item',
												'field' => 'slug',
												'terms' => $group_slug,
											),
										),
									);
									$query = new WP_Query( $docs_arg );
									// $query = new WP_Query( "posts_per_page=3&post_type=bp_doc&category_name=$group_slug" );
									// $query = new WP_Query( "posts_per_page=3&post_type=bp_doc&category_name=$group_id" );
									global $post;
									if ( $query->have_posts() ) {
										while ( $query->have_posts() ) : $query->the_post();
											?>
											<div class="panel panel-default"><div class="panel-body">
													<?php echo openlab_get_group_activity_content( get_the_title(), bp_create_excerpt( strip_tags( $post->post_content ), 250, array( 'ending' => '' ) ), site_url() . '/groups/' . $group_slug . '/docs/' . $post->post_name ); ?>
												</div></div>
											<?php
										endwhile;
									} else {
										echo '<div class="panel panel-default"><div class="panel-body"><p>No Recent Docs</p></div></div>';
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<div id="members-list" class="info-group">

						<?php
						if ( $bp->is_item_admin || $bp->is_item_mod ) :
							$href = site_url() . '/groups/' . $group_slug . '/admin/manage-members/';
						else :
							$href = site_url() . '/groups/' . $group_slug . '/members/';
						endif;
						?>

						<h2 class="title activity-title"><a class="no-deco" href="<?php echo $href; ?>">Members<span class="fa fa-chevron-circle-right" aria-hidden="true"></span></a></h2>
						<?php $member_arg = array( 'exclude_admins_mods' => false ); ?>
						<?php if ( bp_group_has_members( $member_arg ) ) : ?>

							<ul id="member-list" class="inline-element-list">
								<?php
								while ( bp_group_members() ) : bp_group_the_member();
									global $members_template;
									$member = $members_template->member;
									?>
									<li class="inline-element">
										<a href="<?php echo bp_group_member_domain() ?>">
											<img class="img-responsive" src ="<?php echo bp_core_fetch_avatar( array( 'item_id' => $member->ID, 'object' => 'member', 'type' => 'full', 'html' => false ) ) ?>" alt="<?php echo $member->fullname; ?>"/>
										</a>
									</li>
								<?php endwhile; ?>
							</ul>
							<?php bp_group_member_pagination(); ?>
						<?php else : ?>

							<div id="message" class="info">
								<p>This group has no members.</p>
							</div>

						<?php endif; ?>

					</div>

				<?php endif; // end of if $group != 'portfolio'            ?>

			<?php else : ?>
				<?php
				// check if blog (site) is NOT private (option blog_public Not = '_2"), in which
				// case show site posts and comments even though this group is private
				//
				if ( cboxol_site_can_be_viewed() ) {
					openlab_show_site_posts_and_comments();
					echo "<div class='clear'></div>";
				}
				?>
				<?php /* The group is not visible, show the status message */ ?>

				<?php // do_action( 'bp_before_group_status_message' )             ?>
				<!--
												<div id="message" class="info">
														<p><?php // bp_group_status_message()                                                 ?></p>
												</div>
				-->
				<?php // do_action( 'bp_after_group_status_message' )            ?>

			<?php endif; ?>

			<?php
} else {
	bp_get_template_part( 'groups/single/wds-bp-action-logics.php' );
}
		?>

	</div><!-- #single-course-body -->
	<?php
}

function openlab_get_group_activity_content( $title, $content, $link ) {
	$markup = '';

	if ( $title !== '' ) {
		$markup = <<<HTML
                <p class="semibold h6">
                    <span class="hyphenate truncate-on-the-fly" data-basevalue="80" data-minvalue="55" data-basewidth="376">{$title}</span>
                    <span class="original-copy hidden">{$title}</span>
                </p>
HTML;
	}

	$markup .= <<<HTML
            <p class="activity-content">
                <span class="hyphenate truncate-on-the-fly" data-basevalue="120" data-minvalue="75" data-basewidth="376">{$content}</span>
                <span><a href="{$link}" class="read-more">See More<span class="sr-only">{$title}</span></a><span>
                <span class="original-copy hidden">{$content}</span>
            </p>
HTML;

	return $markup;
}

add_filter( 'bp_get_options_nav_nav-invite-anyone', 'cuny_send_invite_fac_only' );

function cuny_send_invite_fac_only( $subnav_item ) {
	global $bp;
	$account_type = xprofile_get_field_data( 'Account Type', $bp->loggedin_user->id );

	if ( $account_type != 'Student' ) {
		return $subnav_item;
	}
}

/**
 * Add the group type to the Previous Step button during group creation
 *
 * @see http://openlab.citytech.cuny.edu/redmine/issues/397
 */
function openlab_previous_step_type( $url ) {
	if ( ! empty( $_GET['group_type'] ) ) {
		$url = add_query_arg( 'group_type', $_GET['group_type'], $url );
	}

	return $url;
}

add_filter( 'bp_get_group_creation_previous_link', 'openlab_previous_step_type' );

/**
	>>>>>>> 1.3.x
 * Remove the 'hidden' class from hidden group leave buttons
 *
 * A crummy conflict with wp-ajax-edit-comments causes these items to be
 * hidden by jQuery. See b208c80 and #1004
 */
function openlab_remove_hidden_class_from_leave_group_button( $button ) {
	$button['wrapper_class'] = str_replace( ' hidden', '', $button['wrapper_class'] );
	return $button;
}

add_action( 'bp_get_group_join_button', 'openlab_remove_hidden_class_from_leave_group_button', 20 );

function openlab_custom_group_buttons( $button ) {

	if ( $button['id'] == 'leave_group' ) {
		$button['link_text'] = '<span class="pull-left"><i class="fa fa-user" aria-hidden="true"></i> ' . $button['link_text'] . '</span><i class="fa fa-minus-circle pull-right" aria-hidden="true"></i>';
		$button['link_class'] = $button['link_class'] . ' btn btn-default btn-block btn-primary link-btn clearfix';
	} elseif ( $button['id'] == 'join_group' || $button['id'] == 'request_membership' ) {
		$button['link_text'] = '<span class="pull-left"><i class="fa fa-user" aria-hidden="true"></i> ' . $button['link_text'] . '</span><i class="fa fa-plus-circle pull-right" aria-hidden="true"></i>';
		$button['link_class'] = $button['link_class'] . ' btn btn-default btn-block btn-primary link-btn clearfix';
	} elseif ( $button['id'] == 'membership_requested' ) {
		$button['link_text'] = '<span class="pull-left"><i class="fa fa-user" aria-hidden="true"></i> ' . $button['link_text'] . '</span><i class="fa fa-clock-o pull-right" aria-hidden="true"></i>';
		$button['link_class'] = $button['link_class'] . ' btn btn-default btn-block btn-primary link-btn clearfix';
	} elseif ( $button['id'] == 'accept_invite' ) {
		$button['link_text'] = '<span class="pull-left"><i class="fa fa-user" aria-hidden="true"></i> ' . $button['link_text'] . '</span><i class="fa fa-plus-circle pull-right" aria-hidden="true"></i>';
		$button['link_class'] = $button['link_class'] . ' btn btn-default btn-block btn-primary link-btn clearfix';
	}

	return $button;
}

add_filter( 'bp_get_group_join_button', 'openlab_custom_group_buttons' );

/**
 * Output the group subscription default settings
 *
 * This is a lazy way of fixing the fact that the BP Group Email Subscription
 * plugin doesn't actually display the correct default sub level ( even though it
 * does *save* the correct level )
 */
function openlab_default_subscription_settings_form() {
	$portfolio_group_type = cboxol_get_portfolio_group_type();
	if ( cboxol_is_portfolio() || ( bp_is_group_create() && isset( $_GET['group_type'] ) && $portfolio_group_type->get_slug() === $_GET['type'] ) ) {
		return;
	}
	?>
	<hr>
	<h4 id="email-sub-defaults"><?php _e( 'Email Subscription Defaults', 'bp-ass' ); ?></h4>
	<p><?php _e( 'When new users join this group, their default email notification settings will be:', 'bp-ass' ); ?></p>
	<div class="radio email-sub">
		<label><input type="radio" name="ass-default-subscription" value="no" <?php ass_default_subscription_settings( 'no' ) ?> />
			<?php _e( 'No Email ( users will read this group on the web - good for any group - the default )', 'bp-ass' ) ?></label>
		<label><input type="radio" name="ass-default-subscription" value="sum" <?php ass_default_subscription_settings( 'sum' ) ?> />
			<?php _e( 'Weekly Summary Email ( the week\'s topics - good for large groups )', 'bp-ass' ) ?></label>
		<label><input type="radio" name="ass-default-subscription" value="dig" <?php ass_default_subscription_settings( 'dig' ) ?> />
			<?php _e( 'Daily Digest Email ( all daily activity bundles in one email - good for medium-size groups )', 'bp-ass' ) ?></label>
		<label><input type="radio" name="ass-default-subscription" value="sub" <?php ass_default_subscription_settings( 'sub' ) ?> />
			<?php _e( 'New Topics Email ( new topics are sent as they arrive, but not replies - good for small groups )', 'bp-ass' ) ?></label>
		<label><input type="radio" name="ass-default-subscription" value="supersub" <?php ass_default_subscription_settings( 'supersub' ) ?> />
			<?php _e( 'All Email ( send emails about everything - recommended only for working groups )', 'bp-ass' ) ?></label>
	</div>
	<hr />
	<?php
}
add_action( 'bp_actions', function() {
	remove_action( 'bp_after_group_settings_admin', 'ass_default_subscription_settings_form' );
	add_action( 'bp_after_group_settings_admin', 'openlab_default_subscription_settings_form' );
} );

/**
 * Save the group default email setting
 *
 * We override the way that GES does it, because we want to save the value even
 * if it's 'no'. This should probably be fixed upstream
 */
function openlab_save_default_subscription( $group ) {
	global $bp, $_POST;

	if ( isset( $_POST['ass-default-subscription'] ) && $postval = $_POST['ass-default-subscription'] ) {
		groups_update_groupmeta( $group->id, 'ass_default_subscription', $postval );
	}
}

remove_action( 'groups_group_after_save', 'ass_save_default_subscription' );
add_action( 'groups_group_after_save', 'openlab_save_default_subscription' );

/**
 * Pagination links in group directories cannot contain the 's' URL parameter for search
 */
function openlab_group_pagination_search_key( $pag ) {
	if ( false !== strpos( $pag, 'grpage' ) ) {
		$pag = remove_query_arg( 's', $pag );
	}

	return $pag;
}

add_filter( 'paginate_links', 'openlab_group_pagination_search_key' );

//
// DIRECTORY FILTERS   //
//
/**
 * Get breadcrumb text for a filter parameter in a directory.
 */
function openlab_get_directory_filter( $filter_type, $filter_value ) {
	$filter_label = '';

	if ( 0 === strpos( $filter_type, 'academic-unit-' ) ) {
		$academic_unit = cboxol_get_academic_unit( $filter_value );
		if ( ! is_wp_error( $academic_unit ) ) {
			$filter_label = $academic_unit->get_name();
		}
	} elseif ( 'member_type' === $filter_type ) {
		$member_type = cboxol_get_member_type( $filter_value );
		if ( ! is_wp_error( $member_type ) ) {
			$filter_label = $member_type->get_label( 'singular' );
		}
	} elseif ( 'cat' === $filter_type ) {
		$term_obj = get_term_by( 'slug', $filter_value, 'bp_group_categories' );
		if ( $term_obj ) {
			$filter_label = $term_obj->name;
		}
	} elseif ( 'term' === $filter_type ) {
		$filter_label = $filter_value;
	}

	return $filter_label;
}

/**
 * Gets the current directory filters, and spits out some markup
 */
function openlab_current_directory_filters() {
	$filters = array();

	if ( bp_is_members_directory() ) {
		$current_view = 'people';
		$academic_unit_types = cboxol_get_academic_unit_types();
	} else {
		$current_view = bp_get_current_group_directory_type();
		$academic_unit_types = cboxol_get_academic_unit_types( array(
			'group_type' => $current_view,
		) );
		$group_type = cboxol_get_group_type( $current_view );

		if ( ! is_wp_error( $group_type ) ) {
			if ( $group_type->get_is_course() ) {
				$current_view = 'course';
			} elseif ( $group_type->get_is_portfolio() ) {
				$current_view = 'portfolio';
			}
		}
	}

	$filters = array();
	switch ( $current_view ) {
		case 'people' :
			$filters = array_merge( $filters, array( 'member_type' ) );
			break;

		case 'course' :
			$filters = array_merge( $filters, array( 'term' ) );
			// fall through

		case 'portfolio' :
			$filters = array_merge( $filters, array( 'member_type' ) );
			// fall through

		default :
			$filters = array_merge( $filters, array( 'cat' ) );
			break;
	}

	foreach ( $academic_unit_types as $academic_unit_type ) {
		$filters[] = 'academic-unit-' . $academic_unit_type->get_slug();
	}

	$active_filters = array();
	foreach ( $filters as $f ) {
		if ( ! empty( $_GET[ $f ] ) && ! ( strpos( $_GET[ $f ], '_all' ) ) ) {
			$active_filters[ $f ] = wp_unslash( $_GET[ $f ] );
		}
	}

	$markup = '';
	if ( ! empty( $active_filters ) ) {
		$markup .= '<h2 class="font-14 regular margin0-0 current-filters"><span class="bread-crumb">';

		$filter_words = array();
		foreach ( $active_filters as $ftype => $fvalue ) {
			$word = openlab_get_directory_filter( $ftype, $fvalue );
			if ( $word ) {
				$filter_words[] = $word;
			}
			continue;
		}

		$markup .= implode( '<span class="sep">&nbsp;&nbsp;|&nbsp;&nbsp;</span>', $filter_words );

		$markup .= '</span></h2>';
	}

	echo $markup;
}

/**
 * Get a group's recent posts and comments, and display them in two widgets
 */
function openlab_show_site_posts_and_comments() {
	global $first_displayed, $bp;

	$group_id = bp_get_group_id();

	$site_type = false;

	if ( $site_id = openlab_get_site_id_by_group_id( $group_id ) ) {
		$site_type = 'local';
	} elseif ( $site_url = openlab_get_external_site_url_by_group_id( $group_id ) ) {
		$site_type = 'external';
	}

	$posts = array();
	$comments = array();

	switch ( $site_type ) {
		case 'local':
			switch_to_blog( $site_id );

			// Set up posts
			$wp_posts = get_posts(array(
				'posts_per_page' => 3,
			));

			foreach ( $wp_posts as $wp_post ) {
				$_post = array(
					'title' => $wp_post->post_title,
					'content' => strip_tags( bp_create_excerpt( $wp_post->post_content, 110, array( 'html' => true ) ) ),
					'permalink' => get_permalink( $wp_post->ID ),
				);

				if ( ! empty( $wp_post->post_password ) ) {
					$_post['content'] = 'This content is password protected.';
				}

				$posts[] = $_post;
			}

			// Set up comments
			$comment_args = array(
				'status' => 'approve',
				'number' => '3',
			);

			$wp_comments = get_comments( $comment_args );

			foreach ( $wp_comments as $wp_comment ) {
				// Skip the crummy "Hello World" comment
				if ( $wp_comment->comment_ID == '1' ) {
					continue;
				}
				$post_id = $wp_comment->comment_post_ID;

				$comments[] = array(
					'content' => strip_tags( bp_create_excerpt( $wp_comment->comment_content, 110, array( 'html' => false ) ) ),
					'permalink' => get_permalink( $post_id ),
				);
			}

			$site_url = get_option( 'siteurl' );

			restore_current_blog();

			break;

		case 'external':
			$posts = openlab_get_external_posts_by_group_id();
			$comments = openlab_get_external_comments_by_group_id();

			break;
	}

	// If we have either, show both
	if ( ! empty( $posts ) || ! empty( $comments ) ) {
		?>
		<div class="row group-activity-overview">
			<div class="col-sm-12">
				<div id="recent-course">
					<div class="recent-posts">
						<h2 class="title activity-title"><a class="no-deco" href="<?php echo esc_attr( $site_url ) ?>">Recent Posts<span class="fa fa-chevron-circle-right" aria-hidden="true"></span></a></h2>


						<?php foreach ( $posts as $post ) : ?>
							<div class="panel panel-default">
								<div class="panel-body">
									<?php echo openlab_get_group_activity_content( $post['title'], $post['content'], $post['permalink'] ) ?>
								</div>
							</div>
						<?php endforeach ?>

						<?php if ( 'external' == $site_type && groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) ) : ?>
							<p class="description">Feed updates automatically every 10 minutes <a class="refresh-feed" id="refresh-posts-feed" href="<?php echo wp_nonce_url( add_query_arg( 'refresh_feed', 'posts', bp_get_group_permalink( groups_get_current_group() ) ), 'refresh-posts-feed' ) ?>">Refresh now</a></p>
						<?php endif ?>
					</div><!-- .recent-posts -->
				</div><!-- #recent-course -->
			</div><!-- .one-half -->

			<div class="col-sm-12">
				<div id="recent-site-comments">
					<div class="recent-posts">
						<h2 class="title activity-title"><a class="no-deco" href="<?php echo esc_attr( $site_url ) ?>">Recent Comments<span class="fa fa-chevron-circle-right" aria-hidden="true"></span></a></h2>
						<?php if ( ! empty( $comments ) ) : ?>
							<?php foreach ( $comments as $comment ) : ?>
								<div class="panel panel-default">
									<div class="panel-body">
										<?php echo openlab_get_group_activity_content( '', $comment['content'], $comment['permalink'] ) ?>
									</div></div>
							<?php endforeach ?>
						<?php else : ?>
							<div class="panel panel-default">
								<div class="panel-body"><p>No Comments Found</p></div></div>
						<?php endif ?>

						<?php if ( 'external' == $site_type && groups_is_user_admin( bp_loggedin_user_id(), bp_get_current_group_id() ) ) : ?>
							<p class="refresh-message description">Feed updates automatically every 10 minutes <a class="refresh-feed" id="refresh-posts-feed" href="<?php echo wp_nonce_url( add_query_arg( 'refresh_feed', 'comments', bp_get_group_permalink( groups_get_current_group() ) ), 'refresh-comments-feed' ) ?>">Refresh now</a></p>
						<?php endif ?>

					</div><!-- .recent-posts -->
				</div><!-- #recent-site-comments -->
			</div><!-- .one-half -->
		</div>
		<?php
	}
}

/**
 * Generates the info line that appears under group names in directories.
 *
 * @param int $group_id ID of the group.
 * @return string
 */
function openlab_output_course_info_line( $group_id ) {
	$infoline_mup = '';

	$group_type = cboxol_get_group_group_type( $group_id );
	if ( is_wp_error( $group_type ) ) {
		return '';
	}

	$course_code = groups_get_groupmeta( $group_id, 'cboxol_course_code' );
	$term = openlab_get_group_term( $group_id );

	$academic_units = cboxol_get_object_academic_units( array(
		'object_id' => $group_id,
		'object_type' => 'group',
	) );

	// We only care about units from "node" types - those that have no children.
	$academic_unit_types = cboxol_get_academic_unit_types( array(
		'group_type' => $group_type->get_slug(),
	) );
	$parent_types = array();
	foreach ( $academic_unit_types as $academic_unit_type ) {
		$parent = $academic_unit_type->get_parent();
		if ( ! $parent ) {
			continue;
		}
		$parent_types[ $parent ] = true;
	}

	$node_types = array();
	foreach ( $academic_unit_types as $academic_unit_type ) {
		$slug = $academic_unit_type->get_slug();
		if ( isset( $parent_types[ $slug ] ) ) {
			continue;
		}
		$node_types[ $slug ] = 1;
	}

	$infoline_elems = array();
	foreach ( $academic_units as $academic_unit ) {
		$unit_type = $academic_unit->get_type();
		if ( ! isset( $node_types[ $unit_type ] ) ) {
			continue;
		}

		$infoline_elems[] = esc_html( $academic_unit->get_name() );
	}

	if ( $course_code ) {
		$infoline_elems[] = esc_html( $course_code );
	}

	if ( $term ) {
		$infoline_elems[] = sprintf( '<span class="bold">%s</span>', esc_html( $term ) );
	}

	$infoline_mup = implode( '|', $infoline_elems );

	return $infoline_mup;
}

/**
 * Displays per group or porftolio site links
 *
 * @global type $bp
 */
function openlab_bp_group_site_pages( $mobile = false ) {
	global $bp;

	$group_id = bp_get_current_group_id();
	$group_type = cboxol_get_group_group_type( $group_id );

	$group_site_settings = openlab_get_group_site_settings( $group_id );

	$responsive_class = $mobile ? 'visible-xs' : 'hidden-xs';

	if ( ! empty( $group_site_settings['site_url'] ) && $group_site_settings['is_visible'] ) {

		if ( cboxol_is_portfolio() ) {

			$portfolio_group_type = cboxol_get_portfolio_group_type();

			?>

			<?php /* Abstract the displayed user id, so that this function works properly on my-* pages */ ?>
			<?php $displayed_user_id = bp_is_user() ? bp_displayed_user_id() : bp_loggedin_user_id(); ?>

			<div class="sidebar-block group-site-links <?php echo esc_html( $responsive_class ); ?> ">

				<?php
				$account_type = xprofile_get_field_data( 'Account Type', $displayed_user_id );
				?>

				<?php if ( openlab_is_my_portfolio() || is_super_admin() ) : ?>
					<ul class="sidebar-sublinks portfolio-sublinks inline-element-list">
						<li class="portfolio-site-link bold">
							<a class="bold no-deco" href="<?php echo esc_url( $group_site_settings['site_url'] ) ?>"><?php echo esc_html( $portfolio_group_type->get_label( 'visit_portfolio_site' ) ); ?><span class="fa fa-chevron-circle-right cyan-circle" aria-hidden="true"></span></a>
						</li>

						<?php if ( openlab_user_portfolio_site_is_local( $displayed_user_id ) ) : ?>
							<li class="portfolio-dashboard-link">
								<a class="line-height height-200 font-size font-13" href="<?php openlab_user_portfolio_url( $displayed_user_id ) ?>/wp-admin"><?php esc_html_e( 'Site Dashboard', 'openlab-theme' ); ?></a>
							</li>
						<?php endif ?>
					</ul>
				<?php else : ?>
					<ul class="sidebar-sublinks portfolio-sublinks inline-element-list">
						<li class="portfolio-site-link">
							<a class="bold no-deco" href="<?php echo trailingslashit( esc_attr( $group_site_settings['site_url'] ) ); ?>"><?php echo esc_html( $portfolio_group_type->get_label( 'visit_portfolio_site' ) ); ?><span class="fa fa-chevron-circle-right cyan-circle" aria-hidden="true"></span></a>
						</li>
					</ul>

				<?php endif ?>
			</div>
		<?php } else { ?>

			<div class="sidebar-block group-site-links <?php echo esc_html( $responsive_class ); ?>">
				<ul class="sidebar-sublinks portfolio-sublinks inline-element-list">
					<li class="portfolio-site-link">
						<?php echo '<a class="bold no-deco" href="' . trailingslashit( esc_attr( $group_site_settings['site_url'] ) ) . '">' . $group_type->get_label( 'visit_group_site' ) . '<span class="fa fa-chevron-circle-right cyan-circle" aria-hidden="true"></span></a>'; ?>
					</li>
					<?php if ( $group_site_settings['is_local'] && ($bp->is_item_admin || is_super_admin() || groups_is_user_member( bp_loggedin_user_id(), bp_get_current_group_id() )) ) : ?>
						<li class="portfolio-dashboard-link">
							<?php echo '<a class="line-height height-200 font-size font-13" href="' . esc_attr( trailingslashit( $group_site_settings['site_url'] ) ) . 'wp-admin/">' . esc_html__( 'Site Dashboard', 'openlab-theme' ) . '</a>'; ?>
						</li>
					<?php endif; ?>
				</ul>

			</div>
			<?php
} // cboxol_is_portfolio()
	} // !empty( $group_site_settings['site_url'] )
}

function openlab_get_faculty_list() {
	global $bp;

	$faculty_list = '';

	if ( isset( $bp->groups->current_group->admins ) ) {
		$faculty_id = $bp->groups->current_group->admins[0]->user_id;
		$group_id = $bp->groups->current_group->id;

		$faculty_ids = groups_get_groupmeta( $group_id, 'additional_faculty', false );
		array_unshift( $faculty_ids, $faculty_id );

		$faculty = array();
		foreach ( $faculty_ids as $id ) {

			array_push( $faculty, bp_core_get_user_displayname( $id ) );
		}

		$faculty = array_unique( $faculty );

		$faculty_list = implode( ', ', $faculty );
	}

	return $faculty_list;
}

function openlab_get_group_site_settings( $group_id ) {

	// Set up data. Look for local site first. Fall back on external site.
	$site_id = openlab_get_site_id_by_group_id( $group_id );

	if ( $site_id ) {
		$site_url = get_blog_option( $site_id, 'siteurl' );
		$is_local = true;

		$blog_public = (float) get_blog_option( $site_id, 'blog_public' );
		switch ( $blog_public ) {
			case 1 :
			case 0 :
				$is_visible = true;
				break;

			case -1 :
				$is_visible = is_user_logged_in();
				break;

			case -2 :
				$group = groups_get_current_group();
				$is_visible = $group->is_member || current_user_can( 'bp_moderate' );
				break;

			case -3 :
				$caps = get_user_meta( get_current_user_id(), 'wp_' . $site_id . '_capabilities', true );
				$is_visible = isset( $caps['administrator'] );
				break;
		}
	} else {
		$site_url = groups_get_groupmeta( $group_id, 'external_site_url' );
		$is_local = false;
		$is_visible = true;
	}

	$group_site_settings = array(
		'site_url' => $site_url,
		'is_local' => $is_local,
		'is_visible' => $is_visible,
	);

	return $group_site_settings;
}

function openlab_custom_group_excerpts( $excerpt, $group ) {
	global $post, $bp;

	$hits = array( 'courses', 'projects', 'clubs', 'portfolios', 'my-courses', 'my-projects', 'my-clubs' );
	if ( in_array( $post->post_name, $hits ) || $bp->current_action == 'invites' ) {
		$excerpt = strip_tags( $excerpt );
	}

	return $excerpt;
}

add_filter( 'bp_get_group_description_excerpt', 'openlab_custom_group_excerpts', 10, 2 );

/**
 * Disable BuddyPress Cover Images for groups and users.
 */
add_filter( 'bp_disable_cover_image_uploads', '__return_true' );
add_filter( 'bp_disable_group_cover_image_uploads', '__return_true' );

function openlab_get_group_activity_events_feed() {
	$events_out = '';

	// Non-public groups shouldn't show this to non-members.
	$group = groups_get_current_group();
	if ( 'public' !== $group->status && empty( $group->user_has_access ) ) {
		return $events_out;
	}

	if ( ! function_exists( 'eo_get_events' ) ) {
		return $events_out;
	}

	$args = array(
		'event_start_after' => 'today',
		'bp_group' => bp_get_current_group_id(),
		'numberposts' => 5,
	);

	$events = eo_get_events( $args );

	$menu_items = openlab_calendar_submenu();

	ob_start();
	include( locate_template( 'parts/sidebar/activity-events-feed.php' ) );
	$events_out .= ob_get_clean();

	return $events_out;
}

/**
 * Renders the markup for group-site affilitation
 */
function openlab_group_site_markup() {
	global $wpdb, $bp, $current_site, $base;

	$group_type = cboxol_get_edited_group_group_type();
	if ( is_wp_error( $group_type ) ) {
		return;
	}

	$the_group_id = null;
	if ( bp_is_group() ) {
		$the_group_id = bp_get_current_group_id();
	}

	$group_school = groups_get_groupmeta( $the_group_id, 'wds_group_school' );
	$group_project_type = groups_get_groupmeta( $the_group_id, 'wds_group_project_type' );

	?>

	<div class="ct-group-meta">

		<?php
		/** @todo This loads a hidden input as well as school/dept info - oy */
		/*
		if ( ! empty( $group_type ) && 'group' !== $group_type ) {
			echo wds_load_group_type( $group_type );
			?>
			<input type="hidden" name="group_type" value="<?php echo $group_type; ?>" />
			<?php
		} */ ?>

		<?php do_action( 'openlab_group_creation_extra_meta' ); ?>

		<?php $group_site_url = openlab_get_group_site_url( $the_group_id ); ?>

		<div class="panel panel-default">
			<div class="panel-heading"><?php esc_html_e( 'Site Details', 'openlab-theme' ); ?></div>
			<div class="panel-body">

				<?php if ( ! empty( $group_site_url ) ) : ?>

					<div id="current-group-site">
						<?php
						$maybe_site_id = openlab_get_site_id_by_group_id( $the_group_id );

						if ( $maybe_site_id ) {
							$group_site_name = get_blog_option( $maybe_site_id, 'blogname' );
							$group_site_text = '<strong>' . esc_html( $group_site_name ) . '</strong>';
							$group_site_url_out = '<a class="bold" href="' . esc_url( $group_site_url ) . '">' . esc_html( $group_site_url ) . '</a>';
						} else {
							$group_site_text = '';
							$group_site_url_out = '<a class="bold" href="' . esc_url( $group_site_url ) . '">' . esc_html( $group_site_url ) . '</a>';
						}
						?>
						<p><?php printf( esc_html__( 'This group is currently associated with the site "%s"', 'openlab-theme' ), $group_site_text ) ?></p>
						<ul id="change-group-site"><li><?php echo $group_site_url_out ?> <a class="button underline confirm" href="<?php echo wp_nonce_url( bp_get_group_permalink( groups_get_current_group() ) . 'admin/edit-details/unlink-site/', 'unlink-site' ) ?>" id="change-group-site-toggle"><?php esc_html_e( 'Unlink', 'openlab-theme' ); ?></a></li></ul>

					</div>

				<?php else : ?>

					<?php
					$template = $group_type->get_template_site_id();

					$blog_details = get_blog_details( $template );

					// Set up user blogs for fields below
					$user_blogs = get_blogs_of_user( get_current_user_id() );

					// Exclude blogs where the user is not an Admin
					foreach ( $user_blogs as $ubid => $ub ) {
						$role = get_user_meta( bp_loggedin_user_id(), $wpdb->base_prefix . $ub->userblog_id . '_capabilities', true );

						if ( ! array_key_exists( 'administrator', (array) $role ) ) {
							unset( $user_blogs[ $ubid ] );
						}
					}
					$user_blogs = array_values( $user_blogs );
					?>
					<style type="text/css">
						.disabled-opt {
							opacity: .4;
						}
					</style>

					<input type="hidden" name="action" value="copy_blog" />
					<input type="hidden" name="source_blog" value="<?php echo intval( $blog_details->blog_id ); ?>" />

					<div class="form-table groupblog-setup"<?php if ( ! empty( $group_site_url ) ) : ?> style="display: none;"<?php endif ?>>
						<?php if ( ! $group_type->get_requires_site() ) : ?>
							<?php $show_website = 'none' ?>
							<div class="form-field form-required">
								<div scope='row' class="site-details-query">
									<label><input type="checkbox" id="set-up-site-toggle" name="set-up-site-toggle" value="yes" /> <?php esc_html_e( 'Set up a site?', 'openlab-theme' ); ?></label>
								</div>
							</div>
						<?php else : ?>
							<?php $show_website = 'auto' ?>
						<?php endif ?>

						<div id="wds-website-tooltips" class="form-field form-required" style="display:<?php echo $show_website; ?>"><div>

						<?php if ( $group_type->get_is_course() ) : ?>
							<p class="ol-tooltip"><?php esc_html_e( 'Take a moment to consider the address for your site. You will not be able to change it once you\'ve created it. We recommend the following format:', 'openlab-theme' ); ?></p>

							<ul class="ol-tooltip">
								<li class="hyphenate"><?php esc_html_e( 'FacultyLastNameCourseCodeSemYear', 'openlab-theme' ); ?></li>
								<li class="hyphenate"><?php esc_html_e( 'smithadv1100sp2012', 'openlab-theme' ); ?></li>
							</ul>

							<p class="ol-tooltip"><?php esc_html_e( 'If you teach multiple sections of the same course on this site, consider adding other identifying information to the address. Please note that all addresses must be unique.', 'openlab-theme' ); ?></p>

						<?php elseif ( ! $group_type->get_is_portfolio() ) : ?>
							<p class="ol-tooltip"><?php esc_html_e( 'Please take a moment to consider the address for your site. You will not be able to change it once you’ve created it.  If you are linking to an existing site, select from the drop-down menu.', 'openlab-theme' ); ?></p>
						<?php endif ?>

					</div><!-- /.groupblog-setup -->
				</div><!-- /.panel-body -->

				<?php if ( bp_is_group_create() && $group_type->get_can_be_cloned() ) : ?>
					<?php /* @todo get rid of all 'wds' */ ?>
					<div id="wds-website-clone" class="form-field form-required" style="display:<?php echo $show_website; ?>">
						<div id="noo_clone_options">
							<div class="row">
								<div class="radio disabled-opt col-sm-6">
									<label>
										<input type="radio" class="noo_radio" name="new_or_old" id="new_or_old_clone" value="clone" disabled />
										<?php esc_html_e( 'Name your cloned site:', 'openlab-theme' ); ?>
									</label>
								</div>

								<div class="col-sm-5 site-label">
									<?php global $current_site ?>
									<?php echo $current_site->domain . $current_site->path ?>
								</div><!-- /.site-label -->

								<div class="col-sm-13">
									<?php /* @todo subdomains */ ?>
									<input class="form-control domain-validate" size="40" id="clone-destination-path" name="clone-destination-path" type="text" title="<?php _e( 'Path', 'openlab-theme' ) ?>" value="" />
								</div>

								<input name="blog-id-to-clone" value="" type="hidden" />
							</div><!-- /.row -->

							<p id="cloned-site-url"></p>
						</div><!-- /#noo_clone_options -->
					</div><!-- /#wds-website-clone -->
				<?php endif ?>

				<div id="wds-website" class="form-field form-required" style="display:<?php echo $show_website; ?>">
					<div id="noo_new_options">
						<div id="noo_new_options-div" class="row">
							<div class="radio col-sm-6">
								<label>
									<input type="radio" class="noo_radio" name="new_or_old" id="new_or_old_new" value="new" />
									<?php esc_html_e( 'Create a new site:', 'openlab-theme' ); ?>
								</label>
							</div>

							<div class="col-sm-5 site-label">
								<?php
								$suggested_path = $group_type->get_is_portfolio() ? openlab_suggest_portfolio_path() : '';
								// @todo Subdomains
								echo $current_site->domain . $current_site->path
								?>
							</div>

							<div class="col-sm-13">
								<input id="new-site-domain" class="form-control domain-validate" size="40" name="blog[domain]" type="text" title="<?php esc_html_e( 'Domain', 'openlab-theme' ) ?>" value="<?php echo esc_html( $suggested_path ) ?>" />
							</div>
						</div><!-- #noo_new_options-div -->
					</div><!-- #noo_new_options -->
				</div><!-- #wds-website -->

						<?php /* Existing blogs - only display if some are available */ ?>
						<?php
						// Exclude blogs already used as groupblogs
						global $wpdb, $bp;
						$current_groupblogs = $wpdb->get_col( "SELECT meta_value FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'cboxol_group_site_id'" );

						foreach ( $user_blogs as $ubid => $ub ) {
							if ( in_array( $ub->userblog_id, $current_groupblogs ) ) {
								unset( $user_blogs[ $ubid ] );
							}
						}
						$user_blogs = array_values( $user_blogs );
						?>

						<?php if ( ! empty( $user_blogs ) ) : ?>
							<div id="wds-website-existing" class="form-field form-required" style="display:<?php echo $show_website; ?>">

								<div id="noo_old_options">
									<div class="row">
										<div class="radio col-sm-6">
											<label>
												<input type="radio" class="noo_radio" id="new_or_old_old" name="new_or_old" value="old" />
												Use an existing site:</label>
										</div>
										<div class="col-sm-18">
											<label class="sr-only" for="groupblog-blogid">Choose a site</label>
											<select class="form-control" name="groupblog-blogid" id="groupblog-blogid">
												<option value="0">- Choose a site -</option>
												<?php foreach ( (array) $user_blogs as $user_blog ) : ?>
													<option value="<?php echo $user_blog->userblog_id; ?>"><?php echo $user_blog->blogname; ?></option>
												<?php endforeach ?>
											</select>
										</div>
									</div>
								</div>
							</div>
						<?php endif ?>

						<div id="wds-website-external" class="form-field form-required" style="display:<?php echo $show_website; ?>">

							<div id="noo_external_options">
								<div class="form-group row">
									<div class="radio col-sm-6">
										<label>
											<input type="radio" class="noo_radio" id="new_or_old_external" name="new_or_old" value="external" />
											Use an external site:
										</label>
									</div>
									<div class="col-sm-18">
										<label class="sr-only" for="external-site-url">Input external site URL</label>
										<input class="form-control pull-left" type="text" name="external-site-url" id="external-site-url" placeholder="http://" />
										<a class="btn btn-primary no-deco top-align pull-right" id="find-feeds" href="#" display="none">Check<span class="sr-only"> external site for Post and Comment feeds</span></a>
									</div>
								</div>
							</div>
						</div>
						<div id="check-note-wrapper" style="display:<?php echo $show_website; ?>"><div colspan="2"><p id="check-note" class="italics disabled-opt"><?php esc_html_e( 'Note: Please click the Check button to search for Post and Comment feeds for your external site. Doing so will push new activity to the Profile page. If no feeds are detected, you may type in the Post and Comment feed URLs directly or just leave blank.', 'cbox-openlab-core' ) ?></p></div></div>
					</div>

				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Outputs data required for group admin/create JS.
 *
 * @param \CBOX\OL\GroupType $group_type Group type object.
 */
function openlab_group_admin_js_data( \CBOX\OL\GroupType $group_type ) {
	$js_data = array(
		'new_group_type' => $group_type->get_slug(),
		'is_course' => $group_type->get_is_course(),
		'enable_site_by_default' => $group_type->get_enable_site_by_default(),
	);

	?>

	<script type="text/javascript">var CBOXOL_Group_Create = <?php echo json_encode( $js_data ); ?></script>

	<?php
}

/** Group Contact / Additional Faculty ***************************************/

/**
 * Render the "Group Contact" field when creating/editing a project or club.
 */
function openlab_group_contact_field() {
	// Don't show on courses or portfolios.
	$group_id = 0;
	if ( bp_is_group() ) {
		$group_id = bp_get_current_group_id();
	}

	$group_type = cboxol_get_edited_group_group_type();

	// @todo supports additional faculty
	if ( is_wp_error( $group_type ) || ! $group_type->get_supports_group_contact() ) {
		return;
	}

	// Enqueue JS and CSS.
	wp_enqueue_script( 'openlab-group-contact', get_template_directory_uri() . '/js/group-contact.js', array( 'jquery-ui-autocomplete' ) );
	wp_enqueue_style( 'openlab-group-contact', get_template_directory_uri() . '/css/group-contact.css' );

	$existing_contacts = array();
	if ( $group_id ) {
		$existing_contacts = groups_get_groupmeta( $group_id, 'group_contact', false );
		if ( ! is_array( $existing_contacts ) ) {
			$existing_contacts = array( bp_loggedin_user_id() );
		}
	} else {
		$existing_contacts[] = bp_loggedin_user_id();
	}

	$existing_contacts_data = array();
	foreach ( $existing_contacts as $uid ) {
		$u = new WP_User( $uid );
		$existing_contacts_data[] = array(
			'label' => sprintf( '%s (%s)', esc_html( bp_core_get_user_displayname( $uid ) ), esc_html( $u->user_nicename ) ),
			'value' => esc_attr( $u->user_nicename ),
		);
	}

	wp_localize_script( 'openlab-group-contact', 'OL_Group_Contact_Existing', $existing_contacts_data );

	?>

	<div id="group-contact-admin" class="panel panel-default">
		<div class="panel-heading"><label for="group-contact-autocomplete"><?php esc_html_e( 'Group Contact', 'openlab-theme' ); ?></label></div>

		<div class="panel-body">
			<p><?php esc_html_e( 'By default, you are the Group Contact. You may add or remove Contacts once your group has more members.', 'openlab-theme' ); ?></p>

			<label for="group-contact-autocomplete"><?php esc_html_e( 'Group Contact', 'openlab-theme' ); ?></label>
			<input class="hide-if-no-js form-control" type="textbox" id="group-contact-autocomplete" value="" <?php disabled( bp_is_group_create() ); ?> />
			<?php wp_nonce_field( 'openlab_group_contact_autocomplete', '_ol_group_contact_nonce', false ) ?>
			<input type="hidden" name="group-contact-group-id" id="group-contact-group-id" value="<?php echo intval( $group_id ); ?>" />

			<ul id="group-contact-list" class="inline-element-list"></ul>

			<label class="sr-only hide-if-js" for="group-contacts"><?php esc_html_e( 'Group Contacts', 'openlab-theme' ); ?></label>
			<input class="hide-if-js" type="textbox" name="group-contacts" id="group-contacts" value="<?php echo esc_attr( implode( ', ', $existing_contacts ) ) ?>" />

		</div>
	</div>

	<?php
}
add_action( 'bp_after_group_details_creation_step', 'openlab_group_contact_field', 5 );
add_action( 'bp_after_group_details_admin', 'openlab_group_contact_field', 5 );

/**
 * AJAX handler for group contact autocomplete.
 */
function openlab_group_contact_autocomplete_cb() {
	global $wpdb;

	$nonce = $term = '';

	if ( isset( $_GET['nonce'] ) ) {
		$nonce = urldecode( $_GET['nonce'] );
	}

	if ( ! wp_verify_nonce( $nonce, 'openlab_group_contact_autocomplete' ) ) {
		die( json_encode( -1 ) );
	}

	$group_id = isset( $_GET['group_id'] ) ? (int) $_GET['group_id'] : 0;
	if ( ! $group_id ) {
		die( json_encode( -1 ) );
	}

	if ( isset( $_GET['term'] ) ) {
		$term = urldecode( $_GET['term'] );
	}

	$q = new BP_Group_Member_Query( array(
		'group_id' => $group_id,
		'search_terms' => $term,
		'type' => 'alphabetical',
		'group_role' => array( 'member', 'mod', 'admin' ),
	) );

	$retval = array();
	foreach ( $q->results as $u ) {
		$retval[] = array(
			'label' => sprintf( '%s (%s)', esc_html( $u->fullname ), esc_html( $u->user_nicename ) ),
			'value' => esc_attr( $u->user_nicename ),
		);
	}

	echo json_encode( $retval );
	die();
}
add_action( 'wp_ajax_openlab_group_contact_autocomplete', 'openlab_group_contact_autocomplete_cb' );

/**
 * Process the saving of group contacts.
 */
function openlab_group_contact_save( $group ) {
	$nonce = '';

	// @todo Courses
	$group_id = 0;
	if ( bp_is_group() ) {
		$group_id = bp_get_current_group_id();
	}

	$group_type = cboxol_get_edited_group_group_type();

	// @todo supports additional faculty
	if ( is_wp_error( $group_type ) || ! $group_type->get_supports_group_contact() ) {
		return;
	}

	if ( isset( $_POST['_ol_group_contact_nonce'] ) ) {
		$nonce = urldecode( $_POST['_ol_group_contact_nonce'] );
	}

	if ( ! wp_verify_nonce( $nonce, 'openlab_group_contact_autocomplete' ) ) {
		return;
	}

	// Admins only.
	if ( ! groups_is_user_admin( bp_loggedin_user_id(), $group->id ) ) {
		return;
	}

	// Give preference to JS-saved items.
	$group_contact = isset( $_POST['group-contact-js'] ) ? $_POST['group-contact-js'] : null;
	if ( null === $group_contact && isset( $_POST['group-contact'] ) ) {
		$group_contact = $_POST['group-contact'];
	}

	// Delete all existing items.
	$existing = groups_get_groupmeta( $group->id, 'group_contact', false );
	foreach ( $existing as $e ) {
		groups_delete_groupmeta( $group->id, 'group_contact', $e );
	}

	foreach ( (array) $group_contact as $nicename ) {
		$f = get_user_by( 'slug', stripslashes( $nicename ) );

		if ( ! $f ) {
			continue;
		}

		if ( ! groups_is_user_member( $f->ID, $group->id ) ) {
			continue;
		}

		groups_add_groupmeta( $group->id, 'group_contact', $f->ID );
	}
}
add_action( 'groups_group_after_save', 'openlab_group_contact_save' );

/**
 * Markup for the Course Information section when editing/creating a course.
 */
function openlab_course_information_edit_panel() {
	$group_type = cboxol_get_edited_group_group_type();
	if ( is_wp_error( $group_type ) || ! $group_type->get_supports_course_information() ) {
		return;
	}

	$group_id = bp_get_current_group_id();
	$course_code = groups_get_groupmeta( $group_id, 'cboxol_course_code' );
	$section_code = groups_get_groupmeta( $group_id, 'cboxol_section_code' );
	$term = groups_get_groupmeta( $group_id, 'cboxol_term' );
	$year = groups_get_groupmeta( $group_id, 'cboxol_year' );
	$additional_desc_html = groups_get_groupmeta( $group_id, 'cboxol_additional_desc_html' );

	?>

	<div class="panel panel-default">
		<div class="panel-heading"><?php echo esc_html( $group_type->get_label( 'course_information' ) ); ?></div>
		<div class="panel-body"><table>

			<tr><td colspan="2"><p class="ol-tooltip"><?php echo esc_html( $group_type->get_label( 'course_information_description' ) ); ?></p></td></tr>

			<tr class="additional-field course-code-field">
				<td class="additional-field-label"><label class="passive" for="course-code"><?php echo esc_html( $group_type->get_label( 'course_code' ) ); ?></label></td>
				<td><input class="form-control" type="text" id="course-code" name="course-code" value="<?php echo esc_attr( $course_code ); ?>" /></td>
			</tr>

			<tr class="additional-field section-code-field">
				<td class="additional-field-label"><label class="passive" for="section-code"><?php echo esc_html( $group_type->get_label( 'section_code' ) ); ?></label></td>
				<td><input class="form-control" type="text" id="section-code" name="section-code" value="<?php echo esc_attr( $section_code ); ?>" /></td>
			</tr>

			<?php /*
			<tr class="additional-field semester-field">
			<td class="additional-field-label"><label class="passive" for="wds_semester">Semester:</label></td>
			<td><select class="form-control" id="wds_semester" name="wds_semester">
			<option value="">--select one--

			$checked = $Spring = $Summer = $Fall = $Winter = '';

			if ( $wds_semester == 'Spring' ) {
			$Spring = 'selected';
			} elseif ( $wds_semester == 'Summer' ) {
			$Summer = 'selected';
			} elseif ( $wds_semester == 'Fall' ) {
			$Fall = 'selected';
			} elseif ( $wds_semester == 'Winter' ) {
			$Winter = 'selected';
			}

			<option value="Spring" ' . $Spring . '>Spring
			<option value="Summer" ' . $Summer . '>Summer
			<option value="Fall" ' . $Fall . '>Fall
			<option value="Winter" ' . $Winter . '>Winter
			</select></td>
			</tr>

			<tr class="additional-field year-field">
			<td class="additional-field-label"><label class="passive" for="wds_year">Year:</label></td>
			<td><input class="form-control" type="text" id="wds_year" name="wds_year" value="' . $wds_year . '"></td>
			</tr>

			*/ ?>
			<tr class="additional-field additional-description-field">
				<td colspan="2" class="additional-field-label"><label class="passive" for="additional-desc-html"><?php esc_html_e( 'Additional Description/HTML:', 'openlab-theme' ); ?></label></td></tr>
				<tr><td colspan="2"><textarea class="form-control" name="additional-desc-html" id="additional-desc-html"><?php echo esc_textarea( $additional_desc_html ); ?></textarea></td></tr>
				</tr>
		</table></div>

		<?php wp_nonce_field( 'openlab_course_information', '_ol_course_information_nonce', false ); ?>
	</div><!--.panel-->
	<?php
}
add_action( 'bp_after_group_details_creation_step', 'openlab_course_information_edit_panel', 8 );
add_action( 'bp_after_group_details_admin', 'openlab_course_information_edit_panel', 8 );

/**
 * Save Course Information.
 *
 * @param BP_Groups_Group $group
 */
function openlab_course_information_save( BP_Groups_Group $group ) {
	if ( ! isset( $_POST['_ol_course_information_nonce'] ) || ! wp_verify_nonce( $_POST['_ol_course_information_nonce'], 'openlab_course_information' ) ) {
		return;
	}

	$metas = array(
		'course-code' => 'cboxol_course_code',
		'section-code' => 'cboxol_section_code',
		'additional-desc-html' => 'cboxol_additional_desc_html',
	);

	foreach ( $metas as $post_key => $meta_key ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			$value = wp_unslash( $_POST[ $post_key ] );
			groups_update_groupmeta( $group->id, $meta_key, $value );
		}
	}
}
add_action( 'groups_group_after_save', 'openlab_course_information_save' );

function openlab_group_academic_units_edit_markup() {
	$selected_academic_units = array();
	if ( bp_is_group_create() ) {
		$group_type = cboxol_get_group_type( $_GET['group_type'] );
		if ( is_wp_error( $group_type ) ) {
			$group_types = cboxol_get_group_types( array(
				'exclude_portfolio' => true,
			) );
			$group_type = reset( $group_types );
		}
	} else {
		$group_type = cboxol_get_group_group_type( bp_get_current_group_id() );
		$group_academic_units = cboxol_get_object_academic_units( array(
			'object_type' => 'group',
			'object_id' => bp_get_current_group_id(),
		) );

		foreach ( $group_academic_units as $group_academic_unit ) {
			$selected_academic_units[] = $group_academic_unit->get_slug();
		}
	}

	$academic_unit_types = cboxol_get_academic_unit_types( array(
		'group_type' => $group_type->get_slug(),
	) );
	?>
	<?php if ( $academic_unit_types ) : ?>
		<div class="panel panel-default">
			<div class="panel-heading"><?php esc_html_e( 'Academic Units', 'openlab-theme' ) ?></div>
			<div class="panel-body">
				<?php
				echo cboxol_get_academic_unit_selector( array(
					'group_type' => $group_type->get_slug(),
					'selected' => $selected_academic_units,
				) );
				?>
			</div>
		</div>
	<?php endif;
}
add_action( 'bp_after_group_details_admin', 'openlab_group_academic_units_edit_markup' );
add_action( 'bp_after_group_details_creation_step', 'openlab_group_academic_units_edit_markup', 3 );

/** "Term" - temporary implementation ****************************************/

function openlab_get_group_term( $group_id ) {
	return groups_get_groupmeta( $group_id, 'openlab_term', true );
}

/**
 * A hack to encourage standardized terms.
 *
 * This will not translate well.
 */
function openlab_get_default_group_term() {
	$month = date( 'n' );
	$year = date( 'Y' );

	if ( $month > 9 || $month < 4 ) {
		$term = __( 'Spring', 'openlab-theme' );
		$year++;
	} else {
		$term = __( 'Fall', 'openlab-theme' );
	}

	return sprintf( '%s %s', $term, $year );
}

function openlab_group_term_edit_markup() {
	// Only show for courses.
	$group_type = cboxol_get_group_group_type( bp_get_current_group_id() );
	if ( is_wp_error( $group_type ) || ! $group_type->get_is_course() ) {
		return;
	}
	$term = openlab_get_group_term( bp_get_current_group_id() );
	if ( ! $term ) {
		$term = openlab_get_default_group_term();
	}

	?>
	<div class="panel panel-default">
		<div class="panel-heading"><?php esc_html_e( 'Term', 'openlab-theme' ) ?></div>
		<div class="panel-body">
			<label for="academic-term"><?php esc_html_e( 'Academic term for this course', 'openlab-theme' ); ?></label>
			<input class="form-control" type="text" name="academic-term" value="<?php echo esc_attr( $term ); ?>" />
			<?php wp_nonce_field( 'openlab_academic_term', '_openlab-term-nonce', false ); ?>
		</div>
	</div>
	<?php
}
add_action( 'bp_after_group_details_admin', 'openlab_group_term_edit_markup' );
add_action( 'bp_after_group_details_creation_step', 'openlab_group_term_edit_markup', 3 );

/**
 * Save Course term
 *
 * @param BP_Groups_Group $group
 */
function openlab_course_term_save( BP_Groups_Group $group ) {
	if ( ! isset( $_POST['_openlab-term-nonce'] ) || ! wp_verify_nonce( $_POST['_openlab-term-nonce'], 'openlab_academic_term' ) ) {
		return;
	}

	if ( ! isset( $_POST['academic-term'] ) ) {
		return;
	}

	$term = wp_unslash( $_POST['academic-term'] );

	groups_update_groupmeta( $group->id, 'openlab_term', $term );
	delete_transient( 'openlab_active_terms' );
}
add_action( 'groups_group_after_save', 'openlab_course_term_save' );

/**
 * Get list of active semesters for use in course sidebar filter.
 */
function openlab_get_active_terms() {
	global $wpdb, $bp;

	$tkey = 'openlab_active_terms';
	$options = get_transient( $tkey );

	if ( false === $options ) {
		$bp = buddypress();

		// Best we can do is alphabetical ordering.
		$options = $wpdb->get_col( "SELECT DISTINCT(meta_value) FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'openlab_term' ORDER BY meta_value ASC" );

		set_transient( $tkey, $options );
	}

	return $options;
}

