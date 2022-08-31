<?php
/**
 * Insight actions file.
 *
 * @package ConvertPro
 */

add_action( 'wp_ajax_cp_update_campaign', 'cp_update_campaign' );
add_action( 'wp_ajax_cp_delete_popup', 'handle_cp_delete_popup_action' );
add_action( 'wp_ajax_cp_duplicate_popup', 'handle_cp_popup_duplicate_action' );
add_action( 'admin_post_cp_delete_campaign', 'handle_cp_delete_campaign_action' );
add_action( 'wp_ajax_cp_rename_popup', 'cp_rename_popup' );
add_action( 'wp_ajax_cp_change_popup_slug', 'cp_change_popup_slug' );
add_action( 'wp_ajax_cp_rename_campaign', 'cp_rename_campaign' );
add_action( 'cp_get_insight_row_value', 'cp_render_insight_options', 10 );
add_action( 'cp_get_type_row_value', 'cp_render_style_type', 10 );
add_action( 'cp_get_style_status_row_value', 'cp_render_style_status', 10 );

/**
 * Display style status
 *
 * @param int $style parameter.
 * @since 0.0.1
 */
function cp_render_style_status( $style ) {

	$has_active_ab_test['status'] = false;

	if ( class_exists( 'CP_V2_AB_Test' ) ) {
		$ab_test_inst       = CP_V2_AB_Test::get_instance();
		$has_active_ab_test = $ab_test_inst->has_active_ab_test( $style->ID );
	}

	if ( false === $has_active_ab_test['status'] ) {  ?>
		<div class="cp-switch-wrapper">

			<?php

			$style_status = get_post_meta( $style->ID, 'live', true );
			$input_name   = 'style_status_' . $style->ID;
			$uniq         = uniqid();

			?>

			<input type="text" id="cp_<?php echo esc_attr( $input_name ); ?>" class="form-control cp-input cp-switch-input" name="<?php echo esc_attr( $input_name ); ?>" data-style="<?php echo esc_attr( $style->ID ); ?>" value="<?php echo esc_attr( $style_status ); ?>" />
			<?php wp_nonce_field( 'cpro_publish', 'cpro_publish_new' ); ?>
			<input type="checkbox" <?php checked( '1', $style_status ); ?> id="cp_<?php echo esc_attr( $input_name ); ?>_btn_<?php echo esc_attr( $uniq ); ?>" class="ios-toggle cp-input cp-switch-input switch-checkbox cp-switch" value="<?php echo esc_attr( $style_status ); ?>"   >

			<label class="cp-switch-btn checkbox-label" data-on="ON"  data-off="OFF" data-id="cp_<?php echo esc_attr( $input_name ); ?>" for="cp_<?php echo esc_attr( $input_name ); ?>_btn_<?php echo esc_attr( $uniq ); ?>">
			</label>

		</div>
	<?php } else { ?>
		<?php
		if ( isset( $has_active_ab_test['test_name'] ) ) {
			$test_name = substr( $has_active_ab_test['test_name'], 0, 13 ) . ( ( strlen( $has_active_ab_test['test_name'] ) > 13 ) ? '...' : '' );
			/* translators: %s Test name */
			$test_name = sprintf( __( 'A/B - %s', 'convertpro' ), $test_name );
		} else {
			$test_name = __( 'A/B Test is running.', 'convertpro' );
		}
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . CP_PRO_SLUG . '-ab-test' ) ); ?>" class="cp-prog-label"><?php echo esc_attr( $test_name ); ?></a>
		<?php
	}
}

/**
 * Renders insight actions for design
 *
 * @param int $style Style ID.
 * @since 0.0.1
 */
function cp_render_insight_options( $style ) {

	$configure_meta_data = get_post_meta( $style->ID, 'configure', true );

	$data_string   = cp_get_style_info( $configure_meta_data, $style->ID, $style->post_title );
	$data_settings = $data_string;
	?>
	<div class="cp-view-analytics-icon">

		<span class="has-tip" data-position="bottom" title="<?php esc_attr_e( 'Info', 'convertpro' ); ?>">
			<a href="javascript:void(0);" data-settings="<?php echo esc_attr( htmlspecialchars( $data_settings, ENT_COMPAT, 'UTF-8' ) ); ?>" class="cp-info-popup"><em class="dashicons dashicons-info"></em></a>
		</span>

		<?php do_action( 'cp_after_insight_actions', $style ); ?>

	</div>
	<?php
}

/**
 * Renders style type
 *
 * @param int $style Style ID.
 * @since 0.0.1
 */
function cp_render_style_type( $style ) {

	$title             = '';
	$cp_module_type    = get_post_meta( $style->ID, 'cp_module_type', true );
	$module_type       = explode( '_', $cp_module_type );
	$module_type       = array_map( 'ucfirst', $module_type );
	$module_class_name = 'CP_' . implode( '_', $module_type );

	if ( class_exists( $module_class_name ) ) {
		$module_settings = $module_class_name::$settings;
		$title           = $module_settings['title'];
	}
	?>

	<span class="cp-module-type-container"><?php echo esc_attr( $title ); ?></span>
	<?php
}

/**
 * Function Name: cp_update_campaign.
 * Function Description: cp_update_campaign.
 */
function cp_update_campaign() {

	if ( ! current_user_can( 'edit_cp_popup_terms' ) ) {
		$data = array(
			'message' => __( 'You are not authorized to perform this action.', 'convertpro' ),
		);
		wp_send_json_error( $data );
	}
	check_ajax_referer( 'cp_create_groups', 'security' );

	$post_id       = esc_attr( $_POST['post_id'] );
	$campaign_id   = esc_attr( $_POST['campaign_id'] );
	$campaign_name = esc_attr( $_POST['campaign_name'] );

	if ( 'false' !== $campaign_id ) {
		$term = term_exists( (int) $campaign_id, CP_CAMPAIGN_TAXONOMY );
	} else {
		$term = wp_insert_term(
			$campaign_name,
			CP_CAMPAIGN_TAXONOMY
		);
	}

	if ( ! is_wp_error( $term ) ) {

		$post_id = (int) $post_id;
		$cat_id  = (int) $term['term_id'];

		$term_result = wp_set_object_terms( $post_id, $cat_id, CP_CAMPAIGN_TAXONOMY );

		if ( ! is_wp_error( $term_result ) ) {

			$data = array(
				'message' => 'Success',
			);
			wp_send_json_success( $data );

		} else {

			$data = array(
				'message' => 'Error',
			);
			wp_send_json_error( $data );
		}
	} else {
		$data = array(
			'message' => __( 'Campaign already exist', 'convertpro' ),
		);
		wp_send_json_error( $data );
	}
}

/**
 * Function Name: handle_cp_delete_campaign_action.
 * Function Description: handle_cp_delete_campaign_action.
 */
function handle_cp_delete_campaign_action() {

	check_admin_referer( 'delete-campaign-' . sanitize_text_field( $_GET['campaign_id'] ) );
	$campaign_id = esc_attr( $_GET['campaign_id'] );

	if ( ! current_user_can( 'manage_cp_popup_terms' ) ) {
		return new WP_Error( 'broke', __( 'You do not have permissions to perform this action', 'convertpro' ) );
	}

	$term = term_exists( 'your-designs', CP_CAMPAIGN_TAXONOMY );

	if ( 0 === $term || null === $term ) {
		$term = wp_insert_term(
			'Your Call-to-actions',
			CP_CAMPAIGN_TAXONOMY
		);
	}

	if ( ! is_wp_error( $term ) ) {
		$cp_popups_inst = CP_V2_Popups::get_instance();
		$popups         = $cp_popups_inst->get_popups_by_campaign_id( $campaign_id );

		if ( is_array( $popups ) && count( $popups ) > 0 ) {

			$cat_ids = array( $term['term_id'] );
			$cat_ids = array_map( 'intval', $cat_ids );

			foreach ( $popups as $popup ) {

				wp_set_object_terms( $popup->ID, $cat_ids, CP_CAMPAIGN_TAXONOMY );
			}
		}

		if ( intval( $term['term_id'] ) !== intval( $campaign_id ) ) {

			$result = wp_delete_term( (int) $campaign_id, CP_CAMPAIGN_TAXONOMY );
		}

		if ( ! is_wp_error( $result ) ) {

			$query = array(
				'message' => 'success',
				'action'  => 'delete-campaign',
			);

		} else {

			$query = array(
				'message' => 'error',
				'action'  => 'delete-campaign',
			);
		}
	} else {

		$query = array(
			'message' => 'error',
			'action'  => 'delete-campaign',
		);
	}

	$sendback = wp_get_referer();
	$sendback = remove_query_arg( array( 'action', 'message' ), $sendback );
	$sendback = add_query_arg( $query, $sendback );

	wp_safe_redirect( $sendback );
	exit();
}

/**
 * Function Name: handle_cp_popup_duplicate_action.
 * Function Description: handle_cp_popup_duplicate_action.
 */
function handle_cp_popup_duplicate_action() {

	if ( ! current_user_can( 'edit_cp_popup' ) ) {
		$data = array(
			'message' => __( 'You are not authorized to perform this action.', 'convertpro' ),
		);
		wp_send_json_error( $data );
	}

	check_ajax_referer( 'cp_duplicate_popup', 'security' );

	$popup_id = isset( $_POST['popup_id'] ) ? sanitize_text_field( $_POST['popup_id'] ) : '';
	$title    = isset( $_POST['popup_name'] ) ? sanitize_text_field( $_POST['popup_name'] ) : '';

	$result = cp_duplicate_popup( $popup_id, $title );

	if ( is_wp_error( $result ) ) {

		$query = array(
			'message' => 'error',
			'action'  => 'duplicate',
			'html'    => '',
		);

	} else {

		if ( 'error' === $result['message'] ) {

			$query = array(
				'message' => 'error',
				'action'  => 'duplicate',
				'html'    => '',
			);

		} else {

			$popup_id    = isset( $result['popup_id'] ) ? $result['popup_id'] : '';
			$module_type = get_post_meta( $popup_id, 'cp_module_type', true );
			$module_type = str_replace( '_', ' ', $module_type );

			$query = array(
				'message'     => 'success',
				'style_id'    => $popup_id,
				'action'      => 'duplicate',
				'module_type' => ucwords( $module_type ),
			);

			$style = get_post( $popup_id );

			$html = cp_get_insights_row( $style );

			$query['html'] = $html;
		}
	}
	wp_send_json_success( $query );
}

/**
 * Function Name: handle_cp_delete_popup_action.
 * Function Description: handle cp delete popup action.
 */
function handle_cp_delete_popup_action() {

	check_ajax_referer( 'cp_delete_popup', 'security' );

	$popup_id = esc_attr( $_POST['popup_id'] );

	if ( current_user_can( 'delete_cp_popup', $popup_id ) ) {
		if ( ! wp_delete_post( $popup_id ) ) {

			$query = array(
				'message' => 'error',
				'action'  => 'delete',
			);

		} else {
			$query = array(
				'message' => 'success',
				'action'  => 'delete',
			);
		}
	} else {

		$query = array(
			'message' => 'error',
			'action'  => 'delete',
		);

	}
	wp_send_json_success( $query );
}

/**
 * Function Name: cp_rename_popup.
 * Function Description: cp_rename_popup.
 */
function cp_rename_popup() {

	if ( ! current_user_can( 'edit_cp_popup' ) ) {
		$data = array(
			'message' => __( 'You are not authorized to perform this action.', 'convertpro' ),
		);
		wp_send_json_error( $data );
	}
	check_ajax_referer( 'cp_rename_popup', 'security' );

	$popup_id   = isset( $_POST['popup_id'] ) ? esc_attr( $_POST['popup_id'] ) : '';
	$popup_name = isset( $_POST['popup_name'] ) ? esc_attr( $_POST['popup_name'] ) : '';

	if ( '' !== $popup_id ) {
		// Update post.
		$popup = array(
			'ID'         => $popup_id,
			'post_title' => $popup_name,
		);

		// Update the post into the database.
		$result = wp_update_post( $popup );

		if ( ! is_wp_error( $result ) ) {
			$data = array(
				'success'   => true,
				'new_title' => $popup_name,
			);

			wp_send_json_success( $data );
		} else {
			wp_send_json_error();
		}
	}
}

/**
 * Function Name: cp_change_popup_slug.
 * Function Description: cp_change_popup_slug.
 */
function cp_change_popup_slug() {

	if ( ! current_user_can( 'edit_cp_popup' ) ) {
		$data = array(
			'message' => __( 'You are not authorized to perform this action.', 'convertpro' ),
		);
		wp_send_json_error( $data );
	}
	check_ajax_referer( 'cp_change_popup_slug', 'security' );

	$popup_id        = isset( $_POST['popup_id'] ) ? esc_attr( $_POST['popup_id'] ) : '';
	$popup_slug_name = isset( $_POST['popup_slug_name'] ) ? esc_attr( $_POST['popup_slug_name'] ) : '';

	if ( '' !== $popup_id ) {
		// Update post.
		$popup = array(
			'ID'        => $popup_id,
			'post_name' => $popup_slug_name,
		);

		// Update the post into the database.
		$result = wp_update_post( $popup );

		if ( ! is_wp_error( $result ) ) {
			$module_type    = get_post_meta( $popup_id, 'cp_module_type', true );
			$cp_popups_inst = CP_V2_Popups::get_instance();
			$display        = '';

			if ( 'inline' === $module_type || 'widget' === $module_type ) {
				$display = 'inline';
			}

			$output            = $cp_popups_inst->render( $popup_id, false, '1', $module_type, $display, '' );
			$output_formattted = htmlspecialchars( $output, ENT_COMPAT, 'UTF-8' );

			update_post_meta( $popup_id, 'html_data', $output_formattted );

			$data = array(
				'success'        => true,
				'new_popup_slug' => $popup_slug_name,
			);

			wp_send_json_success( $data );
		} else {
			$data = array(
				'success' => false,
				'message' => __( 'Something went wrong.', 'convertpro' ),
			);
			wp_send_json_error( $data );
		}
	}
}

/**
 * Function Name: cp_rename_campaign.
 * Function Description: cp rename campaign.
 */
function cp_rename_campaign() {

	if ( ! current_user_can( 'edit_cp_popup_terms' ) ) {
		$data = array(
			'message' => __( 'You are not authorized to perform this action.', 'convertpro' ),
		);
		wp_send_json_error( $data );
	}

	check_ajax_referer( 'cp_create_groups', 'security' );

	$campaign_id   = isset( $_POST['campaign_id'] ) ? esc_attr( $_POST['campaign_id'] ) : '';
	$campaign_name = isset( $_POST['campaign_name'] ) ? esc_attr( $_POST['campaign_name'] ) : '';

	if ( '' !== $campaign_id ) {

		// Update post.
		$campaign = array(
			'name' => $campaign_name,
		);

		// Update the post into the database.
		$result = wp_update_term( $campaign_id, CP_CAMPAIGN_TAXONOMY, $campaign );

		if ( ! is_wp_error( $result ) ) {
			$data = array(
				'success'   => true,
				'new_title' => $campaign_name,
			);

			wp_send_json_success( $data );
		} else {
			wp_send_json_error();
		}
	}
}

/**
 * Function Name: cp_get_style_info.
 * Function Description: cp get style info.
 *
 * @param string $settings string parameter.
 * @param string $style_id string parameter.
 * @param string $title string parameter.
 */
function cp_get_style_info( $settings, $style_id, $title ) {

	ob_start();
	$cp_module_type = get_post_meta( $style_id, 'cp_module_type', true );
	$cp_module_type = ( ! empty( $cp_module_type ) && '' !== $cp_module_type ) ? $cp_module_type : '';

	?>
	<div class="cp-modal-header">
		<h3 class="cp-md-modal-title"><?php esc_attr_e( 'Behavior Quick View - ', 'convertpro' ); ?><span><?php echo esc_attr( $title ); ?></span></h3>
		<span class="cp-info-id-wrap">
		<?php
		/* translators: %s percentage */
		$style_post = get_post( $style_id );
		$slug       = $style_post->post_name;
		echo '<strong>' . esc_html__( 'ID', 'convertpro' ) . '</strong>: ';
		echo esc_html( $style_id );
		echo ' | <strong>' . esc_html__( 'Slug', 'convertpro' ) . '</strong>: ';
		echo esc_html( $slug );
		?>
		</span>
	</div>
	<table>
		<caption></caption>
		<tr style = "display: none;">
			<th scope="col"></th>
			<th scope="col"></th>
		</tr>
		<?php
		if ( 'before_after' !== $cp_module_type && 'inline' !== $cp_module_type & 'widget' !== $cp_module_type ) {
			?>
		<tr>
			<td><?php esc_html_e( 'When should this call-to-action appear?', 'convertpro' ); ?></td>
			<td class="cpro-rules-data">
			<?php

			$rulesets = isset( $settings['rulesets'] ) ? json_decode( $settings['rulesets'] ) : array();

			$user_inactivity = esc_attr( get_option( 'cp_user_inactivity' ) );
			$display_rules   = array();

			if ( ! empty( $rulesets ) ) {
				foreach ( $rulesets as $ruleset ) {

					$confi_rules = array();

					if ( ! $user_inactivity ) {
						$user_inactivity = '60';
					}

					if ( isset( $ruleset->modal_exit_intent ) && ( '1' === $ruleset->modal_exit_intent || true === $ruleset->modal_exit_intent ) ) {
						$confi_rules[] = __( 'Exit Intent', 'convertpro' );
					}

					if ( isset( $ruleset->autoload_on_duration ) && ( '1' === $ruleset->autoload_on_duration || true === $ruleset->autoload_on_duration ) ) {
						/* translators: %s seconds */
						$confi_rules[] = sprintf( __( 'After %s seconds', 'convertpro' ), $ruleset->load_on_duration );
					}

					if ( isset( $ruleset->autoload_on_no_page_visit ) && ( '1' === $ruleset->autoload_on_no_page_visit || true === $ruleset->autoload_on_no_page_visit ) ) {

						$page_visit_type  = isset( $ruleset->load_on_page_visit_type ) ? $ruleset->load_on_page_visit_type : 'is-more-than';
						$page_visit_count = isset( $ruleset->load_on_no_page_visit ) ? $ruleset->load_on_no_page_visit : '1';
						/* translators: %1$s conditon type and %2$s number */
						$confi_rules[] = sprintf( __( 'Display when a user viewed number of pages count: %1$s - %2$s', 'convertpro' ), $page_visit_type, $page_visit_count );
					}

					if ( isset( $ruleset->autoload_on_scroll ) && ( '1' === $ruleset->autoload_on_scroll || true === $ruleset->autoload_on_scroll ) ) {
						/* translators: %s percentage */
						$confi_rules[] = sprintf( __( 'After user scrolls the %s%%', 'convertpro' ), $ruleset->load_after_scroll );

						if ( isset( $ruleset->close_after_scroll ) && ( $ruleset->close_after_scroll > $ruleset->load_after_scroll ) ) {

							/* translators: %s percentage */
							$confi_rules[] = sprintf( __( 'Display within Range - Will appear when user scrolls to %1$s%% and close when he scrolls to %2$s%%.<br> i.e. Remains open between the range (%3$s%% and %4$s%% of the page)', 'convertpro' ), $ruleset->load_after_scroll, $ruleset->close_after_scroll, $ruleset->load_after_scroll, $ruleset->close_after_scroll );
						}
					}

					if ( isset( $ruleset->inactivity ) && ( '1' === $ruleset->inactivity || true === $ruleset->inactivity ) ) {
						/* translators: %s seconds */
						$confi_rules[] = sprintf( __( 'Inactivitiy for %s Seconds', 'convertpro' ), $user_inactivity );
					}

					if ( isset( $ruleset->enable_after_post ) && ( '1' === $ruleset->enable_after_post || true === $ruleset->enable_after_post ) ) {
						$confi_rules[] = __( 'After user reaches the end of a blog post.', 'convertpro' );
					}

					if ( isset( $ruleset->enable_display_inline ) && ( '1' === $ruleset->enable_display_inline || true === $ruleset->enable_display_inline ) ) {
						$confi_rules[] = __( 'Display Inline', 'convertpro' );
					}

					if ( isset( $ruleset->enable_custom_scroll ) && ( '1' === $ruleset->enable_custom_scroll || true === $ruleset->enable_custom_scroll ) ) {
						/* translators: %s enable scroll class option value */
						$confi_rules[] = sprintf( __( 'After user reaches the %s on page.', 'convertpro' ), $ruleset->enable_scroll_class );
					}
					if ( isset( $ruleset->enable_scheduler ) && ( '1' === $ruleset->enable_scheduler || true === $ruleset->enable_scheduler ) ) {
						/* translators: %s start date and end date option value */
						$confi_rules[] = sprintf( __( 'Scheduled: From ( %1$s ) To ( %2$s )', 'convertpro' ), $ruleset->start_date, $ruleset->end_date );
					}

					$confi_rules_string = implode( ' and ', $confi_rules );

					echo '<ul><li class="cpro-bb-cls">' . $confi_rules_string . '</li></ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					$referal_on = ( isset( $ruleset->enable_referrer ) && $ruleset->enable_referrer ) ? 'Enable' : 'Disabled ';

					if ( isset( $ruleset->enable_referrer ) && $ruleset->enable_referrer ) {
						$referal_display_key = 'Display Only To';
						$referal_display_val = $ruleset->display_to;
					} else {
						$referal_display_key = 'Hide Only To';
						$referal_display_val = isset( $ruleset->hide_from ) ? $ruleset->hide_from : '';
					}

					$visible_to = __( 'Visible to all', 'convertpro' );

					if ( isset( $ruleset->enable_visitors ) && ( '1' === $ruleset->enable_visitors || true === $ruleset->enable_visitors ) ) {
						$visible_to = $ruleset->visitor_type;
					}

					$display_rules[] = array(
						'Referrer Detection' => $referal_on,
						$referal_display_key => $referal_display_val,
						'Visitor Type'       => str_replace( '-', ' ', $visible_to ),
					);
				}
			}
			?>
			</td>
		</tr>
		<tr>
			<td>
				<?php esc_html_e( 'Where is this call-to-action enabled/disabled?', 'convertpro' ); ?>
			</td>
			<td>
				<?php

				$rules   = ( isset( $settings['target_rule_display'] ) ) ? $settings['target_rule_display'] : '';
				$display = cpro_get_design_visibility( $rules );

				$rules   = ( isset( $settings['target_rule_exclude'] ) ) ? $settings['target_rule_exclude'] : '';
				$exclude = cpro_get_design_visibility( $rules );

				$enabled = implode( ', ', $display );
				if ( '' !== $enabled ) {
					echo '<ul><li>' . esc_html__( 'Enabled on', 'convertpro' ) . ' - ' . esc_html( $enabled ) . '</li></ul>';
				}

				$disable = implode( ', ', $exclude );
				if ( '' !== $disable ) {
					echo '<ul><li>' . esc_html__( 'Disabled on', 'convertpro' ) . ' - ' . esc_html( $disable ) . '</li></ul>';
				}
				?>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Who should see this call-to-action?', 'convertpro' ); ?></td>
			<td class="cpro-rules-data">
				<?php
				if ( isset( $settings['show_for_logged_in'] ) && '1' === $settings['show_for_logged_in'] ) {
					?>
					<ul>
					<li class="cpro-bb-cls"><?php esc_html_e( 'Everyone including logged in users.', 'convertpro' ); ?></li>
					</ul>
					<?php
				}

				if ( isset( $settings['display_on_first_load'] ) && '1' === $settings['display_on_first_load'] ) {
					?>
					<ul>
					<li class="cpro-bb-cls"><?php esc_html_e( 'Everyone including first time visitors.', 'convertpro' ); ?></li>
					</ul>
					<?php
				}

				if ( isset( $settings['hide_on_device'] ) && $settings['hide_on_device'] ) {
					$hide_devices = str_replace( '|', ', ', $settings['hide_on_device'] );
					?>
					<ul>
					<li class="cpro-bb-cls"><?php esc_html_e( 'Hide On Devices - ', 'convertpro' ); ?>
					<?php echo esc_html( $hide_devices ); ?></li>
					</ul>
					<?php
				}

				foreach ( $display_rules as $key => $rules ) {
					$incrementor = 0;
					$count       = count( $rules );
					$class       = '';

					echo '<h4>' . esc_html__( 'Ruleset', 'convertpro' ) . '&nbsp;' . ( esc_html( $key ) + 1 ) . '</h4>';

					foreach ( $rules as $key => $value ) {

						if ( $incrementor === $count - 1 ) {
							$class = 'cpro-bb-cls';
						}

						if ( '' !== $value ) {
							echo '<ul><li class="' . esc_attr( $class ) . '">' . esc_html( $key ) . ' - ' . esc_html( ucwords( $value ) ) . '</li></ul>';
						}

						$incrementor++;
					}
				}
				?>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'How frequently do you wish to show this call-to-action?', 'convertpro' ); ?></td>
			<td>
				<?php if ( isset( $settings['cookies_enabled'] ) && '1' === $settings['cookies_enabled'] ) { ?>				
					<ul>
					<li>
					<?php
					echo esc_html__( 'Hide for -', 'convertpro' ) . '<br>';
					echo esc_html( $settings['conversion_cookie'] );
					echo esc_html__( ' days after conversion ', 'convertpro' ) . '<br>';
					echo esc_html( $settings['closed_cookie'] );
					echo esc_html__( ' days after closing ', 'convertpro' );
					?>
					</li>
					</ul>
				<?php } else { ?>
					<ul><li><?php esc_html_e( 'It will appear every time a visitor arrives on your website.', 'convertpro' ); ?></li></ul>
					<?php
				}
				?>
			</td>
		</tr>
			<?php
		} elseif ( 'inline' === $cp_module_type ) {
			?>
		<tr>
			<td>
				<?php
				$shortcode = '[cp_popup display="inline" style_id="' . $style_id . '" step_id = "1"][/cp_popup]';
				esc_html_e( 'Shortcode: ', 'convertpro' );
				echo esc_html( htmlspecialchars( $shortcode, ENT_COMPAT, 'UTF-8' ) );
				?>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'This is an inline form & will be displayed on post/pages you have added the short-code.', 'convertpro' ); ?></td>
		</tr>
			<?php
		} elseif ( 'before_after' === $cp_module_type ) {
			?>
		<tr>
			<td><?php esc_html_e( 'What is the call-to-action inline position?', 'convertpro' ); ?></td>
			<td>
			<?php
			$inline_position = __( 'Both Before and After the post.', 'convertpro' );
			if ( isset( $settings['inline_position'] ) ) {
				if ( 'before_post' === $settings['inline_position'] ) {
					$inline_position = __( 'Before the posts.', 'convertpro' );
				} elseif ( 'after_post' === $settings['inline_position'] ) {
					$inline_position = __( 'After the posts.', 'convertpro' );
				}
				?>
				<ul><li><?php echo esc_html( $inline_position ); ?></li></ul>
				<?php
			}
			?>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Where is this call-to-action enabled/disabled?', 'convertpro' ); ?></td>
			<td>
				<?php
				$rules   = ( isset( $settings['target_rule_display'] ) ) ? $settings['target_rule_display'] : '';
				$display = cpro_get_design_visibility( $rules );

				$rules   = ( isset( $settings['target_rule_exclude'] ) ) ? $settings['target_rule_exclude'] : '';
				$exclude = cpro_get_design_visibility( $rules );

				$enabled = implode( ', ', $display );
				if ( '' !== $enabled ) {
					echo '<ul><li>' . esc_html__( 'Enabled on', 'convertpro' ) . ' - ' . esc_html( $enabled ) . '</li></ul>';
				}

				$disable = implode( ', ', $exclude );
				if ( '' !== $disable ) {
					echo '<ul><li>' . esc_html__( 'Disabled on', 'convertpro' ) . ' - ' . esc_html( $disable ) . '</li></ul>';
				}
				?>
							</td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Who should see this call-to-action?', 'convertpro' ); ?></td>
			<td>
				<?php
				if ( isset( $settings['show_for_logged_in'] ) && '1' === $settings['show_for_logged_in'] ) {
					?>
					<ul><li><?php esc_html_e( 'Everyone including logged in users.', 'convertpro' ); ?></li></ul>
					<?php
				}
				if ( isset( $settings['display_on_first_load'] ) && '1' === $settings['display_on_first_load'] ) {
					?>
					<ul><li><?php esc_html_e( 'Everyone including first time visitors.', 'convertpro' ); ?></li></ul>
					<?php
				}
				if ( isset( $settings['hide_on_device'] ) && $settings['hide_on_device'] ) {
					$hide_devices = str_replace( '|', ', ', $settings['hide_on_device'] );
					?>
					<ul><li><?php esc_html_e( 'Hide On Devices - ', 'convertpro' ); ?>
					<?php echo esc_html( $hide_devices ); ?></li></ul>
					<?php
				}
				?>
			</td>
		</tr>
			<?php
		} elseif ( 'widget' === $cp_module_type ) {
			?>
		<tr>
			<td><?php esc_html_e( 'This is a widget form & will be displayed on post/pages you have added the widget.', 'convertpro' ); ?></td>
		</tr>
			<?php
		}
		?>
	</table>
	<?php
	$html_string = ob_get_clean();

	return $html_string;
}

/**
 * Function Name: cpro_get_design_visibility.
 * Function Description: Get design visibility.
 *
 * @param string $rules string parameter.
 */
function cpro_get_design_visibility( $rules ) {

	$rules = json_decode( $rules );
	$arr   = array();

	if ( is_array( $rules ) && ! empty( $rules ) ) {

		foreach ( $rules as $rule ) {
			if ( ! isset( $rule->type ) || ( isset( $rule->type ) && empty( $rule->type ) ) ) {
				break;
			}

			if ( strrpos( $rule->type, 'all' ) !== false ) {
				$rule_case = 'all';
			} else {
				$rule_case = $rule->type;
			}

			switch ( $rule_case ) {
				case 'basic-global':
					$show_popup = __( 'Entire Website', 'convertpro' );
					break;

				case 'basic-singulars':
					$show_popup = __( 'All Singulars', 'convertpro' );
					break;

				case 'basic-archives':
					$show_popup = __( 'All Archives', 'convertpro' );
					break;

				case 'special-404':
					$show_popup = __( '404 Page', 'convertpro' );
					break;

				case 'special-search':
					$show_popup = __( 'Search Page', 'convertpro' );
					break;

				case 'special-blog':
					$show_popup = __( 'Blog / Posts Page', 'convertpro' );
					break;

				case 'special-front':
					$show_popup = __( 'Front Page', 'convertpro' );
					break;

				case 'special-date':
					$show_popup = __( 'Date Archive', 'convertpro' );
					break;

				case 'special-author':
					$show_popup = __( 'Author Archive', 'convertpro' );
					break;

				case 'all':
					$show_popup = $rule->type;
					$rule_data  = explode( '|', $rule->type );

					$post_type     = isset( $rule_data[0] ) ? $rule_data[0] : false;
					$archieve_type = isset( $rule_data[2] ) ? $rule_data[2] : false;
					$taxonomy      = isset( $rule_data[3] ) ? $rule_data[3] : false;

					if ( false === $taxonomy ) {

						$obj = get_post_type_object( $post_type );
						$arc = ( false === $archieve_type ) ? '' : __( 'Archive', 'convertpro' );
						if ( isset( $obj->labels->name ) ) {
							/* translators: %s enable scroll class option value */
							$show_popup = sprintf( __( 'All %1$s %2$s', 'convertpro' ), ucwords( $obj->labels->name ), $arc );
						}
					} else {

						if ( false !== $taxonomy ) {

							$obj = get_taxonomy( $taxonomy );
							if ( isset( $obj->labels->name ) ) {
								/* translators: %s enable scroll class option value */
								$show_popup = sprintf( __( 'All %s Archive', 'convertpro' ), ucwords( $obj->labels->name ) );
							}
						}
					}
					break;

				case 'specifics':
					if ( isset( $rule->specific ) && is_array( $rule->specific ) ) {

						foreach ( $rule->specific as $specific_page ) {

							$specific_data      = explode( '-', $specific_page );
							$specific_post_type = isset( $specific_data[0] ) ? $specific_data[0] : false;
							$specific_post_id   = isset( $specific_data[1] ) ? $specific_data[1] : false;

							if ( 'post' === $specific_post_type ) {
								$names[] = get_the_title( $specific_post_id );
							} elseif ( 'tax' === $specific_post_type ) {
								$term    = get_term( $specific_post_id );
								$names[] = $term->name;
							}
						}
						$show_popup = implode( ', ', $names );
					}
					break;

				default:
					break;
			}

			if ( 'all' !== $rule_case ) {
				$arr[ $rule_case ] = $show_popup;
			} else {
				$arr[] = $show_popup;
			}
		}
	}
	return $arr;
}
