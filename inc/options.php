<?php
/**
 * Generated by the WordPress Option Page generator
 * at http://jeremyhixon.com/wp-tools/option-page/
 */

class TTSAudio_Plugin_Options {
	private $ttsaudio_options;
	private $tts;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'ttsaudio_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'ttsaudio_page_init' ) );
	}

	public function ttsaudio_add_plugin_page() {
		add_menu_page(
			'TTS Audio', // page_title
			'TTS Audio', // menu_title
			'manage_options', // capability
			'ttsaudio', // menu_slug
			array( $this, 'ttsaudio_create_admin_page' ), // function
			'dashicons-controls-volumeon' // icon_url
		);
	}

	public function ttsaudio_create_admin_page() {
		$this->ttsaudio_options = get_option( 'ttsaudio_options' );
		$this->tts = new TTSAudio;
		?>

		<div class="wrap">
			<h2>TTSAudio</h2>
			<p></p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'ttsaudio_option_group' );
					do_settings_sections( 'ttsaudio-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function ttsaudio_page_init() {
		register_setting(
			'ttsaudio_option_group', // option_group
			'ttsaudio_options', // option_name
			array( $this, 'ttsaudio_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'ttsaudio_setting_section', // id
			__('Settings', 'ttsaudio'), // title
			array( $this, 'ttsaudio_section_info' ), // callback
			'ttsaudio-admin' // page
		);

		add_settings_field(
			'post_types', // id
			__('Post Types', 'ttsaudio'), // title
			array( $this, 'post_types_callback' ), // callback
			'ttsaudio-admin', // page
			'ttsaudio_setting_section' // section
		);

		add_settings_field(
			'plyr_skin', // id
			__('Skin', 'ttsaudio'), // title
			array( $this, 'plyr_skin_callback' ), // callback
			'ttsaudio-admin', // page
			'ttsaudio_setting_section' // section
		);

		add_settings_field(
			'custom_skins', // id
			__('Custom Skins', 'ttsaudio'), // title
			array( $this, 'field_custom_skins_callback' ), // callback
			'ttsaudio-admin', // page
			'ttsaudio_setting_section' // section
		);

		add_settings_field(
			'default_voice', // id
			__('Default Voice', 'ttsaudio'), // title
			array( $this, 'default_voice_callback' ), // callback
			'ttsaudio-admin', // page
			'ttsaudio_setting_section' // section
		);

		add_settings_field(
			'fpt_api_key', // id
			'FPT API Key', // title
			array( $this, 'fpt_api_key_callback' ), // callback
			'ttsaudio-admin', // page
			'ttsaudio_setting_section' // section
		);
	}

	public function ttsaudio_sanitize($input) {

		$sanitary_values = array();

		if ( isset( $input['post_types'] ) ) {
			$sanitary_values['post_types'] = $input['post_types'];
		}

		if ( isset( $input['plyr_skin'] ) ) {
			$sanitary_values['plyr_skin'] = sanitize_text_field ( $input['plyr_skin'] );
		}

		if ( isset( $input['custom_skins'] ) && is_array( $input['custom_skins'] ) ) {

			$sanitize_skin = [];
			$count = 0;
			foreach( $input['custom_skins'] as $custom_skin ) {
				foreach( $custom_skin as $key => $value ){
					$sanitize_skin[$count][$key] = sanitize_text_field( $value );
				}
				$count++;
			}

			$sanitary_values['custom_skins'] = $sanitize_skin;
		}

		if ( isset( $input['default_voice'] ) ) {
			$sanitary_values['default_voice'] = sanitize_key( $input['default_voice'] );
		}

		if ( isset( $input['fpt_api_key'] ) ) {
			$sanitary_values['fpt_api_key'] = sanitize_text_field( $input['fpt_api_key'] );
		}

		return apply_filters('ttsaudio_sanitize', $input, $sanitary_values);

		//return $sanitary_values;
	}

	public function ttsaudio_section_info() {

	}

	public function post_types_callback() {?>

		<?php
		$selected = isset( $this->ttsaudio_options['post_types'] ) ? $this->ttsaudio_options['post_types'] : ['post'];
		$post_types = get_post_types( ['public' => true], 'objects' );
		?>
		<?php foreach ( $post_types as $slug => $post_type ) : ?>
			<p>
				<label>
					<input type="checkbox" name="ttsaudio_options[post_types][]" value="<?= esc_attr( $slug ); ?>"<?php checked( in_array( $slug, $selected ) ) ?>>
					<?= esc_html( $post_type->labels->singular_name ); ?>
				</label>
			</p>
		<?php endforeach; ?>

	<?php
	}

	public function plyr_skin_callback() {?>

		<select name="ttsaudio_options[plyr_skin]" id="plyr_skin">
			<?php
			foreach ( $this->tts->PlyrSkin() as $key => $value ) {?>
				<option value="<?php esc_attr_e( $key );?>" <?php selected( $this->ttsaudio_options['plyr_skin'], esc_attr( $key ) ); ?>><?php esc_html_e( $value );?></option>
			<?php }?>
		</select>
		<p class="description"><?php _e( 'This skin will be appeared in Single post and default for TTSaudio widget.', 'ttsaudio' ); ?></p>
	<?php
	}

	public function default_voice_callback() {
		?> <select name="ttsaudio_options[default_voice]" id="default_voice">
			<?php
			foreach ( $this->tts->voices as $key => $value ) {?>
				<option value="<?php esc_attr_e( $key );?>" <?php selected( $this->ttsaudio_options['default_voice'], esc_attr( $key ) ); ?>><?php esc_html_e( $value );?></option>
			<?php }?>

		</select> <p class="description"><?php _e( 'You can change this option in Add/Edit posts screen.', 'ttsaudio' ); ?></p>
		<?php
	}

	public function fpt_api_key_callback() {
		printf(
			'<input class="regular-text" type="text" name="ttsaudio_options[fpt_api_key]" id="fpt_api_key" value="%s">',
			isset( $this->ttsaudio_options['fpt_api_key'] ) ? esc_attr( $this->ttsaudio_options['fpt_api_key']) : ''
		);
		echo '<p class="description">'.__('If you don\'t use Vietnamese, leave it blank.').' <a href="https://console.fpt.ai/" target="_blank"><small>Get FPT API KEY</small></a> (max 5000 characters)</p>';
	}

	public function field_custom_skins_callback() {?>
		<style>
		.wrap .widget .widget-content,
		.wrap .widget .widget-control-actions{
		  padding: 10px;
		}
		.wrap .repeater em{
			color: #888;
		}
		.wrap .repeater .heading > th{
			padding: 0;
			padding-top:20px;
		}
		</style>
		<div class="repeater" >
			<div data-repeater-list="ttsaudio_options[custom_skins]">
				<?php
				$custom_skins[0] = array(
					'bg_color' => '',
					'bg_img' => '',
					'btn_curt_color' => '',
					'btn_hover_bg' => '',
					'thumb_color' => '',
					'track_color' => '',
					'buffer_color' => '',
					'played_voldis' => '',
					'pll_text_color' => '',
					'pll_hover_color' => '',
					'pll_div_color' => '',
				);
		    if( !empty($this->ttsaudio_options['custom_skins']) ) $custom_skins = $this->ttsaudio_options['custom_skins'];

				foreach($custom_skins as $i => $skin):?>
				<div data-repeater-item class="widget">

					<div class="widget-top">
						<div class="widget-title-action">
							<button type="button" class="widget-action toggle_btn" aria-expanded="true">
								<span class="toggle-indicator" aria-hidden="true"></span>
							</button>
						</div>
						<div class="widget-title ui-sortable-handle">
							<h3>Skin <span class="in-widget-title">#<span class="repeaterItemNumber"><?php echo ($i+1);?></span></span></h3>
						</div>
					</div>

					<div class="widget-inside">

							<div class="widget-content">

								<table class="form-table">
									<tbody>
										<tr>
											<th>Background color</th>
											<td><input name="bg_color" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['bg_color']);?>"></td>
										</tr>
										<tr>
											<th>Background image <p class="description"><small><?php _e('Note: dont include "background-image:".', 'ttsaudio');?></small></p></th>
											<td>
												<input name="bg_img" type="text" class="large-text" value="<?php esc_attr_e( $skin['bg_img'] ); ?>">
												<p class="description"><small><?php printf('%1$s <a href="%2$s" target="_blank">%2$s</a>', __('Nice CSS backgrounds', 'ttsaudio'), 'https://www.gradientmagic.com/' ) ;?></small></p></td>
										</tr>
										<tr class="heading">
											<th colspan="2"><em>CONTROLS</em><hr /></th>
										</tr>
										<tr>
											<th>Buttons & Current time</th>
											<td><input name="btn_curt_color" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['btn_curt_color']);?>"></td>
										</tr>
										<tr>
											<th>Buttons hover background</th>
											<td><input name="btn_hover_bg" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['btn_hover_bg']);?>"></td>
										</tr>
										<tr>
											<th>Thumb color</th>
											<td><input name="thumb_color" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['thumb_color']);?>"></td>
										</tr>
										<tr>
											<th>Track color</th>
											<td><input name="track_color" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['track_color']);?>"></td>
										</tr>
										<tr>
											<th>Buffer color</th>
											<td><input name="buffer_color" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['buffer_color']);?>"><small><?php _e('(for progress bar)', 'ttsaudio');?></small></td>
										</tr>
										<tr>
											<th>Played & Volume display</th>
											<td><input name="played_voldis" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['played_voldis']);?>"></td>
										</tr>
										<tr class="heading">
											<th colspan="2"><em>PLAYLIST</em><hr /></th>
										</tr>
										<tr>
											<th>Text color</th>
											<td><input name="pll_text_color" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['pll_text_color']);?>"></td>
										</tr>
										<tr>
											<th>Text hover</th>
											<td><input name="pll_hover_color" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['pll_hover_color']);?>"></td>
										</tr>
										<tr>
											<th>Divider color</th>
											<td><input name="pll_div_color" type="text" class="color-picker" data-alpha="true" value="<?php esc_attr_e($skin['pll_div_color']);?>"></td>
										</tr>
									</tbody>
								</table>
							</div>

							<div class="widget-control-actions">

								<div class="alignright">
									<button data-repeater-delete type="button" class="button-link button-link-delete">Delete</button>
								</div>
								<br class="clear">
							</div>

					</div>
				</div>
			<?php endforeach;?>
			</div>
			<input data-repeater-create  class="button" type="button" value="<?php _e('+ Add Skin', 'ttsaudio');?>"/>
		</div>


	<?php
	}

}
if ( is_admin() )
	$ttsaudio = new TTSAudio_Plugin_Options();