<?php
if ( !defined( 'ABSPATH' ) ) exit;

add_action( 'show_user_profile', 'qtranxf_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'qtranxf_show_extra_profile_fields' );

function qtranxf_show_extra_profile_fields( $user ) {
	global $q_config;
	if ( $q_config['highlight_mode'] != QTX_HIGHLIGHT_MODE_NONE ) { ?>

		<h3><?php _e( 'Translation options', 'qtranslate' ) ?></h3>

		<table class="form-table">

			<tr>
				<th><label for="qtranslate_highlight_enabled"><?php _e( 'Highlight Translatable Fields', 'qtranslate' ) ?></label></th>

				<td>
					<input type="checkbox" value="1" name="qtranslate_highlight_enabled" id="qtranslate_highlight_enabled" <?php checked( !get_user_meta( $user->ID, 'qtranslate_highlight_disabled', true ) ) ?> />
					<span class="description"><?php printf(__( 'The way the translatable fields are highlighted is configured with global option %s.', 'qtranslate' ), '"<a href="'.admin_url('/options-general.php?page=qtranslate-x#option_lsb_style').'">'.__('Highlight Style', 'qtranslate').'</a>"') ?></span>
				</td>
			</tr>

		</table>
	<?php
	}
}

add_action( 'personal_options_update', 'qtranxf_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'qtranxf_save_extra_profile_fields' );

function qtranxf_save_extra_profile_fields( $user_id ) {
	global $q_config;

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	if ( $q_config['highlight_mode'] != QTX_HIGHLIGHT_MODE_NONE ) {
		$enabled = isset($_POST['qtranslate_highlight_enabled']);
		if($enabled){
			delete_user_meta($user_id,'qtranslate_highlight_disabled');
		}else{
			update_user_meta( $user_id, 'qtranslate_highlight_disabled', true );
		}
	}
}