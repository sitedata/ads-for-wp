<?php
//Metabox displays in admin sidebar to show amp compatibilty is enabled or disabled
class adsforwp_metaboxes_amp_compatibility {
	private $screen = array(
		'adsforwp',
	);
	private $meta_fields = array(
		array(
			
			'id' => 'ads-for-wp_amp_compatibilty',
			'type' => 'radio',
			'options' => array(
				'enable'=>'Activated',
				'disable'=>'Deactivate',
			),
		),
	);
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'adsforwp_add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'adsforwp_save_fields' ) );
	}
	public function adsforwp_add_meta_boxes() {
		foreach ( $this->screen as $single_screen ) {
			add_meta_box(
				'ampcompatibility',
				esc_html__( 'AMP', 'ads-for-wp' ),
				array( $this, 'adsforwp_meta_box_callback' ),
				$single_screen,
				'side',
				'low'
			);
		}
	}
	public function adsforwp_meta_box_callback( $post ) {
		wp_nonce_field( 'adsforwp_amp_data', 'adsforwp_amp_nonce' );
                	echo "<p>".esc_html__('AMP compatibility has been activated', 'ads-for-wp')."</p>";
		$this->adsforwp_field_generator( $post );
	}
	public function adsforwp_field_generator( $post ) {
		$output = '';
		foreach ( $this->meta_fields as $meta_field ) {
                        $meta_field_label = isset($meta_field['label']);			
			$meta_value = get_post_meta( $post->ID, $meta_field['id'], true );
			if ( empty( $meta_value ) ) {
				$meta_value = isset($meta_field['default']);
                                if(empty($meta_value)){
                                $meta_value ='enable';   
                               }
                                }
			switch ( $meta_field['type'] ) {
				case 'radio':
					$input = '<fieldset>';
					$input .= '<legend class="screen-reader-text">' . isset($meta_field['label']) . '</legend>';
					$i = 0;
					foreach ( $meta_field['options'] as $key => $value ) {
						$meta_field_value = !is_numeric( $key ) ? $key : $value;
						$input .= sprintf(
							'<label style="padding-right:10px;"><input %s id=" % s" name="% s" type="radio" value="% s"> %s</label>%s',
							$meta_value === $meta_field_value ? 'checked' : '',
							$meta_field['id'],
							$meta_field['id'],
							$meta_field_value,
							esc_html__($value, 'ads-for-wp'),
							$i < count( $meta_field['options'] ) - 1 ? '' : ''
						);
						$i++;
					}
					$input .= '</fieldset>';
					break;
				default:
					$input = sprintf(
						'<input %s id="%s" name="%s" type="%s" value="%s">',
						$meta_field['type'] !== 'color' ? 'style="width: 100%"' : '',
						$meta_field['id'],
						$meta_field['id'],
						$meta_field['type'],
						$meta_value
					);
			}
			$output .= $this->adsforwp_format_rows($input);
		}
                $common_function_obj = new adsforwp_admin_common_functions();
                $allowed_html = $common_function_obj->adsforwp_expanded_allowed_tags();
		echo '<table class="form-table"><tbody>' . wp_kses($output, $allowed_html) . '</tbody></table>';
	}
	public function adsforwp_format_rows($input) {
		return '<tr><td style="padding:0px;">'.$input.'</td></tr>';
	}
	public function adsforwp_save_fields( $post_id ) {
		if ( ! isset( $_POST['adsforwp_amp_nonce'] ) )
			return $post_id;		
		if ( !wp_verify_nonce( $_POST['adsforwp_amp_nonce'], 'adsforwp_amp_data' ) )
			return $post_id;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		foreach ( $this->meta_fields as $meta_field ) {
			if ( isset( $_POST[ $meta_field['id'] ] ) ) {
				switch ( $meta_field['type'] ) {
					case 'email':
						$_POST[ $meta_field['id'] ] = sanitize_email( $_POST[ $meta_field['id'] ] );
						break;
					case 'text':
						$_POST[ $meta_field['id'] ] = sanitize_text_field( $_POST[ $meta_field['id'] ] );
						break;
				}
				update_post_meta( $post_id, $meta_field['id'], $_POST[ $meta_field['id'] ] );
			} else if ( $meta_field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, $meta_field['id'], '0' );
			}
		}
	}
}
if (class_exists('adsforwp_metaboxes_amp_compatibility')) {
	new adsforwp_metaboxes_amp_compatibility;
};