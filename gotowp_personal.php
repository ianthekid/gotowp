<?php
	/*
		Plugin Name: GoToWP Personal
		Plugin URI: http://www.gotowp.com/
		Description: Allow your users to easily register for your GoToWebinar webinars by simply placing a shortcode in any Wordpress post or page.
		Version: 1.1.3
		Author: GoToWP.com
		Author URI:  http://www.gotowp.com/
		Support: http://www.gotowp.com/support
	*/	

define('GOTOWP_PERSONAL_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('GOTOWP_PERSONAL_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
define('GOTOWP_PERSONAL_PLUGIN_VERSION', '1.1.3');
define('GOTOWP_PERSONAL_PLUGIN_SLUG', 'gotowp-personal');

$webinarErrors= new WP_Error();
$trainingErrors= new WP_Error();
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
    
    if ( get_option( 'gotowp_training_personal_organizer_key') === false ) {
    	add_option( 'gotowp_training_personal_organizer_key', '', '', 'yes' );
    }
    
    if ( get_option( 'gotowp_training_personal_access_token') === false ) {
    	add_option( 'gotowp_training_personal_access_token', '', '', 'yes' );
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

class GotowpDateTime extends DateTime
{
	public function setTimestamp( $timestamp )
	{
		$date = getdate( ( int ) $timestamp );
		$this->setDate( $date['year'] , $date['mon'] , $date['mday'] );
		$this->setTime( $date['hours'] , $date['minutes'] , $date['seconds'] );
	}

	public function getTimestamp()
	{
		return $this->format( 'U' );
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
			
			if ( !wp_script_is( 'jquery' ) ) {
				wp_enqueue_script('jquery',GOTOWP_PERSONAL_PLUGIN_URL.'assets/js/jquery-1.8.3.min.js');
			}				
			
			wp_enqueue_style( GOTOWP_PERSONAL_PLUGIN_SLUG . '-admin-css', GOTOWP_PERSONAL_PLUGIN_URL.'assets/css/admin.css', array(), GOTOWP_PERSONAL_PLUGIN_VERSION );
			
			wp_register_script(GOTOWP_PERSONAL_PLUGIN_SLUG .'tab-js',GOTOWP_PERSONAL_PLUGIN_URL.'assets/js/jquery.easytabs.min.js',array('jquery'),'',true);
			wp_register_script(GOTOWP_PERSONAL_PLUGIN_SLUG .'admin-js',GOTOWP_PERSONAL_PLUGIN_URL.'assets/js/admin.js',array('jquery'),'',true);
				
			wp_register_style(GOTOWP_PERSONAL_PLUGIN_SLUG.'tab-css',GOTOWP_PERSONAL_PLUGIN_URL.'assets/css/mstabs.css', array(), GOTOWP_PERSONAL_PLUGIN_VERSION);			
			wp_enqueue_style( GOTOWP_PERSONAL_PLUGIN_SLUG.'tab-css' );
			
			wp_enqueue_script( GOTOWP_PERSONAL_PLUGIN_SLUG .'tab-js' );
			wp_enqueue_script( GOTOWP_PERSONAL_PLUGIN_SLUG .'admin-js');
			
			
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
		if(isset($_POST['action']) && trim($_POST['action'])=='gotowp_personal_savefreewebinar')
		{
			$gotowp_personal_organizer_key=trim(esc_attr($_REQUEST['gotowp_personal_organizer_key']));
			$gotowp_personal_access_token=esc_attr(trim($_REQUEST['gotowp_personal_access_token']));

			update_option( 'gotowp_personal_organizer_key',$gotowp_personal_organizer_key);
			update_option( 'gotowp_personal_access_token', $gotowp_personal_access_token);
		}
		
		
		if(isset($_POST['action']) && trim($_POST['action'])=='save_training_settings')
		{
		
			delete_option( 'gotowp_training_personal_organizer_key');
			delete_option( 'gotowp_training_personal_access_token');
		
			update_option( 'gotowp_training_personal_organizer_key',trim(esc_attr($_REQUEST['training_organizer_key'])));
			update_option( 'gotowp_training_personal_access_token', trim(esc_attr($_REQUEST['training_access_token']) ));
		}		
		
		if(isset($_POST['action']) && trim($_POST['action'])=='gotowp_personal_update_webinar_forms')
		{
            $response=gotowp_personal_get_webinars();
            $webinars_arr=gotowp_personal_json_decode($response);        
     
            foreach($webinars_arr as $webinar){     
/*             	if(isset($webinar->registrationUrl)){
	            	$registrationUrl=$webinar->registrationUrl;
	            	$web_key=str_replace('https://attendee.gotowebinar.com/register/','',$registrationUrl);
               }else{
               	    $web_key=$webinar->webinarKey;
               } */
                $web_key=$webinar->webinarKey;
            	$web_key=trim($web_key); 	
            	webinar_update_registration_fields($web_key);            	
            }
            _e('Webinar forms updated successfully');
			
		}	

		if( isset($_POST['action']) && trim($_POST['action'])=='gotowp_personal_update_trainings'){
			$response=gotowp_training_personal_update_trainings();
			$trainings=gotowp_personal_json_decode($response);
		}		
	
		?>

	<div class="wrap">
	
	
	<div id="tab-container">

			<ul class="tabholder tabsPanel">
				<li><a href="#tab0_content">GoToTraining Settings</a></li>
			    <li><a href="#tab1_content">GoToWebinar Settings</a></li> 
		    </ul>
		    
		    
		<div id="tab0_content" class="tabsCntents">
    		<form name="gotowp_training_settings" id="gotowp_training_settings" action="" method="post"> 

				<table class="tableborder">
			     <tr>
			        <td class="gotowp-description tableclass" colspan="2">The <b style="color:#090;">Organizer Key</b>  and  <b style="color:#090;">Access Token</b>  can be obtained from online application after authenticating with O-Auth connector for webinars (G2T OAuth Flow) by clicking the link below. Keys generated through app.gotowp.com are stable for 1 year<br>
				    <a href="http://app.gotowp.com/" target="_blank"><b>http://app.gotowp.com/</b></a></td> 
				</tr>  				
				    <tr><th colspan="2" class="tableheader"><?php _e('GotoTraining Access Details'); ?></th></tr>
				    <tr>
				        <td class="tableclass"><?php _e('Organizer Key'); ?></td> <td><input type="text" size=40  value="<?php echo trim(get_option('gotowp_training_personal_organizer_key')); ?>" name="training_organizer_key"  id="training_organizer_key"/></td>
				    </tr>
				    <tr>
				        <td class="tableclass"><?php _e('Access Token'); ?></td>  <td><input type="text" size=40  value="<?php echo trim(get_option('gotowp_training_personal_access_token')); ?>" name="training_access_token" id="training_access_token"/></td>
				    </tr>
				    
				    <tr>
				        <td class="tableclass"><input  type="hidden" name="action" value="save_training_settings" /></td>
				        <td><input style="" class="submit_btn" type="submit" name="submit"  value="<?php _e('Save Details') ?>"/></td>
				    </tr>				    
				    
				    
				</table>

			</form>
					
		   <?php
		  	  $organizer_key= trim(get_option('gotowp_training_personal_organizer_key'));
			  $access_token = trim(get_option('gotowp_training_personal_access_token'));
		    ?>
			
			<?php if($organizer_key !='' && $access_token !=''): ?>						
						
						<form name="update_trainings_form" id="update_trainings_form" action="<?php echo admin_url('options-general.php').'?page='.GOTOWP_PERSONAL_PLUGIN_SLUG.'-settings';?>" method="post">		
							<table class="tableborder">
							    <thead>
							        <tr><th colspan="2" class="tableheader"><?php _e('Update Trainings'); ?></th></tr>
							    </thead>
							    <tbody>
				
							    <tr>
							        <td>
							           <input  type="hidden" name="action" value="gotowp_personal_update_trainings" />
							           <input id="update_trainings" class="submit_btn" style="" type="submit" name="submit"  value="<?php _e('Update Trainings') ?>"/>
							        </td>
							    </tr>  
							    </tbody>
							</table>	
						</form>						
			
			<?php endif; ?>			
			
		<table id="gotowp_training_personal_sample_table" class="tableborder">
		<thead>
			<tr class="heading_row"><th colspan="2" class="tableheader heading"><?php _e('Sample Usage Shortcode On Registration Page'); ?><br/></th></tr>
			</thead>
			<tbody>
		    <tr class="sample_row" style="">
		      <td  style="" class="tableclass sample_title"></td>  
		      <td class="sample_field"><input style="width:500px;padding:5px 20px;" type="text" size=40  value="[register_free_training id=xxxxxxxxxxxx page_id=xxx]" name="shortcode" class="shortcode" /></td>    
		    </tr>
		    </tbody>
		</table>			

    </div>
    	    
		    
	
	<div id="tab1_content" class="tabsCntents">

	   <div class="row"> 

	    <div class="col col1">
		<form name="gotowp_personal_adminsettings" id="gotowp_personal_adminsettings" action="" method="post">
			<table class="tableborder">
			    <thead>
			       <tr><th colspan="2" class="tableheader"><?php _e('Webinar Details'); ?></th></tr>
			    </thead>
			    <tbody>
			     <tr>
			        <td class="gotowp-description tableclass" colspan="2">The <b style="color:#090;">Organizer Key</b>  and  <b style="color:#090;">Access Token</b>  can be obtained from online application after authenticating with O-Auth connector for webinars (G2W OAuth Flow) by clicking the link below. Keys generated through app.gotowp.com are stable for 1 year<br>
				    <a href="http://app.gotowp.com/" target="_blank"><b>http://app.gotowp.com/</b></a></td> 
				</tr>    
			    <tr>
			        <td class="gotowp-organizer-key tableclass"><?php _e('Organizer Key'); ?></td> 
			        <td><input type="text" size=40  value="<?php echo trim(get_option('gotowp_personal_organizer_key')); ?>" name="gotowp_personal_organizer_key"  id="gotowp_personal_organizer_key"/></td>
			    </tr>
			    <tr>
			        <td class="gotowp-access-token tableclass"><?php _e('Access Token'); ?></td>  
			        <td><input type="text" size=40  value="<?php echo trim(get_option('gotowp_personal_access_token')); ?>" name="gotowp_personal_access_token" id="gotowp_personal_access_token"/></td>
			    </tr>
			    <tr>
			        <td class="gotowp-action-hidden tableclass"><input  type="hidden" name="action" value="gotowp_personal_savefreewebinar" /></td>
			        <td><input class="gotowp-submit-button" id="savefreewebinar_submit" style="" type="submit" name="submit"  value="<?php _e('Save Details') ?>"/></td>
			    </tr>  
			    </tbody>
			</table>
		</form>
        </div>

        <div class="col col2">
			<a href="http://CitrixOnline.evyy.net/c/83431/19721/810">
			 <img src="http://adn.impactradius.com/display-ad/810-19721" border="0" alt="GoToWebinar Free Trial" width="300" height="250"/>
			</a>
			<img height="1" width="1" src="http://CitrixOnline.evyy.net/i/83431/19721/810" border="0" />
        </div>


		</div>
		
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
			           <input  type="hidden" name="action" value="gotowp_personal_update_webinar_forms" />
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
		
	</div>
		
		
	
	</div>
	<?php		
	
	} 	
	
	/*ADDING ADMIN MENU FOR SETTINGS*/
	add_action('admin_menu','gotowp_personal_admin_menu');
	function gotowp_personal_admin_menu() {
		add_menu_page('Webinar Plugin Settings', 'GoToWP Personal','manage_options', GOTOWP_PERSONAL_PLUGIN_SLUG.'-settings','gotowp_personal_plugin_add_webinar_details',GOTOWP_PERSONAL_PLUGIN_URL.'assets/img/webinar.png');
	}	


}


else{	
	add_action( 'wp_enqueue_scripts', 'gotowp_personal_enqueue_styles'  );
	add_action( 'wp_enqueue_scripts', 'gotowp_personal_enqueue_scripts' );
	
	function gotowp_personal_enqueue_scripts(){
		global $post;
		if( gotowp_has_shortcode( 'register_free_webinar') || gotowp_has_shortcode( 'register_free_training')) {
			if ( !wp_script_is( 'jquery' ) ) {				
				wp_enqueue_script('jquery',GOTOWP_PERSONAL_PLUGIN_URL.'assets/js/jquery-1.8.3.min.js');
			}			
			wp_enqueue_script( GOTOWP_PERSONAL_PLUGIN_SLUG . '-validate-js', GOTOWP_PERSONAL_PLUGIN_URL.'assets/js/jquery.validate.min.js', array( 'jquery' ), GOTOWP_PERSONAL_PLUGIN_VERSION );
		}
	}
	
	function gotowp_personal_enqueue_styles(){
		global $post;
		if( gotowp_has_shortcode( 'register_free_webinar') || gotowp_has_shortcode( 'register_free_training')) {
			wp_enqueue_style( GOTOWP_PERSONAL_PLUGIN_SLUG . '-public-style', GOTOWP_PERSONAL_PLUGIN_URL.'assets/css/public.css', array(), GOTOWP_PERSONAL_PLUGIN_VERSION );
		}
	}		
	
	add_action('init','gotowp_personal_save_before_registration');
	
	function gotowp_personal_save_before_registration()
	{
		global $webinarErrors;			

		
		if( isset($_REQUEST['action']) && trim($_REQUEST['action'])=='gotowp_personal_register_webinars' )
		{
			$webinarid=trim(esc_attr($_REQUEST['webinarid']));

		  if(!empty($webinarid) && $webinarid !='' && gotowp_personal_is_webinar($webinarid)){

			$organizer_key= trim(get_option('gotowp_personal_organizer_key'));
			$access_token = trim(get_option('gotowp_personal_access_token'));
			$gtw_url = "https://api.citrixonline.com/G2W/rest/organizers/".$organizer_key."/webinars/".$webinarid."/registrants";
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
			
			$registrants=gotowp_personal_json_decode($response);
						
			$emails=array();
			
			if(count($registrants) > 0){
				foreach($registrants as $registrant)
				{
					$emails[]=$registrant->email;
				}
		   }
						
			if( is_array($emails) && in_array($_REQUEST['email'],$emails) )
			{			
				$webinarErrors->add('broke','This Email is already registered with this webinar');
			}
			else
			{
					global $wpdb;
	                $webinar_table = $wpdb->prefix . "gotowp_personal_webinars";
	                
	                $webinar_id=trim(esc_attr($_POST['webinarid']));                			
				    $registration_fields=webinar_get_registration_fields($webinar_id);
				    
				    $firstName=trim(esc_attr($_POST['firstName']));
				    $lastName=trim(esc_attr($_POST['lastName']));
				    $email=trim(esc_attr($_POST['email']));
				    
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
					$return_url   = get_permalink(trim(esc_attr($_POST['returnpageid'])));
					$url= 'https://api.citrixonline.com/G2W/rest/organizers/'.$organizer_key.'/webinars/'.$webinar_id.'/registrants';
		
					$curl = curl_init($url);
					
					$curl_post_data=array();
					
					foreach($registration_fields->fields as $row):				
					   $curl_post_data[$row->field]=$_POST[$row->field];				
					endforeach;	

					$responses=array();

					if(isset($registration_fields->questions) && count($registration_fields->questions) > 0){
						foreach($registration_fields->questions as $row):
                           $question=array();                                   
                           $question['questionKey']=$row->questionKey;
							if(isset($row->answers)){
		                        $question['answerKey']=$_POST[$row->questionKey];
							}else{
								$question['responseText']=$_POST[$row->questionKey];
							}                     
						
						   $responses[]=$question;
						endforeach;

                       $curl_post_data['responses']=$responses;
				    }						 


		          $headers=array( 
						"HTTP/1.1",
						"Accept: application/json",
						"Accept: application/vnd.citrix.g2wapi-v1.1+json",
						"Content-Type: application/json",
						"Authorization: OAuth oauth_token=$access_token",
					   );	
	
					$myOptions = array( 
						CURLOPT_POST => true, 
						CURLOPT_SSL_VERIFYHOST => 0, 
						CURLOPT_SSL_VERIFYPEER => 0, 
						CURLOPT_POSTFIELDS => json_encode($curl_post_data), 
						CURLOPT_URL => $url, 
						CURLOPT_RETURNTRANSFER => 1, 
						CURLOPT_HTTPHEADER => $headers
	                );

					curl_setopt_array($curl, $myOptions);
					$curl_response = curl_exec($curl);
					wp_redirect($return_url);exit;
					//echo '<META HTTP-EQUIV="Refresh" Content="0; URL='.$return_url.'">'; 
				}
			}
		  }
		}	
	}

}



function gotowp_training_personal_curl_post_call($url,$post_data){

	$organizer_key= trim(get_option('gotowp_training_personal_organizer_key'));
	$access_token = trim(get_option('gotowp_training_personal_access_token'));

	if($organizer_key !='' && $access_token !=''){

		$curl = curl_init($url);

		$headers=array(
				"HTTP/1.1",
				"Accept: application/json",
				"Content-Type: application/json",
				"Authorization: OAuth oauth_token=$access_token",
		);

		$myOptions = array(
				CURLOPT_POST => true,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_POSTFIELDS => json_encode($post_data),
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => $headers
		);

		curl_setopt_array($curl, $myOptions);
		$curl_response = curl_exec($curl);

		if ($curl_response === FALSE) {
			$curl_response=curl_getinfo($curl);
		}

		return $curl_response;

	}else{
		return false;
	}

}



add_shortcode("register_free_training",'gotowp_training_personal_registration_form_func');

function gotowp_training_personal_registration_form_func($atts)
{
	global $trainingErrors;
	extract(shortcode_atts(array( 'id'=>'','page_id'=>''), $atts));

	$training_id=trim($id);
	$page_id=trim($page_id);
	$date_title='';
	$subject='';		


			if(isset($_POST['action']) && trim($_POST['action'])=='registertraining'){
		
				$givenName=trim(esc_attr($_POST['givenName']));
				$surname=trim(esc_attr($_POST['surname']));
				$email=trim(esc_attr($_POST['email']));
				
				if($givenName !='' && $surname !='' && $email !='' && filter_var($email, FILTER_VALIDATE_EMAIL)){
					$post_id=trim(esc_attr($_POST['post_id']));		
				    $item_name=trim(esc_attr($_POST['item_name']));
					$training_key=trim(esc_attr($_POST['training_id']));			
					$return_page_id=trim(esc_attr($_POST['return_page_id']));
						
					$post_data=array(
							'email'=>$email,
							'givenName'=>$givenName,
							'surname' =>$surname
					);
						
					
					$curl_response=gotowp_training_personal_register_training($training_key,$post_data);	
					$response=gotowp_personal_json_decode($curl_response);
					
					if(isset($response->joinUrl)){
					
					    $return_page_url=get_permalink($return_page_id);			
					   if(!filter_var($return_page_url, FILTER_VALIDATE_URL)){
					       $return_page_url=get_option('siteurl');			
					   }
					
						 echo '<script type="text/javascript">
								<!--
								   window.location="'.$return_page_url.'";
								//-->
								</script>';	
					}
								
								
								
				}else{
				  $trainingErrors->add('broke','please enter valid inputs');
				}
		
			}

	
			$response=gotowp_training_personal_get_training($training_id);
			$training=gotowp_personal_json_decode($response);

			if(isset($training->trainingKey)){
	
				$subject=$training->name;
				$timezone_string=$training->timeZone;
				$startTime = new GotowpDateTime($training->times[0]->startDate);
				$startTime->setTimezone(new DateTimeZone($timezone_string));
				$endTime = new GotowpDateTime($training->times[0]->endDate);
				$endTime->setTimezone(new DateTimeZone($timezone_string));
				$date_title="<b>Date and Time</b> <br/>".$startTime->format('D, M j, Y h:i A');
				$sec_diff=$endTime->getTimestamp()-$startTime->getTimestamp();
				if($sec_diff > 60){
					$date_title.=' - '.$endTime->format('h:i A');
				}
				$date_title.=$endTime->format(' T');
			}else{
				return false;
			}

		$subject_row='';
		$date_row='';

			$subject_row='<tr class="gotowp-subject"><th colspan="2" class="tableheader subject">'.$subject.'</th></tr>';
			$date_row='<tr class="gotowp-date"><td colspan="2" class="date">'.$date_title.'</td></tr>';


		$output='';
		$output.='<form name="trainingregistration" id="trainingregistration" action="" method="post" ><table><thead>';
		$output.=$trainingErrors->get_error_message('broke').' <tr><th colspan="2">Register for a training</th></tr>';
		$output.=$subject_row.'</thead>';
		$output.='<tbody>'.$date_row;
		
		$output.='<tr class="gotowp-firstName"><td class="label">First Name</td><td>';
		$output.='<input class="gotowp-input-text required" type="text" size=20  name="givenName" id="givenName" />';
		$output.='<tr class="gotowp-lastName"><td class="label">Last Name</td><td>';
		$output.='<input class="gotowp-input-text required " type="text" size=20  name="surname" id="surname" />';
		$output.='<tr class="gotowp-email"><td class="label">Email</td><td>';
		$output.='<input class="gotowp-input-text required email" type="text" size=20  name="email" id="email" />';		
		
		global $post;

		$output.='<td><input type="hidden" name="post_id"   value="'.$post->ID.'" /></td>';
		$output.='<td><input type="hidden" name="item_name"  id="item_name" value="'.$subject.'" /></td>';
		$output.='<td><input type="hidden" name="training_id" id="training_id"  value="'.$training_id.'" /></td>';
        $output.='<td><input type="hidden" name="return_page_id" id="return_page_id" value="'.$page_id.'" /></td>';


		$output.='</tr>';
			
		$output.='<tr>
	        <td><input type="hidden" name="action" value="registertraining" /></td>
					<td><input style="background:#6FAA55; color:#ffffff; font-weight:bold;" type="submit" name="submit"  value="Register Now"/></td></tr></table>
			</form>';


		$output.='<script type="text/javascript">';

		$output.='jQuery(document).ready(function($){
							    $("#trainingregistration").validate({
							    	rules:{
							    		givenName:{required:true},
							    		surname:{required:true},
							    		email:{required:true,email:true}
							    	}
							    });
						});
				</script>';
		return $output;

	


	?>
<?php
}

function custom_print_r($data){
	echo "<pre>";
	print_r($data);
	echo "</pre>";

}

function gotowp_training_personal_curl_get_call($url){

	$organizer_key= trim(get_option('gotowp_training_personal_organizer_key'));
	$access_token = trim(get_option('gotowp_training_personal_access_token'));

	if($organizer_key !='' && $access_token !=''){

		$curl = curl_init($url);

		$headers=array(
				"HTTP/1.1",
				"Accept: application/json",
				"Content-Type: application/json",
				"Authorization: OAuth oauth_token={$access_token}",
		);

		$myOptions = array(
				CURLOPT_POST => false,
				CURLOPT_SSL_VERIFYHOST => 0,
				CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => $headers
		);

		curl_setopt_array($curl, $myOptions);
		$curl_response = curl_exec($curl);

		if ($curl_response === FALSE) {
			$curl_response=curl_getinfo($curl);
		}

		return $curl_response;


	}else{
		return false;
	}
}



function gotowp_training_personal_register_training($training_key,$post_data){

	$organizer_key= trim(get_option('gotowp_training_personal_organizer_key'));
	$url="https://api.citrixonline.com/G2T/rest/organizers/{$organizer_key}/trainings/{$training_key}/registrants";
	$curl_response=gotowp_training_personal_curl_post_call($url,$post_data);

	if($curl_response){
		return $curl_response;
	}else{
		return false;
	}

}


function gotowp_training_personal_get_training($training_key){
	$training_key=trim($training_key);

	if($training_key !='' && !empty($training_key)){

		$training_option_key='gotowp_training_personal_training_id_'.$training_key;
		$training_option_key=trim($training_option_key);

		if ( get_option( $training_option_key) !== false ) {
			$response=get_option($training_option_key);
			$training_obj=gotowp_personal_json_decode($response);
			if(!isset($training_obj->trainingKey)){
				$response=gotowp_training_personal_update_training($training_key);
			}else{
				return $response;
			}
		}
		else{
			$response=gotowp_training_personal_update_training($training_key);
		}
			
		return $response;

	}else{
		return false;
	}
}


function gotowp_training_personal_update_training($training_key){
	$training_key=trim($training_key);

	if($training_key !='' && !empty($training_key)){

		$training_option_key='gotowp_training_personal_training_id_'.$training_key;
		$training_option_key=trim($training_option_key);
		delete_option($training_option_key);

		$organizer_key= trim(get_option('gotowp_training_personal_organizer_key'));
		$url="https://api.citrixonline.com/G2T/rest/organizers/{$organizer_key}/trainings/{$training_key}";
		$curl_response=gotowp_training_personal_curl_get_call($url);
			
		$training_obj=gotowp_personal_json_decode($curl_response);
			
		if(isset($training_obj->trainingKey)){
			update_option( $training_option_key, $curl_response, '', 'yes' );
			return $curl_response;
		}else{
			return false;
		}

	}else{
		return false;
	}

}


function gotowp_training_personal_update_trainings(){

	$organizer_key= trim(get_option('gotowp_training_personal_organizer_key'));
	$url="https://api.citrixonline.com/G2T/rest/organizers/{$organizer_key}/trainings";
	$curl_response=gotowp_training_personal_curl_get_call($url);


	if($curl_response){
		delete_option('gotowp_training_personal_trainings_option');
		update_option('gotowp_training_personal_trainings_option',$curl_response);
		return $curl_response;
	}else{
		return false;
	}

}


function gotowp_training_personal_get_trainings(){
	if(get_option('gotowp_training_personal_trainings_option') !== false){
		$trainings_option= get_option('gotowp_training_personal_trainings_option');
	}else{
		$trainings_option=gotowp_training_personal_update_trainings();
		if($trainings_option){
			update_option('gotowp_training_personal_trainings_option',$trainings_option);
		}
	}
	return $trainings_option;
}



function gotowp_personal_is_webinar($webinar_key){
		
	if($webinar_key !='' && !empty($webinar_key)){
		$webinar_response=gotowp_personal_get_webinar($webinar_key);

		if($webinar_response && $webinar_response->webinarKey){
			return true;
		}else{
			return false;
		}
		
	}else{
		return false;
	}
}

function gotowp_personal_json_decode($json, $assoc = false, $depth = 512, $options = 0) {
	// search and remove comments like /* */ and //
	$json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);

	if(version_compare(phpversion(), '5.4.0', '>=')) {
		$json = json_decode($json, $assoc, $depth, JSON_BIGINT_AS_STRING);
	}
	elseif(version_compare(phpversion(), '5.3.0', '>=')) {
		$json=preg_replace('/("\w+"):(\d+)/', '\\1:"\\2"', $json);
		$json = json_decode($json, $assoc, $depth);
	}
	else {
		$json=preg_replace('/("\w+"):(\d+)/', '\\1:"\\2"', $json);
		$json = json_decode($json, $assoc);
	}

	return $json;
}


function gotowp_has_shortcode( $shortcode) {
	global $post;
	$found = false;
	if(function_exists('has_shortcode'))
	{
		if( has_shortcode( $post->post_content, $shortcode) )
		{
			$found = TRUE;
		}
	}
	else
	{
		if( gotowp_custom_has_shortcode($shortcode) )
		{
			$found = TRUE;
		}
	}
	return $found;
}



function gotowp_custom_has_shortcode(){
	global $post;
	$found = false;
	if ( ! $shortcode ) {
		return $found;
	}
	if ( stripos( get_the_content(), '[' . $shortcode) !== FALSE ) {
		$found = TRUE;
	}
	return $found;
}


function webinar_get_registration_fields($web_key)
{
	$web_key=trim($web_key);
		if(!empty($web_key) && $web_key !=''){
			webinar_update_registration_fields($web_key);
			$webinar_option_key='gotowp_personal_webinar_form_id_'.$web_key;
			if ( get_option( $webinar_option_key) !== false ) {
				$response=get_option($webinar_option_key);
			}
			else{
				$response=webinar_update_registration_fields($web_key);
			}		
			$request=gotowp_personal_json_decode($response);
			return $request;
        }
}


function webinar_update_registration_fields($web_key)
{
	$web_key=trim($web_key);	
	if(!empty($web_key) && $web_key !=''){
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
		$response_de=gotowp_personal_json_decode($response);
		if(isset($response_de->fields)){
		 update_option( $webinar_option_key, $response, '', 'yes' );
	   }
	   return $response;
  }
}



function gotowp_personal_get_webinar($webinarKey){
    $webinarKey=trim($webinarKey);

    if(!empty($webinarKey) && $webinarKey !=''){

		$webinar_option_key='gotowp_personal_webinar_id_'.$webinarKey;

		if ( get_option( $webinar_option_key) !== false ) {
			$response=get_option($webinar_option_key);
		}
		else{

			$organizer_key= trim(get_option('gotowp_personal_organizer_key'));
			$access_token = trim(get_option('gotowp_personal_access_token'));
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
			if(isset($curl_response->webinarKey)){
			   update_option( $webinar_option_key, $curl_response, '', 'yes' );
		    }
			$response=$curl_response;
		}

		$request=gotowp_personal_json_decode($response);
		return $request;
   }else{
   	  return false;
   }

}


function gotowp_personal_update_webinars(){

	$organizer_key= trim(get_option('gotowp_personal_organizer_key'));
	$access_token = trim(get_option('gotowp_personal_access_token'));

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
	$webinars_option= trim(get_option('gotowp_personal_webinars_option'));
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

    $webid=trim($webid);
    $pageid=trim($pageid);

	$registration_fields=webinar_get_registration_fields($webid);

	//print_r($registration_fields);die;

	if(isset($registration_fields->fields) && is_array($registration_fields->fields)){

			$webinar=gotowp_personal_get_webinar($webid);

			if(isset($webinar->webinarKey)){

			$subject=$webinar->subject;

			$timezone_string=$webinar->timeZone;
			
		    $startTime = new GotowpDateTime($webinar->times[0]->startTime);
		    $startTime->setTimezone(new DateTimeZone($timezone_string));

		    $endTime = new GotowpDateTime($webinar->times[0]->endTime);
		    $endTime->setTimezone(new DateTimeZone($timezone_string));			
			
			$date_title="<b>Date and Time</b> <br/>".$startTime->format('D, M j, Y h:i A');				

			$sec_diff=$endTime->getTimestamp()-$startTime->getTimestamp();	
			
			if($sec_diff > 60){
			  $date_title.=' - '.$endTime->format('h:i A');
			}
			
			$date_title.=$endTime->format(' T');	
			
			$output.='<form name="gotowp_personal_webinar_registration" id="gotowp_personal_webinar_registration" action="" method="post" >

			<table class="tableborder">';

			$output.=$webinarErrors->get_error_message('broke');
			
			$output.='<thead><tr class="gotowp-subject"><th colspan="2" class="tableheader subject">'.$subject.'</th></tr></thead>';
			$output.='<tbody><tr class="gotowp-date"><td colspan="2" class="date">'.$date_title.'</td></tr>';
			
			if(isset($registration_fields->fields) && count($registration_fields->fields) > 0){
				foreach($registration_fields->fields as $row): $class='';
				if($row->required){ $class='required';}
				if($row->field=='email'){$class=$class.' email';}
				
                $field_name = ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $row->field));

				$output.='<tr class="gotowp-'.$row->field.'"><td >'.$field_name.'</td><td>';
				
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
		      
		      
		      if(isset($registration_fields->questions) && count($registration_fields->questions) > 0){
		      	foreach($registration_fields->questions as $row): $class='';
		      
		      	$label = ucwords(preg_replace('/([a-z])([A-Z])/', '$1 $2', $row->question));		      
	      
		      	if($row->required){ $class='required';}
		      		
		      	$output.='<tr class="gotowp-'.$row->questionKey.'"><td><label>'.$row->question.':</label>';
		      
		      	if(isset($row->answers)){
		      		$output.='
				        <select name="'.$row->questionKey.'" id="'.$row->questionKey.'" class="gotowp-select '.$class.'">
					    <option selected="selected" value="">--Select--</option>';
		      
		      		foreach($row->answers as $opt):
		      		$output.=' <option value="'.$opt->answerKey.'">'.$opt->answer.'</option>';
		      		endforeach;
		      
		      		$output.='</select>';
		      	}else{
		      		$output.='<input class="gotowp-input-text '.$class.'" type="text" size=20  name="'.$row->questionKey.'" id="'.$row->questionKey.'" />';
		      	}
		      
		      	$output.='</td></tr>';
		      
		      	endforeach;
		      
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
		}

	}

   return $output;
}