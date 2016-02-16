<?php


function getUserMintLista($matricola_remote_user="",$emailAdmin=""){
	$urlMint= "http://inenel-svil.enelint.global/enel-mint/services/asset/";
	$rpcurl=$urlMint."search?rows=11&queryFav=false&queryFilter=1&queryString=resCode:".$matricola_remote_user;
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_URL, $rpcurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 80);
	curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
	curl_setopt($ch,CURLOPT_USERAGENT,"BlackBerry");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("x-eip2-email: ".$emailAdmin,"x-eip2-locale: it"));
	
	$results = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if(!$results)  return $error;
	return $results;
	
	
}
function getUserMintDettaglio($resource_code="",$emailAdmin=""){
	$urlMint= "http://inenel-svil.enelint.global/enel-mint/services/asset/";
	$rpcurl=$urlMint."detail?resId=".$resource_code;
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_URL, $rpcurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//	curl_setopt($ch, CURLOPT_TIMEOUT, 8);
	curl_setopt($ch, CURLOPT_TIMEOUT, 80);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
	curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
	curl_setopt($ch,CURLOPT_USERAGENT,"BlackBerry");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("x-eip2-email: ".$emailAdmin,"x-eip2-locale: it"));
	$results = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if(!$results)  return $error;
	return $results;


}
function getUserMintAvatar($resource_code="",$emailAdmin=""){
	$urlMint= "http://inenel-svil.enelint.global/enel-mint/services/asset/";
	$rpcurl=$urlMint."avatar?resId=".$resource_code;
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_URL, $rpcurl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 80);
	curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
	curl_setopt($ch,CURLOPT_USERAGENT,"BlackBerry");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("x-eip2-email: ".$emailAdmin,"x-eip2-locale: it"));
	$results = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);
	if(!$results)  return $error;
	return $results;


}
?>
