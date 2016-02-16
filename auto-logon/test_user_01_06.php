<?php

function test_auto_login_user($matricola_remote_user,$utente){
	
	$newoptions = get_option('auto_login_options');
	if($newoptions['admin_email']!=""){
		$emailAdmin=$newoptions['admin_email'];
	}
	$userLista=getUserMintLista($matricola_remote_user,$emailAdmin);
	
	$jsonUser=json_decode($userLista);
	if($jsonUser === NULL)
    	return "Errore utente :".$userLista;
	$resultEsito=$jsonUser->header->status;
	$resNameOffset="";
	if($resultEsito=="OK"){
		$itemList=$jsonUser->body->itemsList;
		 
		foreach ($itemList as $index => $value) {
			$utente->user_login = $value->resCode;
			$utente->resNum = $value->resNum;
			$utente->user_name =formatName($value->resName);
			$utente->user_mail = $value->email;
		}
	
		
		//DettaglioUtente
		$userDettaglio=getUserMintDettaglio($utente->resNum,$emailAdmin);
		$jsonUserDettaglio=json_decode($userDettaglio);
		if($jsonUserDettaglio === NULL)
		   return "Errore dettaglio utente :".$userDettaglio;
		
		$resultEsitoDettaglio=$jsonUserDettaglio->header->status;
		 
		if($resultEsitoDettaglio=="OK"){
	
			$itemDettaglio=$jsonUserDettaglio->body;
			$resNameOffset=$itemDettaglio->resNameOffset;
			$utente->countryId = $itemDettaglio->countryId;
			$utente->countryName = $itemDettaglio->countryName;
			$utente->placeAddr=$itemDettaglio->placeAddr;
			$utente->placeCity=$itemDettaglio->placeCity;
			$utente->orgTitle=$itemDettaglio->orgTitle;
			$utente->compCode=$itemDettaglio->compCode;
			$utente->compTitle=$itemDettaglio->compTitle;
		}
		$userAvatar=getUserMintAvatar($utente->resNum,$emailAdmin);
		$jsonUserAvatar=json_decode($userAvatar);
		//print_r($jsonUserAvatar);
		if($jsonUserAvatar === NULL)
		   return "Errore avatar :".$userAvatar;
		$resultEsitoAvatar=$jsonUserAvatar->header->status;
		
		if($resultEsitoAvatar=="OK"){
	
			$itemAvatar=$jsonUserAvatar->body;
		//$arrLinkImg2= autologon_get_avatar();
		 //print_r($arrLinkImg2);
			$utente->avatarBase64 =$itemAvatar->avatarBase64;
			$utente->avatarByteLength =$itemAvatar->avatarByteLength;
			$utente->height =$itemAvatar->height;
			$utente->width =$itemAvatar->width;
		//	echo "Type".$itemAvatar->type;
	
			/*  if(function_exists( userphoto_profile_update)){
			 echo "esiste";
			echo   $userAvatar->width;
			 
			}else
			echo "Nooooooooo";
			*/
		}
		if($resNameOffset!=""){
			$utente->user_lastname  = 	mb_substr($utente->user_name, 0, $resNameOffset);
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
		
		// echo   $utente->user_firstname ;
	}else 
	     return $resultEsito;
	
	return $utente;
	
}


?>
