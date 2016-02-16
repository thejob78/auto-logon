<?php
class Utente {
	public $user_login = "";
	public $user_name = "";
	public $user_mail = "";
	public $user_firstname = "";
	public $user_lastname = "";
	public $resNum="";
	public $user_role="";
}

class UtenteCountry extends Utente
{
	public $countryId = "";
	public $contryName = "";
	public $placeAddr="";
	public $placeCity="";
	public $orgTitle="";
	public $compCode="";
	public $compTitle="";
}

class UtenteAvatar extends UtenteCountry
{
	public $avatarBase64="";
	public $avatarByteLength=0;
	public $height="";
	public $width="";
	
}


function auto_login_table_exists( $table = 'autologon_log' ) {
	global $wpdb, $autologon_object;

	if ( 'autologon_log' != $table )
	return false;

	if ( ! $table = $autologon_object->{$table} )
	return false;

	return strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) == strtolower( $table );
}


function autologon_object() {
	global $wpdb, $autologon_object;

	if ( is_object( $autologon_object ) )
	return;

	$autologon_object = (object) array(
		'autologon_log' => $wpdb->base_prefix . "autologon_log" );
}
autologon_object();

function formatName($name=NULL) {

	if (empty($name))
	return false;
	// mette tutto in minuscolo
	$name = strtolower($name);
	// trasforma in array
	$names_array = explode(' ',$name);
	for ($i = 0; $i < count($names_array); $i++) {
	
		  // "McDonald", "O'Conner"..etc
            if (strncmp($names_array[$i],'mc',2) == 0 || ereg('^[oO]\'[a-zA-Z]',$names_array[$i])) {
            $names_array[$i][2] = strtoupper($names_array[$i][2]);
    
            }
            // Prima lettera uppercase
            $names_array[$i] = ucfirst($names_array[$i]);
	}
	$name = implode(' ',$names_array);
	// Return upper-casing on all missed (but required) elements of the $name var
	return ucwords($name);
}


?>