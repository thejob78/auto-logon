<?php
/* 
	Plugin Name: Auto Logon
	Version: 3.2
	Author URI: 
	Plugin URI: 
	Description:  Permette di effetturare l'auto Login di un utente  gia' presente altrimenti lo aggiunge al db. Inoltre effettua l'aggiornamento automatico del display name, del first/last name  e dell'indirizzo email dell'utente a partire dai dati recuperati da SiteMinder. Tali opzioni sono attivabili dalla console del plugin. E' possibile anche configurare sempre da console il ruolo di default dell'utente che deve essere creato.
	V.3.1.3 (2014-05-30) Aggiunte Colonne con countryId e countryName
	V.3.1.3 (2013-04-16) Aggiunto $contryName
	v.3.1 (2013-03-25)	Implementata autologon_get_avatar
	v.3.0.9 (2012-05-12)	Cambiata la logica della verifica dei dati letti da sessione/json/database il confronto con nofn etc viene fatto con != e non più !== (in un caso da === a !=)
	v 3.0.8 (2012-05-12)	Se JsonProvider va in timeout (o comunque non ritorna niente) e il db ha dati "buoni" li uso anche per la sessione. (file checkUtente.php da riga 276-345 circa)
	v 3.0.7	Sisteamto bugs sui nofn,noln,noemail
	v 3.0.6a	Aggiunto locid
	v 3.0.6	Quando l'utente non esiste, ma esiste un utente con la sua email, rinomina la vecchia mail è crea un nuovo utente
	v 3.0.5a	Aggiunto anche compCode,compTitle all'utente
	v 3.0.5         Aggiunti  countryId,placeAddr,placeCity,orgTitle all'utente
	v 3.0.4a        Risolto problema cambio ruoli custom e separazione cognomi senza $resNameOffset!!
	v 3.0.3a        sistemata query per creare table sul db!!
	v 3.0.3	Piena compatibilità con REMOTE_USER per Kerberos e Siteminder, con HTTP_REMOTE_USER per la vecchia configurazione di Siteminder
			Aggiunto il riconoscimento anche in WP-ADMIN
			Aggiunta l'abilitazione della creazione di una nuova utenza di portale sul logon.
	v 3.0.2	aggiunto il css e test User 
	v 3.0		Gestione per Kerberos.
	v 2.2.b	Gestito il caso in cui mail e displayname sono vuoti.
	v 2.3.1	Aggiunta la gestione di firstname e lastname.
	v 2.3.2 (2010-09-30)	Si usa il "cn" invece del "displayName" in ADS per valorizzare REMOTE_DISPLAY_NAME 
					e se relativo ad un utente esterno si scarta l'ultimo token.
	v 2.3.3 (2010-10-01)	Valorizzo anche il nickname come "firtname lastname".
	v 2.3.4 (2010-10-06)	Corretto BUG: Quando si crea un utente le info sono errate solo dopo un aggiornamento
					le info sono corrette.
					Adesso il displayname, lastname e firstname sono obbligatori come info.
					Corretto BUG: Per utenti esteri vengono creati dei Display Name errati 
					(eg: Radu Cosarca r300088).
					Corretto BUG: Il display name viene scritto solo in wp_usermeta e non in wp_users. 

	Author: Marco Sorrentino, Maurizio Totti
	USAGE:
	LICENCE: GPL
*/


define( 'AUTOLOGON_VERSION', '3.0.9' );

if ( ! defined( 'AUTOLOGON_PLUGIN_BASENAME' ) )
define( 'AUTOLOGON_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'AUTOLOGON_PLUGIN_NAME' ) )
define( 'AUTOLOGON_PLUGIN_NAME', trim( dirname( AUTOLOGON_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'AUTOLOGON_PLUGIN_DIR' ) )
define( 'AUTOLOGON_PLUGIN_DIR',WP_PLUGIN_DIR . '/' . AUTOLOGON_PLUGIN_NAME );

if ( ! defined( 'AUTOLOGON_PLUGIN_URL' ) )
define( 'AUTOLOGON_PLUGIN_URL', WP_PLUGIN_URL . '/' . AUTOLOGON_PLUGIN_NAME );
define("DEBUG", false);
require_once AUTOLOGON_PLUGIN_DIR . '/admin.php';
require_once AUTOLOGON_PLUGIN_DIR . '/userphoto_custom.php';
require_once AUTOLOGON_PLUGIN_DIR . '/test_user.php';
require_once AUTOLOGON_PLUGIN_DIR . '/settings.php';
require_once AUTOLOGON_PLUGIN_DIR . '/checkUtente.php';
require_once AUTOLOGON_PLUGIN_DIR . '/auto-logon_mint.php';


global $utente;
$utente = new UtenteAvatar;

//echo "primo autologin ".date("H:i:s")."</br>";
add_action('init', 'auto_login');
// add the actions
add_action('admin_menu', 'auto_login_add_pages');
register_activation_hook( __FILE__, 'auto_login_install' );
register_deactivation_hook( __FILE__, 'auto_login_uninstall' );



?>
