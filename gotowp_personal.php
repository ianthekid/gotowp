<?php
/*
Plugin Name: GoToWP Personal
Plugin URI: http://www.gotowp.com/
Description: Allow your users to easily register for your GoToWebinar webinars by simply placing a shortcode in any Wordpress post or page.
Version: 1.0.3
Author: GoToWP.com
Author URI:  http://www.gotowp.com/
Support: http://www.gotowp.com/support
*/

register_activation_hook(__FILE__,'freewebinar_install');
function freewebinar_install()
{
	 add_option( 'freewebinar_organizer_key', '', '', 'yes' );
	 add_option( 'freewebinar_access_token', '', '', 'yes' );   
	 mysql_query('CREATE TABLE IF NOT EXISTS `wp_webinar` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `firstname` varchar(30) DEFAULT NULL,
				  `lastname` varchar(30) DEFAULT NULL,
				  `email` varchar(50) DEFAULT NULL,
				  `registration_id` varchar(50) NOT NULL,
				  `item_name` varchar(25) NOT NULL,
				  `item_number` varchar(25) NOT NULL,
				  `amount` varchar(50) NOT NULL,
				  `returnpageid` int(11) NOT NULL,
				  `payer_id` varchar(255) NOT NULL,
				  `payment_date` varchar(255) NOT NULL,
				  `payment_status` varchar(255) NOT NULL,
				  `first_name` varchar(255) NOT NULL,
				  `last_name` varchar(255) NOT NULL,
				  `mc_fee` varchar(255) NOT NULL,
				  `address_country_code` varchar(255) NOT NULL,
				  `address_name` varchar(255) NOT NULL,
				  `address_country` varchar(255) NOT NULL,
				  `address_state` varchar(255) NOT NULL,
				  `address_city` varchar(255) NOT NULL,
				  `business` varchar(255) NOT NULL,
				  `quantity` varchar(255) NOT NULL,
				  `paypalitem_name` varchar(255) NOT NULL,
				  `paypalitem_number` varchar(255) NOT NULL,
				  `mc_currency` varchar(255) NOT NULL,
				  `payment_type` varchar(255) NOT NULL,
				  `payment_fee` varchar(255) NOT NULL,
				  `payment_gross` varchar(255) NOT NULL,
				  `verify_sign` varchar(255) NOT NULL,
				  `payer_email` varchar(255) NOT NULL,
				  `receiver_email` varchar(255) NOT NULL,
				  `receiver_id` varchar(255) NOT NULL,
				  `txn_id` varchar(255) NOT NULL,
				  `txn_type` varchar(255) NOT NULL,
				  `test_ipn` varchar(255) NOT NULL,
				  `notify_version` varchar(255) NOT NULL,
				  `ipn_track_id` varchar(255) NOT NULL,
				  `cc_approve` int(11) NOT NULL,
				  `cc_declined` int(11) NOT NULL,
				  `cc_error` int(11) NOT NULL,
				  `cc_held` int(11) NOT NULL,
				  `cc_response_code` int(11) NOT NULL,
				  `cc_response_subcode` int(11) NOT NULL,
				  `cc_response_reason_code` int(11) NOT NULL,
				  `cc_transaction_id` varchar(50) NOT NULL,
				  `cc_authorization_code` varchar(50) NOT NULL,
				  `cc_transaction_type` varchar(50) NOT NULL,
				  `cc_avs_response` varchar(10) NOT NULL,
				  `cc_cavv_response` varchar(25) NOT NULL,
				  `cc_method` varchar(50) NOT NULL,
				  `cc_card_type` varchar(50) NOT NULL,
				  `cc_amount` varchar(50) NOT NULL,
				  `recordedurl` text NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=latin1');				 
}

register_deactivation_hook(__FILE__,'freewebinar_uninstall');
function freewebinar_uninstall()
{
	delete_option('freewebinar_organizer_key');
	delete_option('freewebinar_access_token');	
}

add_action('init','save_before_registration');
add_action('after_setup_theme', 'scriptas');
add_action('admin_menu','freewebinarmenu');

add_shortcode("register_free_webinar",'registration_forms'); 
$webinarErrors= new WP_Error();
function scriptas(){ 
wp_enqueue_script('jquery');
wp_enqueue_script('validationjs',plugins_url('/gotowp_personal/javascripts/jquery.validate.js'));
}

/*ADDING A SETTINGS LINK BESIDE ACTIVATE/DEACTIVATE*/
if ( ! defined( 'WEBINAR_PLUGIN_BASENAME' ) )
	define( 'WEBINAR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

add_filter( 'plugin_action_links', 'gotowebinar_plugin_action_linkas', 10, 8 );
function gotowebinar_plugin_action_linkas( $links, $file ) {
	if ( $file != WEBINAR_PLUGIN_BASENAME )
		return $links;
	$settings_link = '<a href="'.menu_page_url( 'freewebinar', false ).'">'.esc_html( __( 'Settings', 'freewebinar' )).'</a>';
	array_unshift( $links, $settings_link );
	return $links;
}


/*ADDING ADMIN MENU FOR SETTINGS*/
add_action('admin_menu', 'gtfw_create_menu');
function gtfw_create_menu() {
add_menu_page('Webinar Plugin Settings', 'Free Webinar','administrator', __FILE__,'add_freewebinardetails',plugins_url('gotowp_personal/javascripts/images/webinar.png'));
}

/*ADDING A SETTINGS LINK IN ADMN SETTINGS*/
function freewebinarmenu(){
   add_options_page('Free Webinar Registration','FreeWebinar','1','freewebinar','add_freewebinardetails');
}

/*ADD FREE WEBINAR DETAILS*/
function add_freewebinardetails()
{
?>
<style type="text/css">
.error{ color:red;}
.tableclass{width:125px;}
.tableborder{border: 1px solid #56B4EF;margin-bottom:15px;}
.tableheader{background:#007FC8;color:#ffffff;}
</style>
<div class="wrap">
<form name="adminsettings" id="adminsettings" action="" method="post">
<table class="tableborder">
    <th colspan="2" class="tableheader"><?php _e('Webinar Details'); ?></th>
     <tr><td class="tableclass" colspan="2">The <b style="color:#090;">Organizer Key</b>  and  <b style="color:#090;">Access Token</b>  can be obtained from online application after authenticating with O-Auth connector for webinars (G2W OAuth Flow) by clicking the link below</br>
	<a href="http://citrixonline-quick-oauth.herokuapp.com/" target="_blank"><b>http://citrixonline-quick-oauth.herokuapp.com/</b></a></td> </tr>
    
    <tr>
        <td class="tableclass"><?php _e('Organizer Key'); ?></td> <td><input type="text" size=40  value="<?php echo get_option('freewebinar_organizer_key'); ?>" name="organizer_key"  id="organizer_key"/></td>
    </tr>
    <tr>
        <td class="tableclass"><?php _e('Access Token'); ?></td>  <td><input type="text" size=40  value="<?php echo get_option('freewebinar_access_token'); ?>" name="access_token" id="access_token"/></td>
    </tr>
    <tr>
        <td class="tableclass"><input  type="hidden" name="action" value="savefreewebinar" /></td><td><input style="background:#629819 ; color:#ffffff; font-weight:bold;padding:5px 18px 5px 15px;" type="submit" name="submit"  value="<?php _e('Save Details') ?>"/></td>
    </tr>
  
</table>
</form>

<table class="">
	<tr style="width:500px;float:left;"><th colspan="2" class=""><?php _e('Sample Usage Shortcode'); ?></th></tr><br/>
    <tr style="width:500px;float:left;">
    <td  style="width:125px;float:left;" class="tableclass"><?php _e('On Registration Page'); ?></td>  <td><input style="width:650px;" type="text" size=40  value="[register_free_webinar webid=xxxxxxx pageid=xxx]" name="shortcode" /></td>
    
    </tr>

</table>

</div>
<?php	
} 

function registration_forms($atts)
{		
	global $webinarErrors;
	extract(shortcode_atts(array( 'webid'=>'','pageid'=>''), $atts));	
	$output='';



$output.='<form name="webinar_registration" id="webinar_registration" action="" method="post" >
<style type="text/css">
.error{ color:red;}
.tableclass{width:125px;}
.tableborder{border: 1px solid #56B4EF;}
.tableheader{background:#007FC8;color:#ffffff;}
</style>
<table class="tableborder">';

$output.=$webinarErrors->get_error_message('broke');
   $output.='<th colspan="2" class="tableheader">Register for a Webinar</th>
    <tr>
        <td >First Name</td><td><input type="text" size=20  name="firstname" id="firstname" /></td>
    </tr>
    <tr>
        <td >Last Name </td><td><input type="text" size=20  name="lastname"  id="lastname" /></td>
    </tr>
    <tr>
        <td >Email</td><td><input type="text" size=20  name="email"     id="email" />
		<input type="hidden" name="returnpageid"      value="'.$pageid.'" /><input type="hidden" name="webinarid"   value="'.$webid.'" /></td>
    </tr>

    <tr>
        <td><input type="hidden" name="action" value="registerwebinars" /></td><td><input style="background:#629819; color:#ffffff; font-weight:bold;" type="submit" name="submit"  value="Register Now"/></td>
    </tr>
    
</table>
</form>';

   $output.='
<script type="text/javascript">
jQuery(document).ready(function($){	
	$("#webinar_registration").validate({
		rules:  {
					firstname   :{required:true},
					lastname    :{required:true},			
					email       :{required:true,email:true}	
				}							
		});
		
});
</script>';

return $output;
}


/*UPDATING ADMIN SECTION SETTINGS TO OPTION TABLE*/
if(isset($_POST['action']) && $_POST['action']=='savefreewebinar')
{
 	update_option( 'freewebinar_organizer_key',$_REQUEST['organizer_key']);
	update_option( 'freewebinar_access_token', $_REQUEST['access_token'] ); 	
}
/*SAVING WEBINAR REGISTRANT BEFORE SENDING TO REGISTRATION*/
function save_before_registration()
{
	global $webinarErrors;	
	if( isset($_REQUEST['action']) && $_REQUEST['action']=='registerwebinars' )
	{
		$organizer_key= get_option('freewebinar_organizer_key');
		$access_token = get_option('freewebinar_access_token');
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
			$sql="INSERT INTO wp_webinar(firstname,lastname,email,registration_id,returnpageid)values('".$_REQUEST['firstname']."','".$_REQUEST['lastname']."','".$_REQUEST['email']."','".$_REQUEST['webinarid']."','".$_REQUEST['returnpageid']."')";
			mysql_query($sql);
			$lastid=mysql_insert_id();
			webinar_curls_registration($lastid);
		}
	}	
}

/*REGISTERING TO WEBINAR VIA CURL AFTER SELECTING RECORDS FOM DB ON BASIS OF LAST INSERT ID*/		
function webinar_curls_registration($id)
{
			$sql="SELECT * FROM wp_webinar WHERE id='".$id."' ";
			$res=mysql_query($sql);
			$results= mysql_fetch_assoc($res);
			$organizer_key= get_option('freewebinar_organizer_key');
			$access_token = get_option('freewebinar_access_token');
			$return_url   = get_permalink($results['returnpageid']);
			$url= 'https://api.citrixonline.com/G2W/rest/organizers/'.$organizer_key.'/webinars/'.$results['registration_id'].'/registrants';

			$curl = curl_init($url);
			$curl_post_data = array(
			"firstName" =>$results['firstname'],
			"lastName"  =>$results['lastname'],
			"email"     =>$results['email']
			);
			$myOptions = array( 
			CURLOPT_POST => true, 
			CURLOPT_SSL_VERIFYHOST => 0, 
			CURLOPT_SSL_VERIFYPEER => 0, 
			CURLOPT_POSTFIELDS => json_encode($curl_post_data), 
			CURLOPT_URL => $url, 
			CURLOPT_RETURNTRANSFER => 1, 
			CURLOPT_HTTPHEADER => array( "Content-Type: application/json; charset=utf-8","Accept:application/json, text/javascript, */*; q=0.01", ("Authorization: OAuth oauth_token=".$access_token)));
			curl_setopt_array($curl, $myOptions);
			$curl_response = curl_exec($curl);
			echo '<META HTTP-EQUIV="Refresh" Content="0; URL='.$return_url.'">'; 	
}

