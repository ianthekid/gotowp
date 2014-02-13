<?php
	/*
		Plugin Name: GoToWP Personal
		Plugin URI: http://www.gotowp.com/
		Description: Allow your users to easily register for your GoToWebinar webinars by simply placing a shortcode in any Wordpress post or page.
		Version: 1.0.7
		Author: GoToWP.com
		Author URI:  http://www.gotowp.com/
		Support: http://www.gotowp.com/support
	*/	

define('GOTOWP_PERSONAL_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('GOTOWP_PERSONAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('GOTOWP_PERSONAL_PLUGIN_VERSION', '1.0.5');
define('GOTOWP_PERSONAL_PLUGIN_SLUG', 'gotowp-personal');

$webinarErrors= new WP_Error();

register_activation_hook(__FILE__,'gotowp_personal_install');

function gotowp_personal_install(){	
   if(!function_exists('curl_exec'))
		{
		  deactivate_plugins(__FILE__);		
		  wp_die("Sorry, but you can't run this plugin, it requires curl."); 
		}

	global $wpdb;
	global $charset_collate;
	
    $webinar_table = $wpdb->prefix . "gotowp_personal_webinars";

	if ( get_option( 'gotowp_personal_organizer_key') === false ) {
       	 add_option( 'gotowp_personal_organizer_key', '', '', 'yes' );
    }
	if ( get_option( 'gotowp_personal_access_token') === false ) {
       	 add_option( 'gotowp_personal_access_token', '', '', 'yes' );
    }	
	
	if($wpdb->get_var("SHOW TABLES LIKE '$webinar_table'") != $webinar_table) {

		$sql ="CREATE TABLE IF NOT EXISTS $webinar_table (
					  id int(11) NOT NULL AUTO_INCREMENT,
					  firstName varchar(30) DEFAULT NULL,
					  lastName varchar(30) DEFAULT NULL,
					  email varchar(50) DEFAULT NULL,
					  webinar_id varchar(50) NOT NULL,
					  formdata longtext NOT NULL,
					  PRIMARY KEY (id)
					) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
			 
}

register_deactivation_hook(__FILE__,'gotowp_personal_uninstall');

function gotowp_personal_uninstall()
{
	//delete_option('gotowp_personal_organizer_key');
	//delete_option('gotowp_personal_access_token');	
}


if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		
	add_action( 'admin_enqueue_scripts', 'gotowp_personal_enqueue_admin_styles'  );
	
	function gotowp_personal_enqueue_admin_styles(){		
		$screen = get_current_screen();
		if ( 'toplevel_page_gotowp-personal-settings' == $screen->id ) {
			wp_enqueue_style( GOTOWP_PERSONAL_PLUGIN_SLUG . '-admin-css', GOTOWP_PERSONAL_PLUGIN_URL.'assets/css/admin.css', array(), GOTOWP_PERSONAL_PLUGIN_VERSION );
		}		
	}	
	
	/*ADDING A SETTINGS LINK BESIDE ACTIVATE/DEACTIVATE*/
	if ( ! defined( 'GOTOWP_PERSONAL_PLUGIN_BASENAME' ) ){
		define( 'GOTOWP_PERSONAL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
	}
	
	add_filter( 'plugin_action_links', 'gotowp_personal_plugin_action_links', 10, 8 );
	
	function gotowp_personal_plugin_action_links( $links, $file ) {
		if ( $file != GOTOWP_PERSONAL_PLUGIN_BASENAME )
			return $links;
		$settings_link = '<a href="'.menu_page_url( GOTOWP_PERSONAL_PLUGIN_SLUG.'-settings', false ).'">'.esc_html( __( 'Settings', 'gotowp_personal' )).'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}	
	
	
	/*ADD FREE WEBINAR DETAILS*/
	function gotowp_personal_plugin_add_webinar_details()
	{	
		if(isset($_POST['action']) && $_POST['action']=='gotowp_personal_savefreewebinar')
		{
			update_option( 'gotowp_personal_organizer_key',$_REQUEST['gotowp_personal_organizer_key']);
			update_option( 'gotowp_personal_access_token', $_REQUEST['gotowp_personal_access_token'] );
		}
		
		if(isset($_POST['action']) && $_POST['action']=='gotowp_personal_webinar_forms')
		{
            $response=gotowp_personal_get_webinars();
            $webinars_arr=json_decode($response);           
      
            foreach($webinars_arr as $web_key){     
            	$registrationUrl=$web_key->registrationUrl;
            	$web_key=str_replace('https://attendee.gotowebinar.com/register/','',$registrationUrl);
            	$web_key=trim($web_key); 	
            	webinar_update_registration_fields($web_key);            	
            }
            _e('Webinar forms updated successfully');
			
		}		
	
		?>

	<div class="wrap">
		<form name="gotowp_personal_adminsettings" id="gotowp_personal_adminsettings" action="" method="post">
			<table class="tableborder">
			    <thead>
			       <tr><th colspan="2" class="tableheader"><?php _e('Webinar Details'); ?></th></tr>
			    </thead>
			    <tbody>
			     <tr>
			        <td class="gotowp-description tableclass" colspan="2">The <b style="color:#090;">Organizer Key</b>  and  <b style="color:#090;">Access Token</b>  can be obtained from online application after authenticating with O-Auth connector for webinars (G2W OAuth Flow) by clicking the link below<br>
				    <a href="http://citrixonline-quick-oauth.herokuapp.com/" target="_blank"><b>http://citrixonline-quick-oauth.herokuapp.com/</b></a></td> 
				</tr>    
			    <tr>
			        <td class="gotowp-organizer-key tableclass"><?php _e('Organizer Key'); ?></td> 
			        <td><input type="text" size=40  value="<?php echo get_option('gotowp_personal_organizer_key'); ?>" name="gotowp_personal_organizer_key"  id="gotowp_personal_organizer_key"/></td>
			    </tr>
			    <tr>
			        <td class="gotowp-access-token tableclass"><?php _e('Access Token'); ?></td>  
			        <td><input type="text" size=40  value="<?php echo get_option('gotowp_personal_access_token'); ?>" name="gotowp_personal_access_token" id="gotowp_personal_access_token"/></td>
			    </tr>
			    <tr>
			        <td class="gotowp-action-hidden tableclass"><input  type="hidden" name="action" value="gotowp_personal_savefreewebinar" /></td>
			        <td><input class="gotowp-submit-button" id="savefreewebinar_submit" style="" type="submit" name="submit"  value="<?php _e('Save Details') ?>"/></td>
			    </tr>  
			    </tbody>
			</table>
		</form>
		
		<form name="gotowp_personal_webinar_forms" id="gotowp_personal_webinar_forms" action="" method="post">		
			<table class="tableborder">
			    <thead>
			        <tr><th colspan="2" class="tableheader"><?php _e('Refresh Webinar forms'); ?></th></tr>
			    </thead>
			    <tbody>
			    <tr><td><p class="gotowp-description">If you edit GoToWebinar's registration form after you create a webinar,<br/> 
			    you can display these changes on your Wordpress site by using the Refresh Webinar forms feature</p></td></tr>
			    <tr>
			        <td>
			           <input  type="hidden" name="action" value="gotowp_personal_webinar_forms" />
			           <input id="update_webinar_forms" style="" type="submit" name="submit"  value="<?php _e('Update Webinar forms') ?>"/>
			        </td>
			    </tr>  
			    </tbody>
			</table>	
		</form>	
		
		<table id="gotowp_personal_sample_table" class="tableborder">
		<thead>
			<tr class="heading_row"><th colspan="2" class="tableheader heading"><?php _e('Sample Usage of Shortcode'); ?><br/></th></tr>
			</thead>
			<tbody>
		    <tr class="sample_row" style="">
		      <td  style="" class="tableclass sample_title"><?php _e('On Registration Page'); ?></td>  
		      <td class="sample_field"><input style="" type="text" size=40  value="[register_free_webinar webid=xxxxxxx pageid=xxx]" name="shortcode" class="shortcode" /></td>    
		    </tr>
		    </tbody>
		</table>
	
	</div>
	<?php	
	} 	
	
	
	/*ADDING ADMIN MENU FOR SETTINGS*/
	add_action('admin_menu','gotowp_personal_admin_menu');
	function gotowp_personal_admin_menu() {
		add_menu_page('Webinar Plugin Settings', 'GOTOWP PERSONAL','administrator', GOTOWP_PERSONAL_PLUGIN_SLUG.'-settings','gotowp_personal_plugin_add_webinar_details',GOTOWP_PERSONAL_PLUGIN_URL.'assets/img/webinar.png');
	}	


}


else{	
	add_action( 'wp_enqueue_scripts', 'gotowp_personal_enqueue_styles'  );
	add_action( 'wp_enqueue_scripts', 'gotowp_personal_enqueue_scripts' );
	
	function gotowp_personal_enqueue_scripts(){
		global $post;
		if( has_shortcode( $post->post_content, 'register_free_webinar') ) {
			wp_enqueue_script( GOTOWP_PERSONAL_PLUGIN_SLUG . '-validate-js', GOTOWP_PERSONAL_PLUGIN_URL.'assets/js/jquery.validate.min.js', array( 'jquery' ), GOTOWP_PERSONAL_PLUGIN_VERSION );
		}
	}
	
	function gotowp_personal_enqueue_styles(){
		global $post;
		if( has_shortcode( $post->post_content, 'register_free_webinar') ) {
			wp_enqueue_style( GOTOWP_PERSONAL_PLUGIN_SLUG . '-public-style', GOTOWP_PERSONAL_PLUGIN_URL.'assets/css/public.css', array(), GOTOWP_PERSONAL_PLUGIN_VERSION );
		}
	}		
	
	add_action('init','gotowp_personal_save_before_registration');
	
	function gotowp_personal_save_before_registration()
	{
		global $webinarErrors;	

			

		
		if( isset($_REQUEST['action']) && $_REQUEST['action']=='gotowp_personal_register_webinars' )
		{
			$organizer_key= get_option('gotowp_personal_organizer_key');
			$access_token = get_option('gotowp_personal_access_token');
			$gtw_url = "https://api.citrixonline.com/G2W/rest/organizers/".$organizer_key."/webinars/".$_REQUEST['webinarid']."/registrants";
			$headers=array( 
							"HTTP/1.1",
							"Accept: application/json",
							"Accept: application/vnd.citrix.g2wapi-v1.1+json",
							"Content-Type: application/json",
							"Authorization: OAuth oauth_token=$access_token",
						   );
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_POST,0);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); 
			curl_setopt($curl, CURLOPT_URL, $gtw_url);
			curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$response= curl_exec($curl);
			curl_close($curl);
			$request=json_decode($response);
			
			foreach($request as $val)
			{
				$emails[]=$val->email;
			}
			
			if( in_array($_REQUEST['email'],$emails) )
			{			
				$webinarErrors->add('broke','This Email is already registered with this webinar');
			}
			else
			{
					global $wpdb;
	                $webinar_table = $wpdb->prefix . "gotowp_personal_webinars";
	                
	                $webinar_id=$_POST['webinarid'];                			
				    $registration_fields=webinar_get_registration_fields($webinar_id);
				    
				    $firstName=esc_attr($_POST['firstName']);
				    $lastName=esc_attr($_POST['lastName']);
				    $email=esc_attr($_POST['email']);
				    
				    unset($_POST['webinarid']);
				    unset($_POST['action']);
				    unset($_POST['submit']);
				    
				    $data=array(
				    		'firstname'=>$firstName,
	                        'lastName'=>$lastName,
							'email'=>$email,
							'webinar_id'=>$webinar_id,
							'formdata'=>json_encode($_POST),
					    );
				
				if($wpdb->insert( $webinar_table, $data)){	
					$return_url   = get_permalink($_POST['returnpageid']);
					$url= 'https://api.citrixonline.com/G2W/rest/organizers/'.$organizer_key.'/webinars/'.$webinar_id.'/registrants';
		
					$curl = curl_init($url);
					
					$curl_post_data=array();
					
					foreach($registration_fields->fields as $row):				
					   $curl_post_data[$row->field]=$_POST[$row->field];				
					endforeach;				
	
					$myOptions = array( 
						CURLOPT_POST => true, 
						CURLOPT_SSL_VERIFYHOST => 0, 
						CURLOPT_SSL_VERIFYPEER => 0, 
						CURLOPT_POSTFIELDS => json_encode($curl_post_data), 
						CURLOPT_URL => $url, 
						CURLOPT_RETURNTRANSFER => 1, 
						CURLOPT_HTTPHEADER => array( "Content-Type: application/json; charset=utf-8","Accept:application/json, text/javascript, */*; q=0.01", ("Authorization: OAuth oauth_token=".$access_token))
	                );
					curl_setopt_array($curl, $myOptions);
					$curl_response = curl_exec($curl);
					echo '<META HTTP-EQUIV="Refresh" Content="0; URL='.$return_url.'">'; 
				}
			}
		}	
	}

}



function webinar_get_registration_fields($web_key)
{
	$webinar_option_key='gotowp_personal_webinar_form_id_'.$web_key;
	if ( get_option( $webinar_option_key) !== false ) {
		$response=get_option($webinar_option_key);
	}
	else{
		webinar_update_registration_fields($web_key);
	}		
	$request=json_decode($response);
	return $request;
}


function webinar_update_registration_fields($web_key)
{
	$webinar_option_key='gotowp_personal_webinar_form_id_'.$web_key;	
	delete_option($webinar_option_key);

	$organizer_key= get_option('gotowp_personal_organizer_key');
	$access_token = get_option('gotowp_personal_access_token');
	$gtw_url = "https://api.citrixonline.com/G2W/rest/organizers/".$organizer_key."/webinars/".$web_key."/registrants/fields";
	$headers=array(
			"HTTP/1.1",
			"Accept: application/json",
			"Accept: application/vnd.citrix.g2wapi-v1.1+json",
			"Content-Type: application/json",
			"Authorization: OAuth oauth_token=$access_token",
	);
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_POST,0);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($curl, CURLOPT_URL, $gtw_url);
	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$response= curl_exec($curl);
	curl_close($curl);
	update_option( $webinar_option_key, $response, '', 'yes' );
}



function gotowp_personal_get_webinar($webinarKey){

	$webinar_option_key='gotowp_personal_webinar_id_'.$webinarKey;

	if ( get_option( $webinar_option_key) !== false ) {
		$response=get_option($webinar_option_key);
	}
	else{

		$organizer_key= get_option('gotowp_personal_organizer_key');
		$access_token = get_option('gotowp_personal_access_token');
		//https://api.citrixonline.com/G2W/rest/organizers/{organizerKey}/webinars/{webinarKey}
		$url='https://api.citrixonline.com/G2W/rest/organizers/'.$organizer_key.'/webinars/'.$webinarKey;
		$curl = curl_init($url);
			
		$myOptions = array(
				CURLOPT_POST => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => array( "Content-Type: application/json; charset=utf-8","Accept:application/json, text/javascript, */*; q=0.01", ("Authorization: OAuth oauth_token=".$access_token)));
		curl_setopt_array($curl, $myOptions);
		$curl_response = curl_exec($curl);
		curl_close($curl);
		update_option( $webinar_option_key, $curl_response, '', 'yes' );
		$response=$curl_response;
	}

	$request=json_decode($response);
	return $request;

}


function gotowp_personal_update_webinars(){

	$organizer_key= get_option('gotowp_personal_organizer_key');
	$access_token = get_option('gotowp_personal_access_token');

	//https://api.citrixonline.com/G2W/rest/organizers/{organizerKey}/webinars/{webinarKey}
	$url='https://api.citrixonline.com/G2W/rest/organizers/'.$organizer_key.'/upcomingWebinars';
	$curl = curl_init($url);

	$myOptions = array(
			CURLOPT_POST => false,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_HTTPHEADER => array( "Content-Type: application/json; charset=utf-8","Accept:application/json, text/javascript, */*; q=0.01", ("Authorization: OAuth oauth_token=".$access_token)));
	curl_setopt_array($curl, $myOptions);
	$curl_response = curl_exec($curl);

	if($curl_response){
		return $curl_response;
	}else{
		return false;
	}

}


function gotowp_personal_get_webinars(){
	$webinars_option= get_option('gotowp_personal_webinars_option');
	if(empty($webinars_option) || $webinars_option ==''){
		$webinars_option=gotowp_personal_update_webinars();
		update_option('gotowp_personal_webinars_option',$webinars_option);
	}
	$webinars_option=gotowp_personal_update_webinars();
	return $webinars_option;
}




add_shortcode("register_free_webinar",'gotowp_personal_registration_forms');

function gotowp_personal_registration_forms($atts)
{
	global $webinarErrors;
	extract(shortcode_atts(array( 'webid'=>'','pageid'=>''), $atts));
	$output='';
	$registration_fields=webinar_get_registration_fields($webid);
	$webinar=gotowp_personal_get_webinar($webid);

	$startTime=strtotime($webinar->times[0]->startTime);
	$endTime=strtotime($webinar->times[0]->endTime);
	$subject=$webinar->subject;
	
	$date_title="<b>Date and Time</b> <br/>".date('D, M j, Y m:s A',$startTime);	

	$sec_diff=$endTime-$startTime;
	
	if($sec_diff > 60){
	  $date_title.=' - '.date('m:s A',$endTime);
	}
	$date_title.=date(' T',$endTime);
	

	$output.='<form name="gotowp_personal_webinar_registration" id="gotowp_personal_webinar_registration" action="" method="post" >

	<table class="tableborder">';

	$output.=$webinarErrors->get_error_message('broke');
	
	$output.='<thead><tr class="gotowp-subject"><th colspan="2" class="tableheader subject">'.$subject.'</th></tr></thead>';
	$output.='<tbody><tr class="gotowp-date"><td colspan="2" class="date">'.$date_title.'</td></tr>';
	
	if(isset($registration_fields->fields) && count($registration_fields->fields) > 0){
		foreach($registration_fields->fields as $row): $class='';
		if($row->required){ $class='required';}
		if($row->field=='email'){$class=$class.' email';}
		
		$output.='<tr class="gotowp-'.$row->field.'"><td >'.ucwords(preg_replace('/(?=([A-Z]))/',' '.${1},$row->field)).'</td><td>';
		
		if(isset($row->answers)){
			$output.='
		        <select name="'.$row->field.'" id="'.$row->field.'" class="gotowp-select '.$class.'">
			    <option selected="selected" value="">--Select--</option>';
				
			foreach($row->answers as $opt):
			$output.=' <option value="'.$opt.'">'.$opt.'</option>';
			endforeach;
				
			$output.='</select>';
		}else{
			$output.='<input class="gotowp-input-text '.$class.'" type="text" size=20  name="'.$row->field.'" id="'.$row->field.'" />';
		}
		
		$output.='</td></tr>';
		
		endforeach;

    }else{
         $output.='<tr class="gotowp-firstName"><td >First Name</td><td>';
         $output.='<input class="gotowp-input-text required" type="text" size=20  name="firstName" id="firstName" />';
         $output.='<tr class="gotowp-lastName"><td >Last Name</td><td>';
         $output.='<input class="gotowp-input-text required " type="text" size=20  name="lastName" id="lastName" />';         
         $output.='<tr class="gotowp-email"><td >Email</td><td>';
         $output.='<input class="gotowp-input-text required email" type="text" size=20  name="email" id="email" />'; 
      }	

	$output.='<tr>
		    <input type="hidden" name="returnpageid"      value="'.$pageid.'" />
			<input type="hidden" name="webinarid"   value="'.$webid.'" /></td>
	        <td><input type="hidden" name="action" value="gotowp_personal_register_webinars" /></td>
			<td><input id="register_now_submit" style="" type="submit" name="submit"  value="Register Now"/></td>
	    </tr>
	  </tbody>
	</table>
	</form>';

	$output.='
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$("#gotowp_personal_webinar_registration").validate();
			});
		</script>';

	return $output;
}

