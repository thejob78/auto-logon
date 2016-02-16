<?php
function set_avatar($utente,$itemAvatar){
	$avatarType=$itemAvatar->type;

	/* Trovo la directory dove salvare l'avatar */
	if(is_multisite()){
	switch_to_blog(1);
		$upload_directory = wp_upload_dir();
		$upload_dir = $upload_directory['basedir'];
		//$upload_url = $upload_directory['baseurl'];
	restore_current_blog();
	}else
	{
		$upload_directory = wp_upload_dir();
		$upload_dir = $upload_directory['basedir'];
	}
	//$upload_url .="/avatars/";
	$dirDest=$upload_dir;
	$dirDest .= "/avatars/";
	
	if(!file_exists($dirDest))
		mkdir($dirDest, 0775);

	/* $autologon_validtypes = array(
	 "image/jpeg" => true,
			"image/pjpeg" => true,
			"image/gif" => true,
			"image/png" => true,
			"image/x-png" => true
	);*/


	/*Elimino i vecchi avaatr dell'utente*/
	$filesGlob = glob($dirDest.$utente->user_login."*.*");
	foreach ($filesGlob as $fileG) {
		unlink($fileG);
	}

	$_jpgimage=false;
	$_pngimage=false;
	$_gifimage=false;
	$est="jpg";
	if(preg_match('image/jpeg',$avatarType) || preg_match('image/pjpeg',$avatarType)){
		$_jpgimage=true;
	}
	else if(preg_match('image/png',$avatarType) || preg_match('image/x-png',$avatarType)){
		$_pngimage=true;
		$est="png";
	}
	else if(preg_match('image/gif',$avatarType) || preg_match('H',$avatarType)){
		$_gifimage=true;
		$est="gif";
	}

	$fileDest = $dirDest.$utente->user_login.".".$est;
	$fileDest80x80 = $dirDest.$utente->user_login."-80x80.".$est;
	$fileDest150x150 = $dirDest.$utente->user_login."-150x150.".$est;


	error_log($fileDest, 0);

	//  file_put_contents($file, $image_data);
	$imgdata =base64_decode($utente->avatarBase64);

	//  $f = finfo_open();

	// $mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);


	$img = imagecreatefromstring($imgdata);

	if($img != false)
	{
		if($_jpgimage){
			imagejpeg($img, $fileDest);
		}
		else if($_pngimage){
			imagepng($img, $fileDest);
		}
		else if($_gifimage){
			imagegif($img, $fileDest);
		}else {/*Default jpg*/
			imagejpeg($img, $fileDest);
			$_jpgimage=true;
		}

		list($imgwidth,$imgheight)=getimagesize($fileDest);

		$kwidth = $imgwidth/224;
		$kheight = $imgheight/240;

		error_log(print_r($itemAvatar->selection, true), 0);
		error_log('Dimensioni: '.$imgwidth.' '.$imgheight,0);
		error_log('Parametri: '.$kwidth.' '.$kheight,0);

		$selX1=($itemAvatar->selection->x1 != null ? $itemAvatar->selection->x1 : 0);
		$selY1=($itemAvatar->selection->y1 != null ? $itemAvatar->selection->y1 : 0);
		$selX2=($itemAvatar->selection->x2 != null ? $itemAvatar->selection->x2 : 224);
		$selY2=($itemAvatar->selection->y2 != null ? $itemAvatar->selection->y2 : 240);

		error_log('$selX1='.$selX1.' $selY1='.$selY1.' $selX2='.$selX2.' $selY2='.$selY2, 0);

		$img80x80 = imagecreatetruecolor(80,80);
		$img150x150 = imagecreatetruecolor(150,150);

		imagecopyresampled($img80x80, $img, 0, 0,
				round($selX1*$kwidth),
				round($selY1*$kheight),
				80, 80,
				round(($selX2-$selX1)*$kwidth),
				round(($selY2-$selY1)*$kheight));

		imagecopyresampled($img150x150, $img, 0, 0,
				round($selX1*$kwidth),
				round($selY1*$kheight),
				150, 150,
				round(($selX2-$selX1)*$kwidth),
				round(($selY2-$selY1)*$kheight));


		if($_jpgimage){
			imagejpeg($img80x80, $fileDest80x80);
			imagejpeg($img150x150, $fileDest150x150);
		}
		else if($_pngimage){
			imagepng($img80x80, $fileDest80x80);
			imagepng($img150x150, $fileDest150x150);
		}
		else if($_gifimage){
			imagegif($img80x80, $fileDest80x80);
			imagegif($img150x150, $fileDest150x150);
		}

		imagedestroy($img);
		imagedestroy($img80x80);
		imagedestroy($img150x150);
	}
	return;
}

function autologon_get_avatar( $id_or_email){
	global $wpdb,$current_user,$blog_id;

	if(!isset($id_or_email) || empty($id_or_email)){
		get_currentuserinfo();
		$utente=$current_user->user_login;
		$userid=$current_user->ID;
		
	}
	else if(is_numeric($id_or_email)){
		$userid = (int)$id_or_email;
		$user_info = get_userdata($userid);
		$utente=$user_info->user_login;
	}
	else if(is_string($id_or_email)){
		$userid = (int)$wpdb->get_var("SELECT ID FROM $wpdb->users WHERE user_email = '" . mysql_escape_string($id_or_email) . "'");
		$user_info = get_userdata($userid);
		$utente=$user_info->user_login;
	}
	if(!$userid)
		return array();
	if(is_multisite()){
		switch_to_blog(1);
		$upload_directory = wp_upload_dir();
		$upload_dir = $upload_directory['basedir'];
		$upload_url = $upload_directory['baseurl'];
		restore_current_blog();
	}else
	{
		$upload_directory = wp_upload_dir();
		$upload_dir = $upload_directory['basedir'];
		$upload_url = $upload_directory['baseurl'];
	}
	
	$upload_url .="/avatars/";
	$dirDest=$upload_dir;
	$dirDest .= "/avatars/";
	$filesGlob = glob($dirDest.$utente."*.*");
	$img=array();
	/*Prendo le img dell'utente*/
	foreach ($filesGlob as $fileG) {
		$urlimage=$upload_url.basename($fileG);
		array_push($img,$urlimage);
		$key['maxlen'] = strlen($urlimage);
		$sort_img[] = $key['maxlen'];
	}
	/*ordino in base alla lunghezza del file*/
	array_multisort($sort_img, $img);
	if(count($img)==0){
		/*Prendo le img di default*/
		$urlDefault=WP_PLUGIN_URL."/auto-logon/image/";
		array_push($img,$urlDefault."profile-avatar.gif");
		array_push($img,$urlDefault."avatar-vuoto-80x80.jpg");
		array_push($img,$urlDefault."avatar-vuoto-150x150.jpg");
		
	}
	
	/**Restituisco le img dell'utente*/
	// print_r($img);
	return $img ;
}
//add_filter('get_avatar', 'autologon_filter_get_avatar', 3, 2);
?>