<?php
function auto_login_creaTable(){

	global $wpdb, $autologon_object;

	if ( auto_login_table_exists() )
	   return "table_exist";

	$charset_collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) )
		$charset_collate .= " COLLATE $wpdb->collate";
	}
	
	$sql="CREATE TABLE  IF NOT EXISTS ".$autologon_object->autologon_log." (
	  	`timestamp` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`blog_id` BIGINT(20) NOT NULL,
	`user_id` VARCHAR(200) NOT NULL,
	`blog_name` VARCHAR(200) NOT NULL,
	`display_name` VARCHAR(200) NOT NULL,
	`user_mail` VARCHAR(200) NOT NULL,
	`user_role` VARCHAR(200) NOT NULL,
	`user_login` VARCHAR(200) NOT NULL,
	`locId` VARCHAR(5) NOT NULL,
	`countryName` VARCHAR(40) NOT NULL,
	PRIMARY KEY (`timestamp`,`blog_id`) 
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED";
	
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$result= dbDelta($sql);

	if ( !  auto_login_table_exists() )
	  return "table_not_created"; // Failed to create
	else
	  return "table_created";

}
function auto_login_install () {
	$newoptions = get_option('auto_login_options');
	$newoptions['display_name'] = 'true';
	$newoptions['email'] = 'true';
	$newoptions['newuser'] = 'true';
	$newoptions['role_default'] = 'subscriber';
	$newoptions['first_last'] = 'true';
	$newoptions['nickname'] = 'true';
	$newoptions['user_photo'] = 'false';
	$newoptions['admin_email'] = 'jsonprovider.blogintra@enel.com';
	$newoptions['user_country'] = 'true';
	
	add_option('auto_login_options', $newoptions);
	auto_login_creaTable();
}
global $utenteTest;
$erroreTest="";
function auto_login_options() {
	 if( current_user_can('edit_users')){
	 	global $current_user;
		$msg_save='false'; 
		$options = $newoptions = get_option('auto_login_options');
		// if submitted, process results
		if ( $_POST["auto_logon_submit"] ) {
			$newoptions['display_name']= strip_tags(stripslashes($_POST["auto_login_display_name"]));
			$newoptions['email']  = strip_tags(stripslashes($_POST["auto_login_email"]));
			$newoptions['newuser'] = strip_tags(stripslashes($_POST["auto_login_newuser"]));
			$newoptions['role_default'] = strip_tags(stripslashes($_POST["auto_login_role_default"]));
			$newoptions['first_last'] = strip_tags(stripslashes($_POST["auto_login_first_last"]));
			$newoptions['nickname'] = strip_tags(stripslashes($_POST["auto_login_nickname"]));
			$newoptions['user_photo'] = strip_tags(stripslashes($_POST["auto_login_user_photo"]));
			$newoptions['user_country'] = strip_tags(stripslashes($_POST["auto_login_user_country"]));
			$newoptions['admin_email'] = strip_tags(stripslashes($_POST["auto_login_admin_email"]));
		
		}else 
		if ( $_POST["test_auto_logon_submit"] ) {
			
			$utenteTest = new Utente();
			$user_login_test=strip_tags(stripslashes($_POST["auto_login_user_login_test"]));
			$utenteTest=test_auto_login_user($user_login_test,$utenteTest);
			
			if(is_string($utenteTest))
			   $erroreTest=$utenteTest; else {
				
				
			}
		 
		
		}
	
		
		// any changes? save!
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('auto_login_options', $options);
			$msg_save='true';
		}
		// options form
		echo '<link rel="stylesheet" type="text/css" href="' . AUTOLOGON_PLUGIN_URL . '/css/admin.css" />';
		echo '<form method="post">';
		echo "<div class=\"wrap\"><h2>Auto-Logon options</h2>";
		echo '<h3>Output options</h3>';
		echo '<table class="form-table">';
		// Set Email Admin for Mint .
		echo '<tr valign="top"><th scope="row">Email Admin for Mint</th>';
		echo '<td><input  type="text" name="auto_login_admin_email" size="40" value="'.$options['admin_email'].'"';
		echo '></input><br />Enter the e-mail enabled contact Admin Mint.</td></tr>';
		
		// Enable display name update.
		echo '<tr valign="top"><th scope="row">Update display name?</th>';
		echo '<td><input type="checkbox" name="auto_login_display_name" value="true"';
		if( $options['display_name'] == "true" ){ echo ' checked="checked"'; }
		echo '></input><br />Enabling this option switches the plugin to update displaied name directly from Siteminder authentication if it is changed during logon.</td></tr>';

		// Enable email update.
		echo '<tr valign="top"><th scope="row">Update email?</th>';
		echo '<td><input type="checkbox" name="auto_login_email" value="true"';
		if( $options['email'] == "true" ){ echo ' checked="checked"'; }
		echo '></input><br />Enabling this option switches the plugin to update email directly from Siteminder authentication if it is changed during logon.</td></tr>';
		
		// Default Role
		echo '<tr valign="top"><th scope="row">Default Role</th>';
		echo '<td><select  name="auto_login_role_default">';
		if ( !isset($wp_roles) ) 
			$wp_roles = new WP_Roles();
		foreach ($wp_roles->get_names() as $role=>$roleName) {
			if($options['role_default'] == $role )
				echo '<option value="'.$role.'"  selected="selected">'.$roleName.'</option>';
			else
				echo '<option value="'.$role.'">'.$roleName.'</option>';
		}
		echo '</select><br />Select the default role of new user when SiteMinder authenticated user is not present in blogs.</td></tr>';	
		
		// Enable First Last Name update.
		echo '<tr valign="top"><th scope="row">Update First and Last Name?</th>';
		echo '<td><input type="checkbox" name="auto_login_first_last" value="true"';
		if( $options['first_last'] == "true" ){ echo ' checked="checked"'; }
		echo '></input><br />Enabling this option switches the plugin to update First and Last name directly from Siteminder authentication if it is changed during logon.</td></tr>';

		// Enable Nickname update.
		echo '<tr valign="top"><th scope="row">Update User Nickname?</th>';
		echo '<td><input type="checkbox" name="auto_login_nickname" value="true"';
		if( $options['nickname'] == "true" ){ echo ' checked="checked"'; }
		echo '></input><br />Enabling this option switches the plugin to update User Nickname directly from Siteminder authentication if it is changed during logon.</td></tr>';

		// Enable New User Creation.
		echo '<tr valign="top"><th scope="row">Create User on logon?</th>';
		echo '<td><input type="checkbox" name="auto_login_newuser" value="true"';
		if( $options['newuser'] == "true" ){ echo ' checked="checked"'; }
		echo '></input><br />Enabling this option allows to create a new WP User at logon if not alreay present.</td></tr>';
		
		// Enable download Address.
		echo '<tr valign="top"><th scope="row">Download user Address?</th>';
		echo '<td><input type="checkbox" name="auto_login_user_country" value="true"';
		if( $options['user_country'] == "true" ){
			echo ' checked="checked"';
		}
		echo '></input><br />This option enables the user to download the address .</td></tr>';
		
		// Enable download Photo.
		echo '<tr valign="top"><th scope="row">Download user Photo?</th>';
		echo '<td><input type="checkbox" name="auto_login_user_photo" value="true"';
		if( $options['user_photo'] == "true" ){
			echo ' checked="checked"';
		}
		echo '></input><br />This option enables the user to download the picture .</td></tr>';
		
		// close stuff
		echo '<input type="hidden" name="auto_logon_submit" value="true"></input>';
		echo '</table>';
		echo '<p class="submit"><input type="submit" value="Update Options &raquo;"></input></p>';
		echo "</div>";
		echo '</form>';
		/*************FORM EXPORT******************/
		echo '<h3>Report Statistiche</h3>';
		echo '<form action="'.AUTOLOGON_PLUGIN_URL.'/exportToCsvUser.php" method="post">';
		echo '<table class="form-table">';
		echo '<tr valign="top"><th scope="row">Data Inizio</th>';
		echo '<td><input type="text" name="dataStart" id="dataStart" value=""></input>aaaa-mm-gg</td>';
		echo '</tr>';
		echo '<tr valign="top"><th scope="row">Data Fine</th>';
		echo '<td><input type="text" name="dataEnd" id="dataEnd" value=""></input>aaaa-mm-gg</td>';
		echo '</tr>';
		echo '<tr valign="top"><th scope="row">Report Stat</th>';
		//echo '<input type="hidden" name="blog_id" value="'.get_current_blog_id().'"></input>';
		echo '<td><input type="submit" name="reportExport" id="reportExport" value="Report Stat."></input><br />Report sugli accessi degli utenti.</td>';
		echo '</tr>';
		echo '</table>';
		echo '</form>';

		/*************FORM PER TEST******************/
		echo '<h3>Test User</h3>';
		echo '<form method="post">';
		echo '<table class="form-table">';
		// UserName for Test .
		echo '<tr valign="top"><th scope="row">UserName for Test</th>';
		echo '<td><input type="text" name="auto_login_user_login_test" value=""';
		
		echo '></input>';
		echo '<input type="submit" value="Test User &raquo;"></input>';
		echo '<br />Enter the username for test.</td></tr>';
		echo '<input type="hidden" name="test_auto_logon_submit" value="true"></input>';
		echo '</table>';
		echo '</form>';
		
		
		if($msg_save == 'true'){
				echo "<div>";
				echo '<h3>Update successfully executed.</h3>';				
				echo "</div>";
		}
		
		echo '<table class="form-table-admin"  >';
		// UserName for Test .
		echo '<tr valign="top"><th scope="row">UserName : </th>';
		echo '<td>'.$utenteTest->user_login.' </td></tr>';
		echo '<tr valign="top"><th scope="row">Firstname : </th>';
		echo '<td>'.$utenteTest->user_firstname.' </td></tr>';
		echo '<tr valign="top"><th scope="row">Lastname : </th>';
		echo '<td>'.$utenteTest->user_lastname.' </td></tr>';
		echo '<tr valign="top"><th scope="row">Email : </th>';
		echo '<td>'.$utenteTest->user_mail.' </td></tr>';
		echo '<tr valign="top"><th scope="row">Country Id : </th>';
		echo '<td>'.$utenteTest->countryId.' </td></tr>';
		echo '<tr valign="top"><th scope="row">Country Name : </th>';
		echo '<td>'.$utenteTest->countryName.' </td></tr>';
		echo '<tr valign="top"><th scope="row">Place Addr : </th>';
		echo '<td>'.$utenteTest->placeAddr.' </td></tr>';
		echo '<tr valign="top"><th scope="row">Place City : </th>';
		echo '<td>'.$utenteTest->placeCity.' </td></tr>';
		echo '<tr valign="top"><th scope="row">Org Title : </th>';
		echo '<td>'.$utenteTest->orgTitle.' </td></tr>';
		echo '<tr valign="top"><th scope="row">Image : </th>';
		$avatarBase64=$utenteTest->avatarBase64;
		if($avatarBase64!=""){
		echo '<td><img alt="img embed" src="data:image/jpg;base64,'.$avatarBase64.'" width="120px" /></td></tr>';
		}
		echo '<tr valign="top"><th scope="row">Errore : </th>';
		echo '<td>'.$erroreTest.' </td></tr>';
		
		echo '</table>';
		
		
	}else{
		echo "<div class=\"wrap\"><h2>Display options</h2>";
		echo '<h3>You don\'t have permissions to configure this plugin.</h3>';
		echo "</div>";
	}
	
}

//uninstall all options
function auto_login_uninstall () {
	delete_option('auto_login_options');
}

// add the admin page
function auto_login_add_pages() {
	add_options_page('Auto Logon', 'Auto Logon', 8, __FILE__, 'auto_login_options');
}



?>