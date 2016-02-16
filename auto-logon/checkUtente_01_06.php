<?php

function auto_login_wp_admin_page_redirect() {
	if ( is_user_logged_in()) {

		wp_safe_redirect(admin_url(''));
		exit();
		//return false;
	}else{

	}
}

function auto_login($utente) {
	

    // error_log("inizio auto_login ".date("H:i:s")."</br>",0);

	/*Se non è loggato e se non ha appena fatto il logout*/
	//if ( !get_site_option( 'dm_remote_login' ) || (isset($_GET[ 'action' ]) && $_GET[ 'action' ] == 'logout') || isset( $_GET[ 'loggedout' ] ) ) {
	//var_dump("option=".$_GET[ 'loggedout' ]);
	//if ( isset( $_GET[ 'loggedout' ] ) ) {
	//auto_login_wp_admin_page_redirect();
	//}
	$logOut=false;
	if(  $_GET[ 'action' ] == 'logout' || isset( $_GET[ 'loggedout' ])){
		$logOut=true;
	}

	
    if ( !is_user_logged_in() && $logOut===false) {
		//if ( $logOut===false) {
		//echo "entrato auto_login</br>";
		require ( ABSPATH . WPINC . '/registration.php' );
		$remote_user=getenv('REMOTE_USER');
		if($remote_user==""){
			$remote_user=getenv('HTTP_REMOTE_USER');
		}

		if($remote_user!=""){
			$mint=false;
			$siteminder=false;
			$json_errors ="";
			$newoptions = get_option('auto_login_options');
			if($newoptions['admin_email']!=""){
				$emailAdmin=$newoptions['admin_email'];
			}

			if( strpos( $remote_user, "@" ) !== false ) {
				list($matricola_remote_user,$dominio_remote_user)=split("@",$remote_user);
				$utente->user_login =$matricola_remote_user;
				$userLista=getUserMintLista($matricola_remote_user,$emailAdmin);
				$jsonUser=json_decode($userLista);
				if($jsonUser === NULL)
				$json_errors= "Errore utente :".$userLista;

				$resultEsito=$jsonUser->header->status;
				$resNameOffset="";
				/*Se l'utente è stato trovato*/
				if($resultEsito=="OK"){
					$itemList=$jsonUser->body->itemsList;
					foreach ($itemList as $index => $value) {
						//$utente->user_login = $value->resCode;
						$utente->resNum = $value->resNum;
						$utente->user_name = formatName($value->resName);
						$utente->user_mail = $value->email;
					}
					list($utente->user_lastname,$utente->user_firstname)=mb_split(" ",$utente->user_name);
                    //scarica il DettaglioUtente
                     error_log(print_r($utente, true),0);
					if($utente->resNum!=""){
						
						$userDettaglio=getUserMintDettaglio($utente->resNum,$emailAdmin);
						$jsonUserDettaglio=json_decode($userDettaglio);
						if($jsonUserDettaglio === NULL)
						$json_errors= "Errore dettaglio utente :".$userDettaglio;

						$resultEsitoDettaglio=$jsonUserDettaglio->header->status;

						if($resultEsitoDettaglio=="OK"){
							$itemDettaglio=$jsonUserDettaglio->body;
							$resNameOffset=$itemDettaglio->resNameOffset;
							if(strcmp($newoptions['user_country'], 'true') == 0){
								$utente->countryId = $itemDettaglio->countryId;
								$utente->countryName = $itemDettaglio->countryName;
								$utente->placeAddr=$itemDettaglio->placeAddr;
								$utente->placeCity=$itemDettaglio->placeCity;
								$utente->orgTitle=$itemDettaglio->orgTitle;
								$utente->compCode=$itemDettaglio->compCode;
								$utente->compTitle=$itemDettaglio->compTitle;
								$utente->locId=$itemDettaglio->locId;
							}
						}
                        if(strcmp($newoptions['user_photo'], 'true') == 0){
                            error_log("user_photo attivo",0);
                            // error_log(print_r($jsonUserDettaglio, true), 0);
                            
                            if($jsonUserDettaglio->resLastUpdate > date("d/m/Y H:i")){
                                error_log("resLastUpdate >=", 0);

							    $userAvatar=getUserMintAvatar($utente->resNum,$emailAdmin);
    							$jsonUserAvatar=json_decode($userAvatar);
    							if($jsonUserAvatar === NULL)
    							$json_errors= "Errore avatar :".$userAvatar;

	    						$resultEsitoAvatar=$jsonUserAvatar->header->status;
    							if($resultEsitoAvatar=="OK" ){
                                    
	       							$itemAvatar=$jsonUserAvatar->body;
    
	       							$utente->avatarBase64 =$itemAvatar->avatarBase64;
        							$utente->avatarByteLength =$itemAvatar->avatarByteLength;
		    						$utente->height =$itemAvatar->height;
			    					$utente->width =$itemAvatar->width;
                                    
				    				/*  if(function_exists( userphoto_profile_update)){
					    			 echo "esiste";
						    		echo   $userAvatar->width;
    
	    							}else
		    						echo "Nooooooooo";
                                     */
			    					set_avatar($utente,$itemAvatar);
    							}
						
                            }
                            else {
                                error_log("resLastUpdate <", 0);
                            }
                        }
						// echo   $utente->user_firstname ;
					}
				}
				$mint=true;
				

			} //******************SITEMINDER***********************
			else if( strpos( $remote_user, "\\" ) !== false  ) {
				$siteminder=true;
				$utente->user_login = getenv('HTTP_REMOTE_USER');
				$utente->user_name = getenv('HTTP_REMOTE_DISPLAY_NAME');
				$utente->user_mail = getenv('HTTP_REMOTE_EMAIL');
				$utente->user_firstname = getenv('HTTP_REMOTE_FIRSTNAME');
				$utente->user_lastname = getenv('HTTP_REMOTE_LASTNAME');

			}else {
				$siteminder=true;
				$utente->user_login = getenv('HTTP_REMOTE_USER');
				$utente->user_name = getenv('HTTP_REMOTE_DISPLAY_NAME');
				$utente->user_mail = getenv('HTTP_REMOTE_EMAIL');
				$utente->user_firstname = getenv('HTTP_REMOTE_FIRSTNAME');
				$utente->user_lastname = getenv('HTTP_REMOTE_LASTNAME');

			}



			if($utente->user_name!="") {
				$output=preg_split('/\((.*)\)/', $utente->user_name, -1, PREG_SPLIT_DELIM_CAPTURE);
				if(!empty($output[1])) {
					$utente->user_name=sprintf("%s(%s)", $output[0], $output[1]);
				}
			}

			if($resNameOffset!=""){
				$utente->user_lastname  = mb_substr($utente->user_name, 0, $resNameOffset);
				$utente->user_firstname  = mb_substr($utente->user_name, $resNameOffset);
			}else {
				$userTemp=mb_split(" ",$utente->user_name);
				if(count($userTemp)>2){
					for ($i = 0; $i < (count($userTemp)-1); $i++) {
						if($i==0)
						$utente->user_lastname=$userTemp[$i];
						else
						$utente->user_lastname=$utente->user_lastname." ".$userTemp[$i];
					}
					$utente->user_firstname=end($userTemp);
				}else
				list($utente->user_lastname,$utente->user_firstname)=mb_split(" ",$utente->user_name);
					
			}


			//echo "utente=".$utente->user_login."</br>";
			if($utente->user_login!=""){
                      $nofn="nofn_";
                      $noln="noln_";
                      $noem="@noname.enel";
				
				if(strcmp(trim($utente->user_mail),"") == 0  ) $utente->user_mail=$utente->user_login.$noem;
				if(strcmp(trim($utente->user_firstname),"") == 0 ) $utente->user_firstname=$nofn.$utente->user_login;
				if(strcmp(trim($utente->user_lastname),"") == 0 ) $utente->user_lastname=$noln.$utente->user_login;

				$utente->user_name = $utente->user_firstname." ".$utente->user_lastname;
				global $wpdb;


				//echo  "User=".$user_login. "  user_name=".$user_name ." user_mail =".$user_mail  ;
				//get users password
				//if($utente->user_login!="" && $utente->user_name!="" && $utente->user_firstname!="" && $utente->user_lastname!=""){
				//$user = new WP_User(0,$utente->user_login);
				$user = new WP_User($utente->user_login);
				$user_pass = md5($user->user_pass);

				/****************UTENTE GIA' ESISTE *************************/
				if( username_exists($utente->user_login)){

					//login, set cookies, and set current user
					wp_login($utente->user_login, $user_pass, true);
					wp_setcookie($utente->user_login, $user_pass, true);
					//wp_set_current_user($user->ID, $user_login);
					$user_id =  $user->ID;
					$user_name_db = get_usermeta($user_id, 'display_name');
					$user_firstname_db = get_usermeta($user_id, 'first_name');
					$user_lastname_db = get_usermeta($user_id, 'last_name');
					$user_nickname_db = get_usermeta($user_id, 'nickname');
					$user_countryId_db =  get_usermeta($user_id, 'countryId');
					$user_countryName_db =  get_usermeta($user_id, 'countryName');
					$user_locId_db = get_usermeta($user_id, 'locId');
					$user_placeAddr_db = get_usermeta($user_id, 'placeAddr');
					$user_placeCity_db = get_usermeta($user_id, 'placeCity');
					$user_orgTitle_db = get_usermeta($user_id, 'orgTitle');
					$user_compCode_db = get_usermeta($user_id, 'compCode');
					$user_compTitle_db = get_usermeta($user_id, 'compTitle');

					$uid = get_userdata($user_id);
					$user_mail_db  = $uid->user_email;
					// error_log($user_name_db." ".$user_mail_db." ".$user_firstname_db." ".$user_lastname_db);

					// $newoptions = get_option('auto_login_options');

				
					if(strcmp($newoptions['user_country'], 'true') == 0){

						if(strcmp($utente->countryId,$user_countryId_db) != 0){
							if( $user_countryId_db=="" ){
								update_usermeta( $user_id, 'countryId',  $utente->countryId);
							}else if(  $utente->countryId !=""){
								update_usermeta($user_id, 'countryId' , $utente->countryId);
							}
						}
						if(strcmp($utente->countryName,$user_countryName_db) != 0){
							if( $user_countryName_db=="" ){
								update_usermeta( $user_id, 'countryName',  $utente->countryName);
							}else if(  $utente->countryName !=""){
								update_usermeta($user_id, 'countryName' , $utente->countryName);
							}
						}
						if(strcmp($utente->placeAddr,$user_placeAddr_db) != 0){
							if( $user_placeAddr_db == "" ){
								update_usermeta($user_id, 'placeAddr', $utente->placeAddr);
							}else if(  $utente->placeAddr!=""){
								update_usermeta($user_id, 'placeAddr' ,$utente->placeAddr);
							}
						}
						if(strcmp($utente->placeCity,$user_placeCity_db) != 0){
							if( $user_placeCity_db=="" ){
								update_usermeta($user_id, 'placeCity' , $utente->placeCity);
							}else if(  $utente->placeCity!=""){
								update_usermeta($user_id, 'placeCity' , $utente->placeCity);
							}
						}
						if(strcmp($utente->orgTitle,$user_orgTitle_db) != 0){
							if( $user_orgTitle_db=="" ){
								update_usermeta( $user_id, 'orgTitle' , $utente->orgTitle);
							}else if(  $utente->orgTitle!=""){
								update_usermeta( $user_id, 'orgTitle' , $utente->orgTitle);
							}
						}
						if(strcmp($utente->compCode,$user_compCode_db) != 0){
							if( $user_compCode_db=="" ){
								update_usermeta( $user_id, 'compCode',  $utente->compCode);
							}else if(  $utente->compCode !=""){
								update_usermeta($user_id, 'compCode' , $utente->compCode);
							}
						}
						if(strcmp($utente->compTitle,$user_compTitle_db) != 0){
							if( $user_compTitle_db=="" ){
								update_usermeta( $user_id, 'compTitle',  $utente->compTitle);
							}else if(  $utente->compTitle !=""){
								update_usermeta($user_id, 'compTitle' , $utente->compTitle);
							}
						}
						if(strcmp($utente->locId,$user_locId_db) != 0){
							if( $user_locId_db=="" ){
								update_usermeta( $user_id, 'locId',  $utente->locId);
							}else if(  $utente->locId !=""){
								update_usermeta($user_id, 'locId' , $utente->locId);
							}
						}
						
					}

				/*	if(strcmp($newoptions['display_name'], 'true') == 0){
						if(strcmp($utente->user_name,$user_name_db) != 0){
					
							update_usermeta( $user_id, 'display_name', $utente->user_name);
							wp_update_user(array('ID' => $user_id, 'display_name' => $utente->user_name));
						}
					}
					*/
					if(strcmp($newoptions['display_name'], 'true')  == 0){
						if(strcmp($utente->user_name,$user_name_db) != 0){
							if( $user_name_db=="" ){
								wp_update_user(array('ID' => $user_id, 'display_name' => $utente->user_name));
								update_usermeta( $user_id, 'display_name', $utente->user_name);
							}else if( strpos(  $utente->user_name, $nofn ) === false && $utente->user_name!=""){
								wp_update_user(array('ID' => $user_id, 'display_name' => $utente->user_name));
								update_usermeta( $user_id, 'display_name', $utente->user_name);
							}else if( strpos(  $utente->user_name, $nofn ) != false){
								if( strpos(  $user_name_db, $nofn ) != false){
									wp_update_user(array('ID' => $user_id, 'display_name' => $utente->user_name));
									update_usermeta( $user_id, 'display_name', $utente->user_name);
								} else {
									$utente->user_name = $user_name_db;
								}
								
							}
					
						}
					}
					
					if(strcmp($newoptions['email'], 'true')  == 0){
						if(strcmp($utente->user_mail,$user_mail_db) != 0){
							if( $user_mail_db=="" ){
								wp_update_user(array('ID' => $user_id, 'user_email' => $utente->user_mail));
							}else if( strpos(  $utente->user_mail, $noem ) === false && $utente->user_mail!=""){
								wp_update_user(array('ID' => $user_id, 'user_email' => $utente->user_mail));
							}else if( strpos(  $utente->user_mail, $noem ) != false){
								if( strpos(  $user_mail_db, $noem ) != false){
									wp_update_user(array('ID' => $user_id, 'user_email' => $utente->user_mail));
								} else {
									$utente->user_mail = $user_mail_db;
								}
							}

						}
					}

					if(strcmp($newoptions['first_last'], 'true')  == 0){
						if(strcmp($utente->user_firstname,$user_firstname_db) != 0){
							if( $user_firstname_db=="" ){
								update_usermeta( $user_id, 'first_name', $utente->user_firstname);
							}else if( strpos(  $utente->user_firstname, $nofn ) === false && $utente->user_firstname!=""){
								update_usermeta( $user_id, 'first_name', $utente->user_firstname);
							}else if( strpos(  $utente->user_firstname, $nofn ) != false){
								if( strpos(  $user_firstname_db, $nofn ) != false){
									update_usermeta( $user_id, 'first_name', $utente->user_firstname);
								} else {
									$utente->user_firstname = $user_firstname_db;
								}
								
							}

						}
						if(strcmp($utente->user_lastname,$user_lastname_db) != 0){
							if( $user_lastname_db=="" ){
								update_usermeta( $user_id, 'last_name', $utente->user_lastname);
							}else if( strpos(  $utente->user_lastname, $noln ) === false && $utente->user_lastname!=""){
								update_usermeta( $user_id, 'last_name', $utente->user_lastname);
							}else if( strpos(  $utente->user_lastname, $noln ) != false){
								if( strpos(  $user_lastname_db, $noln ) != false){
									update_usermeta( $user_id, 'last_name', $utente->user_lastname);
								} else {
									$utente->user_lastname = $user_lastname_db;
								}
							}

						}

					}

					if(strcmp($newoptions['nickname'], 'true')  == 0){
						if(strcmp($utente->user_name,$user_nickname_db) != 0){
							if( $user_nickname_db=="" ){
								update_usermeta( $user_id, 'nickname', $utente->user_name);
							}else if( strpos(  $utente->user_name, $noln ) === false && $utente->user_name!=""){
								update_usermeta( $user_id, 'nickname', $utente->user_name);
							}else if( strpos(  $utente->user_name, $noln ) != false){
								if( strpos(  $user_nickname_db, $noln ) != false){
									update_usermeta( $user_id, 'nickname', $utente->user_name);
								} 
							}
						}
					}

					if(strcmp($newoptions['newuser'], 'true')  == 0){
						global $current_user, $wp_roles;
						$ruolo=false;
						$userRole = new WP_User( $user_id);
						if ( !isset($wp_roles) )
						$wp_roles = new WP_Roles();
							
						// var_dump($wp_roles);

						foreach($userRole->roles as $key => $value) {
							$utente->user_role=$value;

						}

						foreach($wp_roles->roles as $role => $Role) {
							//$ruolii .=$role."="..",";
							if (strcmp($role,$utente->user_role)==0 ){
								$ruolo=true;
								break;
							}
						}

						if($ruolo==false)
						{
							$newoptions = get_option('auto_login_options');
							wp_update_user(array('ID' => $user_id, 'role' => $newoptions['role_default'] ));
							$utente->user_role=$newoptions['role_default'];
						}
                    }
                    // error_log(print_r($newoptions, true),0);

                    if(strcmp($newoptions['user_photo'], 'true') == 0){
                        error_log("user_photo attivo",0);
						$userAvatar=getUserMintAvatar($utente->resNum,$emailAdmin);
						$jsonUserAvatar=json_decode($userAvatar);
						if($jsonUserAvatar === NULL)
						$json_errors= "Errore avatar :".$userAvatar;

                        $resultEsitoAvatar=$jsonUserAvatar->header->status;
						if($resultEsitoAvatar=="OK" ){

                            $itemAvatar=$jsonUserAvatar->body;

							$utente->avatarBase64 =$itemAvatar->avatarBase64;
							$utente->avatarByteLength =$itemAvatar->avatarByteLength;
							$utente->height =$itemAvatar->height;
							$utente->width =$itemAvatar->width;
							set_avatar($utente,$itemAvatar);
							/*  if(function_exists( userphoto_profile_update)){
							 echo "esiste";
							echo   $userAvatar->width;

							}else
							echo "Nooooooooo";
                                */

                            /* Trovo la directory dove salvare l'avatar */
                            error_log("Aggiorna user_photo", 0);

                            // $uploads = wp_upload_dir();
                            // error_log(print_r($uploads, true),0);

					    }
                    }   

					wp_set_current_user($user->ID, $utente->user_login);
						
				}else{ /****************UTENTE NON ESISTE *************************/
					//Ma esiste un utente con la stessa mail
					if(email_exists($utente->user_mail)){
						//Cambio La email al vecchio Utente
						$uid = get_userdata(email_exists($utente->user_mail));
						update_usermeta( $uid->ID, 'user_email', "OLD".$utente->user_mail);
						wp_update_user(array('ID' => $uid->ID, 'user_email' => "OLD".$utente->user_mail ));
					}
					//Crea il nuovo Utente
						
					$user_login=sanitize_user( $utente->user_login);
					$random_password = wp_generate_password( 12, false );

					$newoptions = get_option('auto_login_options');
						
					if(strcmp($newoptions['newuser'], 'true')  == 0){
					
						$user_id = wp_create_user( $user_login, $random_password, $utente->user_mail  );
						
						wp_update_user(array('ID' => $user_id, 'role' => $newoptions['role_default'] ));
						$utente->user_role=$newoptions['role_default'];
						update_usermeta( $user_id, 'display_name', $utente->user_name);
						wp_update_user(array('ID' => $user_id, 'display_name' => $utente->user_name));

						update_usermeta( $user_id, 'nickname', $utente->user_nickname);
						update_usermeta( $user_id, 'first_name', $utente->user_firstname);
						update_usermeta( $user_id, 'last_name', $utente->user_lastname);
						$user = new WP_User($user_login);
						$user_pass = md5($user->user_pass);

						wp_login($user_login, $user_pass, true);
						wp_setcookie($user_login, $user_pass, true);
						wp_set_current_user($user->ID, $user_login);


					} else {
						// L'utente non viene loggato.
					}
						
				}

				global $current_user,$blog_id,$wpdb,$autologon_object;
				if ( auto_login_table_exists()){
					date_default_timezone_set('Europe/Rome');
					$result=$wpdb->insert( $autologon_object->autologon_log, array(
									'timestamp' => date( 'Y-m-d H:i:s', time()) ,
									'blog_id' => $blog_id,
									'user_id' => $current_user->ID,
									'blog_name' => get_bloginfo('name'),
					        'display_name' => $utente->user_name,
						      'user_mail' => $utente->user_mail,
				          'user_role' => $utente->user_role,
				          'user_login' => $utente->user_login,
									'locId'=> $utente->locId,
									'countryName'=> $utente->countryName

					), array( '%s', '%d','%s', '%s', '%s', '%s', '%s','%s','%s','%s' ) );
				}

			}else{
				return false;
			}
		}else{
			//echo "remote_user vuoto=".$remote_user."</br>";
		}
	}else{
		if ( !isset($_GET['loggedout']) && !isset($_POST['log'])  &&   $_GET[ 'action' ] != 'logout'){

			add_action('login_form_login','auto_login_wp_admin_page_redirect');
			//add_filter('login_redirect', 'wp_admin_page_redirect');
			//add_filter('login_form_login', 'admin_default_page');
		}
	}
	//echo "fine auto_login ".date("H:i:s")."</br>";
}


?>