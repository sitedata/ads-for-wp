<?php
/**
 * This class handle all the user end related functions
 */
class adsforwp_output_functions{
    
    private $is_amp = false;     
    public  $visibility = null;

    public function __construct() {       
        
        
        
        add_filter('the_content', array($this, 'adsforwp_display_ads'));            
        add_shortcode('adsforwp', array($this,'adsforwp_manual_ads'));
        add_shortcode('adsforwp-group', array($this, 'adsforwp_group_ads'));
        add_action('wp_ajax_nopriv_adsforwp_get_groups_ad', array($this, 'adsforwp_get_groups_ad'));
        add_action('wp_ajax_adsforwp_get_groups_ad', array($this, 'adsforwp_get_groups_ad'));
    }
    /**
     * This hook function display content in post. we are modifying post content here
     * @param type $content
     * @return type string
     */
    public function adsforwp_display_ads($content){
        if ( is_single() ) {                       
        $current_post_data = get_post_meta(get_the_ID(),$key='',true);                  
        if(array_key_exists('ads-for-wp-visibility', $current_post_data)){
        $this->visibility = $current_post_data['ads-for-wp-visibility'][0];    
        }
        if ((function_exists( 'ampforwp_is_amp_endpoint' ) && ampforwp_is_amp_endpoint()) || function_exists( 'is_amp_endpoint' ) && is_amp_endpoint()) {
            $this->is_amp = true;        
        }         
        if($this->visibility != 'hide') {
            $all_ads_post = json_decode(get_transient('adsforwp_transient_ads_ids'), true);  
            
            foreach($all_ads_post as $ads){                               
            $post_ad_id = $ads;             
            $common_function_obj = new adsforwp_admin_common_functions();
            $in_group = $common_function_obj->adsforwp_check_ads_in_group($post_ad_id);
           
            if(empty($in_group)){                
            $where_to_display=""; 
            $adposition="";    
            $post_meta_dataset = array();
            $post_meta_dataset = get_post_meta($post_ad_id,$key='',true);
            $ad_code =  $this->adsforwp_get_ad_code($post_ad_id); 
            if(array_key_exists('wheretodisplay', $post_meta_dataset)){
            $where_to_display = $post_meta_dataset['wheretodisplay'][0];  
            }
            if(array_key_exists('adposition', $post_meta_dataset)){
            $adposition = $post_meta_dataset['adposition'][0];    
            }
                                                                                                                                             
           //Displays all ads according to their settings paragraphs starts here              
            switch ($where_to_display) {
                
             case 'after_the_content':
              $content = $content.$ad_code;
              break;
          
             case 'before_the_content':
              $content = $ad_code.$content;
              break;
          
             case 'between_the_content':        
              if($adposition == 'number_of_paragraph'){
                $paragraph_id = $post_meta_dataset['paragraph_number'][0];   
                $closing_p = '</p>';
                $paragraphs = explode( $closing_p, $content );   
                foreach ($paragraphs as $index => $paragraph) {

                 if ( trim( $paragraph ) ) {
                       $paragraphs[$index] .= $closing_p;
                   }
                   if ( $paragraph_id == $index + 1 ) {
                       $paragraphs[$index] .= $ad_code;
                   }
                 }
                        $content = implode( '', $paragraphs );
                }
        
               if($adposition == '50_of_the_content'){
                 $closing_p = '</p>';
                 $paragraphs = explode( $closing_p, $content );       
                 $total_paragraphs = count($paragraphs);
                 $paragraph_id = round($total_paragraphs /2);       
                 foreach ($paragraphs as $index => $paragraph) {
                    if ( trim( $paragraph ) ) {
                        $paragraphs[$index] .= $closing_p;
                    }
                    if ( $paragraph_id == $index + 1 ) {
                        $paragraphs[$index] .= $ad_code;
                    }
                  }
                   $content = implode( '', $paragraphs ); 
                 }
                break;             
             default:
               break;
          }      
          //Displays all ads according to their settings paragraphs ends here   
          
          
            }
         }                          
       }
         }
        return $content;    
    }
    
    /**
     * we are generating html or amp code for ads which will be displayed in post content.
     * @param type $post_ad_id
     * @return string 
     */
    public function adsforwp_get_ad_code($post_ad_id){
    
            $ad_image="";
            $ad_type="";
            $ad_code ="";  
            $ad_expire_to ="";
            $ad_expire_from ="";
            $custom_ad_code="";
            $where_to_display="";                        
            $amp_compatibility ="";              
            $adsforwp_ad_expire_enable ="";                        
            $adsforwp_ad_days_enable ="";
            $adsforwp_ad_expire_days =array();
            $post_meta_dataset = array();
            $post_meta_dataset = get_post_meta($post_ad_id,$key='',true);              
            
            if(array_key_exists('custom_code', $post_meta_dataset)){
            $custom_ad_code = $post_meta_dataset['custom_code'][0];    
            }
            if(array_key_exists('adsforwp_ad_image', $post_meta_dataset)){
            $ad_image = $post_meta_dataset['adsforwp_ad_image'][0];    
            }
            if(array_key_exists('wheretodisplay', $post_meta_dataset)){
            $where_to_display = $post_meta_dataset['wheretodisplay'][0];  
            }            
            if(array_key_exists('select_adtype', $post_meta_dataset)){
            $ad_type = $post_meta_dataset['select_adtype'][0];      
            }
            if(array_key_exists('adsforwp_ad_expire_day_enable', $post_meta_dataset)){
            $adsforwp_ad_expire_enable = $post_meta_dataset['adsforwp_ad_expire_day_enable'][0];      
            }            
            if(array_key_exists('adsforwp_ad_expire_from', $post_meta_dataset)){
            $ad_expire_from = $post_meta_dataset['adsforwp_ad_expire_from'][0];      
            }
            if(array_key_exists('adsforwp_ad_expire_to', $post_meta_dataset)){
            $ad_expire_to = $post_meta_dataset['adsforwp_ad_expire_to'][0];      
            }
            if(array_key_exists('adsforwp_ad_expire_day_enable', $post_meta_dataset)){
            $adsforwp_ad_days_enable = $post_meta_dataset['adsforwp_ad_expire_day_enable'][0];      
            }
            if(array_key_exists('adsforwp_ad_expire_days', $post_meta_dataset)){
            $adsforwp_ad_expire_days = get_post_meta($post_ad_id,$key='adsforwp_ad_expire_days',true); ;      
            }                                                          
            if($ad_type !=""){                                        
            if(array_key_exists('ads-for-wp_amp_compatibilty', $post_meta_dataset)){
            $amp_compatibility = $post_meta_dataset['ads-for-wp_amp_compatibilty'][0];    
            }                
            switch ($ad_type) {
            case 'custom':
                    if($this->is_amp){
                     if($amp_compatibility == 'enable'){
                     $ad_code = '<div class="afw afw_custom afw_'.$post_ad_id.'">
							'.$custom_ad_code.'
							</div>';    
                    }   
                    }else{
                    $ad_code = '<div class="afw afw_custom afw_'.$post_ad_id.'">
							'.$custom_ad_code.'
							</div>';        
                    }                                                                                
            break;
            case 'ad_image':
                    if($this->is_amp){
                     if($amp_compatibility == 'enable'){
                     $ad_code = '<div class="afw afw_ad_image afw_'.$post_ad_id.'">
							<amp-img src="'.$ad_image.'" layout="responsive" height="300" width="400"></amp-img>
							</div>';    
                    }   
                    }else{
                    $ad_code = '<div class="afw afw_ad_image afw_'.$post_ad_id.'">
							<img src="'.$ad_image.'">
							</div>';        
                    }                                                                                
            break;
           //adsense ads logic code starts here
            case 'adsense':
                        
            $ad_client = $post_meta_dataset['data_client_id'][0];
            $ad_slot = $post_meta_dataset['data_ad_slot'][0];    
            $width='200';
            $height='200';
            $banner_size = $post_meta_dataset['banner_size'][0];    
            if($banner_size !=''){
            $explode_size = explode('x', $banner_size);            
            $width = $explode_size[0];            
            $height = $explode_size[1];                               
            }            
            if($this->is_amp){
                if($amp_compatibility == 'enable'){
                 $ad_code = '<div class="afw afw-ga afw_'.$post_ad_id.'">
                                <amp-ad 
				type="adsense"
				width="'. esc_attr($width) .'"
				height="'. esc_attr($height) .'"
				data-ad-client="'. $ad_client .'"
				data-ad-slot="'.$ad_slot .'">
			    </amp-ad>
                            </div>';
                }                             
				                
            }else{                
             $ad_code = '<div class="afw afw-ga afw_'.$post_ad_id.'">
							<script async="" src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js">
							</script>
							<ins class="adsbygoogle" style="display:inline-block;width:'.esc_attr($width).'px;height:'.esc_attr($height).'px" data-ad-client="'.$ad_client.'" data-ad-slot="'.$ad_slot.'">
							</ins>
							<script>
								(adsbygoogle = window.adsbygoogle || []).push({});
							</script>
						</div>';   
            }                                    
            break;
            
            case 'media_net':
                        
            $ad_data_cid = $post_meta_dataset['data_cid'][0];
            $ad_data_crid = $post_meta_dataset['data_crid'][0];    
            $width='200';
            $height='200';
            $banner_size = $post_meta_dataset['banner_size'][0];    
            if($banner_size !=''){
            $explode_size = explode('x', $banner_size);            
            $width = $explode_size[0];            
            $height = $explode_size[1];                               
            }            
            if($this->is_amp){
                if($amp_compatibility == 'enable'){
                 $ad_code = 
                            '<div class="afw afw-md afw_'.$post_ad_id.'">
                            <amp-ad 
				type="medianet"
				width="'. esc_attr($width) .'"
				height="'. esc_attr($height) .'"
                                data-tagtype="cm"    
				data-cid="'. $ad_data_cid.'"
				data-crid="'.$ad_data_crid.'">
			    </amp-ad>;  
                            </div>';    
                }                             
				                
            }else{                
             $ad_code = '<div class="afw afw-md afw_'.$post_ad_id.'">
						<script id="mNCC" language="javascript">
                                                            medianet_width = "'.esc_attr($width).'";
                                                            medianet_height = "'.esc_attr($height).'";
                                                            medianet_crid = "'.$ad_data_crid.'"
                                                            medianet_versionId ="3111299"
                                                   </script>
                                                   <script src="//contextual.media.net/nmedianet.js?cid='.$ad_data_cid.'"></script>		
						</div>';   
            }                                    
            break;
            default:
            break;
        }      
              
            $current_date = date("Y-m-d"); 
            
            if($adsforwp_ad_expire_enable){
                
             if($ad_expire_from && $ad_expire_to )  {     
                 
                if($ad_expire_from <= $current_date && $ad_expire_to >=$current_date){
                    
                 if($adsforwp_ad_days_enable){
                     
                    foreach ($adsforwp_ad_expire_days as $days){
                        
                        if(date('Y-m-d', strtotime($days))==$current_date){
                            
                         return $ad_code;     
                        }
                    }      
                }else{
                return $ad_code;          
                }                                                        
                }                             
            }else{
              return $ad_code;    
            }
            }else{
              if($adsforwp_ad_days_enable){
                  
                    foreach ($adsforwp_ad_expire_days as $days){
                        
                        if(date('Y-m-d', strtotime($days))==$current_date){
                            
                        return $ad_code;     
                        }
                    }      
                }else{
                 return $ad_code;     
                }
            }
        
      }                         
}

    /**
     * We are displaying ads as per shortcode. eg ["adsforwp id="000"]
     * @param type $atts
     * @return type string
     */
    public function adsforwp_manual_ads($atts) {	
        if ( is_single() ) { 
        $post_ad_id =   $atts['id'];                  
        if($this->visibility != 'hide') {                                    
        $ad_code =  $this->adsforwp_get_ad_code($post_ad_id);          
        return $ad_code;  
        }
       }        
    }
    
    /**
     * We are displaying groups as per shortcode. eg [[adsforwp-group id="0000"]
     * @param type $atts
     * @return type string
     */
    public function adsforwp_group_ads($atts, $group_id = null, $widget=false) { 
        if ( is_single() || is_page()) { 
        $post_group_id  =   $atts['id']; 
        if($group_id){
        $post_group_id  =   $group_id;     
        }        
        if($this->visibility != 'hide') {
        $ad_code ="";    
        if($this->is_amp){
        $post_group_data = get_post_meta($post_group_id,$key='adsforwp_ads',true);         
        $ad_code =  $this->adsforwp_get_ad_code(array_rand($post_group_data));          
        return $ad_code;
        }else{
        $post_group_data = get_post_meta($post_group_id,$key='adsforwp_ads',true);     
        $post_data = get_post_meta($post_group_id,$key='',true);
        if($post_group_data){
        $adsresultset = array();  
        $response = array();           
        foreach($post_group_data as $post_ad_id => $post){
        $adsresultset[] = array(
                'ad_id' => $post_ad_id
        ) ;           
        }
        $response['afw_group_id'] = $post_group_id;
        $response['adsforwp_refresh_type'] = $post_data['adsforwp_refresh_type'][0];
        $response['adsforwp_group_ref_interval_sec'] = $post_data['adsforwp_group_ref_interval_sec'][0];
        $response['adsforwp_group_type'] = $post_data['adsforwp_group_type'][0];
        $response['ad_ids'] = $adsresultset;  
        if($response['adsforwp_refresh_type'] == 'on_interval'){
        $ad_code ='<div class="afw-groups-ads-json" afw-group-id="'.esc_attr($post_group_id).'" data-json="'. esc_attr(json_encode($response)).'">';           
        $ad_code .='</div>';    
        }else{
        $post_group_data = get_post_meta($post_group_id,$key='adsforwp_ads',true);         
        $ad_code =  $this->adsforwp_get_ad_code(array_rand($post_group_data));   
        }        
        }
        return $ad_code;                           
       } 
        }
        }              
}

    /**
     * This is a ajax handler function for ads groups. 
     * @return type json string
     */
    public function adsforwp_get_groups_ad(){  
        
        $ad_id = sanitize_text_field($_GET['ad_id']);        
        $ads_group_id = sanitize_text_field($_GET['ads_group_id']);
        $ads_group_type = sanitize_text_field($_GET['ads_group_type']);
        $ads_group_data = get_post_meta($ads_group_id,$key='adsforwp_ads',true);
        switch ($ads_group_type) {
            case 'rand':
            $ad_code =  $this->adsforwp_get_ad_code(array_rand($ads_group_data));
                break;
            
            case 'ordered':                
            $ad_code =  $this->adsforwp_get_ad_code($ad_id);    
                break;
            
            default:
                break;
        }                
        if($ad_code){
        echo json_encode(array('status'=> 't','ad_code'=> $ad_code));        
        }else{
        echo json_encode(array('status'=> 'f','ad_code'=> 'group code not available'));                                 
        }
        
           wp_die();           
}
}
if (class_exists('adsforwp_output_functions')) {
	new adsforwp_output_functions;
};
