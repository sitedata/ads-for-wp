<?php
/**
 * Generated by the WordPress Meta Box generator
 * at http://jeremyhixon.com/tool/wordpress-meta-box-generator/
 */

function adsforwp_get_meta_post( $value, $post_id = '' ) {
	global $post;
	$field  	= "";
	$default 	= "";

	if ( empty( $post_id ) ) {
		$post_id = $post->ID;
	}

	$default 	= "show"; 

	if ( $value === 'adsforwp_incontent_ads_paragraphs') {
		$selected_ads_for   = get_post_meta(get_ad_id(get_the_ID()),'select_ads_for',true);
		    if('ampforwp' === $selected_ads_for){
		      $doint   = get_post_meta(get_ad_id(get_the_ID()),'incontent_ad_type',true);
		      $default = $doint;
		    }
		    elseif('amp_by_automattic' === $selected_ads_for){
		      $default   = get_post_meta(get_ad_id(get_the_ID()),'_amp_incontent_ad_type',true);
		      
		    }

	}

	$field = get_metadata('post',$post_id, $value, true );

	if('adsforwp-advert-data' === $value){
		$selected_ads_for 	= get_post_meta(get_ad_id(get_the_ID()),'select_ads_for',true);
			$cpt_paragraph = '';
			if(is_array($field)){
				foreach ($field as $key => $value ) {
					if(empty($value)){
			              $value = array(
			                      'post_id' => '',
			                      'ads_id' => '',
			                      'visibility' => '',
			                      'paragraph' => '',
			                      );
			            }
					$selected_ads_for 	= get_post_meta($key,'select_ads_for',true);
						if('ampforwp' === $selected_ads_for){
							$cpt_paragraph = get_post_meta($key,'incontent_ad_type',true);
						}elseif('amp_by_automattic' === $selected_ads_for){
							$cpt_paragraph = get_post_meta($key,'_amp_incontent_ad_type',true);
						}
						$cpt_paragraph = $cpt_paragraph[0];
						$value['post_id'] = get_the_ID();
						$value['ads_id']  = $key;
						$visi = get_post_meta($key,'ad_visibility_status',true);
						if('show' === $visi){
						$value['visibility'] = 'show';
						}
						else{
							$value['visibility'] = 'hide';
						} 
						$value['paragraph'] = $cpt_paragraph;
						$field[$key] = $value;
				}
			}
		
	}


	if ( ! empty( $field ) ) {
		return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
	} else {
		return $field = $default;
	}
}

function adsforwp_ads_meta_box() {

	$ampforwp_post_types = " ";

	$ampforwp_post_types = adsforwp_post_types();


	foreach ($ampforwp_post_types as $ampforwp_post_type => $value ) {

		add_meta_box(
			'adsforwp_ads_meta_box',
			esc_html__( 'Ads on this ' . $value, 'ads-for-wp' ),
			'adsforwp_ads_meta_box_html',
			$ampforwp_post_type,
			'side',
			'default'
		);
	}
}

add_action( 'add_meta_boxes', 'adsforwp_ads_meta_box' );

function adsforwp_ads_meta_box_html( $post ) {
	$ampforwp_post_types = " ";
	wp_nonce_field( '_adsforwp_ads_meta_box_nonce', 'adsforwp_ads_meta_box_nonce' ); ?>

	<input type="text" class="screen-reader-text" id="adsforwp-current-ad-status" value="<?php echo adsforwp_get_meta_post( 'adsforwp_ads_meta_box_ads_on_off' ); ?>">
	<p class="adsforwp-ads-controls">
		<input type="radio" name="adsforwp_ads_meta_box_ads_on_off" id="adsforwp_ads_meta_box_radio_show" value="show" <?php echo ( adsforwp_get_meta_post( 'adsforwp_ads_meta_box_ads_on_off' ) === 'show' ) ? 'checked' : ''; ?>>
		<label for="adsforwp_ads_meta_box_radio_show"><?php esc_attr_e('Show', 'ads-for-wp') ?></label> 
	</p>
	<p class="adsforwp-ads-controls">
		<input type="radio" name="adsforwp_ads_meta_box_ads_on_off" id="adsforwp_ads_meta_box_radio_hide" value="hide" <?php echo ( adsforwp_get_meta_post( 'adsforwp_ads_meta_box_ads_on_off' ) === 'hide' ) ? 'checked' : ''; ?>>
		<label for="adsforwp_ads_meta_box_radio_hide"><?php esc_attr_e('Hide', 'ads-for-wp') ?></label><br>
	</p>

	<div id="adsforwp-all-ads" style="display: none">
		<input hidden type="text" id="current-post-id" value="<?php echo get_the_ID();?>">
		<?php adsforwp_generate_ad_post_type_data();?>
	</div> <?php
}

function adsforwp_generate_ad_post_type_data(){

	$query 		= "";
	$post_id 	= "";
	$count 		= "";
	$ad_type 	= "";
	$visibility = "";
	$paragraph 	= "";
	$all_ads_info = "";
	$selected  	= '';

	$ad_data 			= array();
	$all_ads_from_db 	= array();
	$updated_ads_array 	= array();

	$post_id 		= 	get_the_ID();
	
	// $all_ads_info 	=  (array) adsforwp_get_meta_post( 'adsforwp-advert-data', $post_id );
	$all_ads_info 	=  (array) get_post_meta( $post_id, 'adsforwp-advert-data',true );
	
	if ( 'show' === $all_ads_info[0] )
		$all_ads_info = array();
	$check = $all_ads_info;

	if ( ! empty( $all_ads_info ) ) {
	 	$all_ads_info = array_merge($all_ads_info);
	}

	foreach ($all_ads_info as $key => $value) {
		if ( ! empty( $value['ads_id'] ) ) {
			$all_ads_from_db[] 						=  $value['ads_id'];
			$updated_ads_array[$value['ads_id']] 	= $value;
		}
	}
	$count = 0;
	$selected_ads_for   = get_post_meta(get_ad_id($post_id),'select_ads_for',true);

    if('ampforwp' === $selected_ads_for) {
	      $get_all_ads = get_posts( array( 'post_type' => 'ads-for-wp-ads','posts_per_page' => -1, 
			'meta_query' => array(
				array(
					'key' 	=> 'ad_type_format',
					'value' => '2',
				)
			)
		) );
    }
    elseif('amp_by_automattic' === $selected_ads_for) {
	      $get_all_ads = get_posts( array( 'post_type' => 'ads-for-wp-ads','posts_per_page' => -1, 
			'meta_query' => array(
				array(
					'key' 	=> '_amp_ad_type_format',
					'value' => '2',
				)
			)
		) );
    }	
	 
	if ( $get_all_ads ) {
		foreach ( $get_all_ads as $ad ) :

			$selected_ads_for   = get_post_meta($ad->ID,'select_ads_for',true);
		    
		    if('ampforwp' === $selected_ads_for){		    	 
			    $visibility  =  get_post_meta($ad->ID, 'ad_visibility_status', true );		    
		    }
		    elseif('amp_by_automattic' === $selected_ads_for){		    	
			    $visibility  =  get_post_meta($ad->ID, '_amp_ad_visibility_status', true );
		    }

			    $ads_post_id 	= 	$ad->ID;
			    $ad_type  		= 'show';

			    $ads_paragraph 	=  adsforwp_get_meta_post( 'incontent_ad_type', $ads_post_id );
			    if ( $ads_paragraph ) {
			    	$paragraph = $ads_paragraph;
			    }

				if ( 'show' === $ad_type ) {


					if ( $all_ads_from_db ) {
						$ad_found = in_array($ads_post_id, $all_ads_from_db);
					}

					if( isset($ad_found ) && $ad_found){
					    if ( ! empty(  $updated_ads_array[$ads_post_id]['visibility'] ) ) {
					    	$visibility = $updated_ads_array[$ads_post_id]['visibility'] ;
					    }
					    if ( ! empty(  $updated_ads_array[$ads_post_id]['paragraph'] ) ) {
					     	$paragraph = $updated_ads_array[$ads_post_id]['paragraph'] ;
					    }
					}
					if ( !empty($check)) {
						if( isset($check[$ads_post_id]['paragraph'])){
							 $paragraph = $check[$ads_post_id]['paragraph'];
							 $visibility = $check[$ads_post_id]['visibility'];
						}
					}

				    echo '<div data-ads-id="'.$ads_post_id.'" id="ad-control-child-'.$count.'">'; ?>
					   	<?php esc_attr_e('Ad name: ', 'ads-for-wp') ?><?php esc_attr_e( $ad->post_title ); ?> <br />
						
						<select  data-ad-visibility="<?php esc_attr_e($visibility); ?>" name="post_specific_visi" class="ads-visibility widefat" id="ad-visibility-<?php esc_attr_e($count) ?>" disabled="disabled">

							<option value="show" <?php if ( $visibility == "show" ) echo 'selected="selected"'; ?>><?php esc_attr_e('Show:', 'ads-for-wp') ?></option> 				
							<option value="hide" <?php if ( $visibility == "hide" ) echo 'selected="selected"'; ?>><?php esc_attr_e('Hide:', 'ads-for-wp') ?></option>
						</select>
						
				   		<label for="ad-paragraph-<?php echo esc_attr($count); ?>"> <?php esc_attr_e('Paragraph Position:', 'ads-for-wp') ?></label>
				   		<input class="small-text" id="ad-paragraph-<?php echo esc_attr($count);?>" data-ad-paragraph=" <?php echo esc_attr($paragraph); ?>" type="number" disabled="disabled" value="<?php echo esc_attr($paragraph); ?>" >

				   		<span class='edit-ads'> <?php esc_attr_e('Edit:', 'ads-for-wp') ?></span>
				   		<span class="save-ads" style="display:none"> <?php esc_attr_e('Save:', 'ads-for-wp') ?></span>
				   		<div class="spinner"></div>
				   		<br /><br />
				   		<?php

					echo "</div>";
				}
				$count++;
			
		endforeach;
	}
		wp_reset_postdata();
}

function adsforwp_ads_meta_box_save( $post_id ) {

	// Return if user does not have proper permission or security nonce is failed 
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['adsforwp_ads_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['adsforwp_ads_meta_box_nonce'], '_adsforwp_ads_meta_box_nonce' ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	// Save Data
	if ( isset( $_POST['adsforwp_ads_meta_box_ads_on_off'] ) )
		update_post_meta( $post_id, 'adsforwp_ads_meta_box_ads_on_off', esc_attr( $_POST['adsforwp_ads_meta_box_ads_on_off'] ) );

	// All posts Ads on-off
	if ( isset( $_POST["adsforwp_ads_controller_default"] ) )
		update_post_meta( $post_id, "adsforwp_ads_controller_default", esc_attr( $_POST["adsforwp_ads_controller_default"] ) );

	if ( isset( $_POST["adsforwp_ads_position"] ) )
		update_post_meta( $post_id, "adsforwp_ads_position", esc_attr( $_POST["adsforwp_ads_position"] ) );

	// Incontent Sub Controls
	if ( isset( $_POST["adsforwp_incontent_ads_default"] ) )
		update_post_meta( $post_id, "adsforwp_incontent_ads_default", esc_attr( $_POST["adsforwp_incontent_ads_default"] ) );

	if ( isset( $_POST["adsforwp_incontent_ads_paragraphs"] ) )
		update_post_meta( $post_id, "adsforwp_incontent_ads_paragraphs", esc_attr( $_POST["adsforwp_incontent_ads_paragraphs"] ) );
}
add_action( 'save_post', 'adsforwp_ads_meta_box_save' );

/*
	Usage: adsforwp_get_meta_post( 'adsforwp_ads_meta_box_ads_on_off' );
*/

/*
 * Creating ShortCode meta box for the users to get the ad code.
 
// add_action( 'add_meta_boxes', 'adsforwp_generate_ads_shortcode' );
function adsforwp_generate_ads_shortcode(){

	add_meta_box(
		'adsforwp_ads_shortcode',
		esc_html__( 'Ad Code ', 'ads-for-wp' ),
		'adsforwp_ads_shortcode_html',
		'ads-for-wp-ads',
		'side',
		'high'
	);
}

//  

function adsforwp_ads_shortcode_html( $post ) {
	wp_nonce_field( '_adsforwp_ads_meta_box_nonce', 'adsforwp_ads_meta_box_nonce' ); ?>

	<div class="ads-on-off-controller">
		<p> Ads </p>
		<p class="incontent-radio">
			<input type="text" class="screen-reader-text" id="adsforwp-current-ad-default" value="<?php echo adsforwp_get_meta_post( 'adsforwp_ads_controller_default' );?>">

			<input type="radio" name="adsforwp_ads_controller_default" id="adsforwp_ads_controller_default_show" value="show" <?php echo ( adsforwp_get_meta_post( 'adsforwp_ads_controller_default' ) === 'show' ) ? 'checked' : ''; ?> >
			<label for="adsforwp_ads_controller_default_show"> Show </label> 

			<input type="radio" name="adsforwp_ads_controller_default" id="adsforwp_ads_controller_default_hide" value="hide" <?php echo ( adsforwp_get_meta_post( 'adsforwp_ads_controller_default' ) === 'hide' ) ? 'checked' : ''; ?> >
			<label for="adsforwp_ads_controller_default_hide"> Hide </label> 
		</p>
	</div>
	
	<div id="adsforwp-ads-control-wrapper">		
		<input type="text" class="screen-reader-text" id="adsforwp-current-ad-type" value="<?php echo adsforwp_get_meta_post( 'adsforwp_ads_position' );?>">
		<p>
			<input type="radio" name="adsforwp_ads_position" id="adsforwp_ads_position_global" value="show" <?php echo ( adsforwp_get_meta_post( 'adsforwp_ads_position' ) === 'show' ) ? 'checked' : ''; ?>>
			<label for="adsforwp_ads_position_global"> Global </label> 
			
			<p><code id="adsforwp_position_global_code"> [ads-for-wp ads-id="<?php echo get_the_ID(); ?>"]</code></p> 

		</p>
		<div>
			<input type="radio" name="adsforwp_ads_position" id="adsforwp_ads_position_specific" value="hide" <?php echo ( adsforwp_get_meta_post( 'adsforwp_ads_position' ) === 'hide' ) ? 'checked' : ''; ?>>
			<label for="adsforwp_ads_position_specific"> Incontent </label> 

			<div id="adsforwp_ads_position_specific_controls" style="display: none">
				<div>
					<p> Default Ads </p>
					<p class="incontent-radio">
						<input type="radio" name="adsforwp_incontent_ads_default" id="adsforwp_incontent_ads_default_show" value="show" <?php echo ( adsforwp_get_meta_post( 'adsforwp_incontent_ads_default' ) === 'show' ) ? 'checked' : ''; ?>>
						<label for="adsforwp_incontent_ads_default_show"> Show </label> 

						<input type="radio" name="adsforwp_incontent_ads_default" id="adsforwp_incontent_ads_default_hide" value="hide" <?php echo ( adsforwp_get_meta_post( 'adsforwp_incontent_ads_default' ) === 'hide' ) ? 'checked' : ''; ?>>
						<label for="adsforwp_incontent_ads_default_hide"> Hide </label> 
					</p>
	 
					<p> <label for="adsforwp_incontent_ads_paragraphs">Show ads after </label>
						<input type="number" max="30" min="1" value="<?php echo adsforwp_get_meta_post( 'adsforwp_incontent_ads_paragraphs' );?>" id="adsforwp_incontent_ads_paragraphs" class="ads-paragraphs small-text"  name="adsforwp_incontent_ads_paragraphs">
						<label for="adsforwp_incontent_ads_paragraphs">paragraphs. </label>
						
					</p>
				</div>
			</div>

		</div>
	</div>
	<?php
}
*/

// HELP METABOX

add_action( 'add_meta_boxes', 'adsforwp_help_metabox' );
function adsforwp_help_metabox(){

	add_meta_box(
		'adsforwp_help_metabox',
		esc_html__( 'Help?', 'ads-for-wp' ),
		'adsforwp_help_links',
		'ads-for-wp-ads',
		'side',
		'low'
	);
} 

function adsforwp_help_links(){
	wp_nonce_field( '_adsforwp_ads_meta_box_nonce', 'adsforwp_ads_meta_box_nonce' ); ?>
	<div class="ads-for-wp-help">
		<p><a target="_blank" href="https://ampforwp.com/tutorials/article/work-ads-for-wp/"><?php esc_attr_e('Documentation', 'ads-for-wp') ?></a></p>
		<p><a target="_blank" href="https://ampforwp.com/amp-ads-beta-form/"><?php esc_attr_e('Feedback', 'ads-for-wp') ?></a></p>
		<p><a target="_blank" href="https://ampforwp.com/amp-ads-beta-form/"><?php esc_attr_e('Request a feature', 'ads-for-wp') ?></a></p>
		<p><a target="_blank" href="https://ampforwp.com/amp-ads-beta-form/"><?php esc_attr_e('Report a bug', 'ads-for-wp') ?></a></p>
	</div>
<?php }