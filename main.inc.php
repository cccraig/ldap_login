<?php
/*
Plugin Name: Ldap_Login
Version: auto
Description: Allow piwigo authentication using LDAP
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=650
Author: cccraig
Original Author: 22decembre
Original Author URI: http://www.22decembre.eu
*/
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');


// +-----------------------------------------------------------------------+
// | Define plugin constants                                               |
// +-----------------------------------------------------------------------+
define('LDAP_LOGIN_ID',      basename(dirname(__FILE__)));
define('LDAP_LOGIN_PATH' ,   PHPWG_PLUGINS_PATH . LDAP_LOGIN_ID . '/');
define('LDAP_LOGIN_ADMIN',   get_root_url() . 'admin.php?page=plugin-' . LDAP_LOGIN_ID);
define('LDAP_LOGIN_VERSION', '1.3.1');

include_once(LDAP_LOGIN_PATH.'/class.ldap.php');

// +-----------------------------------------------------------------------+
// | Event handlers                                                        |
// +-----------------------------------------------------------------------+

add_event_handler('init', 'ld_init');

add_event_handler('try_log_user','login', 0, 4);

add_event_handler('get_admin_plugin_menu_links', array(&$ldap, 'ldap_admin_menu'));

// +-----------------------------------------------------------------------+
// | Admin menu loading                                                    |
// +-----------------------------------------------------------------------+


// Add ldap class to plugin
$ldap = new Ldap();
$ldap->load_config();
set_plugin_data($plugin['id'], $ldap);
unset($ldap);



// +-----------------------------------------------------------------------+
// | functions                                                             |
// +-----------------------------------------------------------------------+

function random_password( $length = 8 ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
}

function ld_init(){
	load_language('plugin.lang', LDAP_LOGIN_PATH);
}



/*
 * Check user login
 *
 * @var bool
 * @var string
 * @var string
 * @var bool
 *
 */
function login($success, $username, $password, $remember_me){

	global $conf;

	/*
	 * Initialize the LDAP Class
	 */
	$ldap = new Ldap();

	// Don't continue if LDAP cannot connect
	if(!$ldap -> connect()) {

		trigger_notify('login_failure', stripslashes($username));

		return false;
	}

	/* Check if using cn or mail to log in.
	 * Reason is two-fold. One is for cases where
	 * cn is not properly mapped to mail or vice versa.
	 * second is to make sure nobody gets duplicated
	 * by logging in with cn and then later with mail.
	 */
	include_once(LDAP_LOGIN_PATH.'/include/check_cn_or_mail.php');

	list($username, $mail, $info, $found) = test_for_cn_or_mail($ldap, $username);

	if(!$found) {
		trigger_notify('login_failure', stripslashes($username));
		return false;
	}

	// Try to authenticate the user through LDAP
	$auth = $ldap -> authenticate2($mail, $password);

	if ($auth) {

		// Make a new user in the piwigo database?
		if($ldap -> config['allow_newusers']) {

			// SQL query to find user in piwigo database
			$query = 'SELECT '.$conf['user_fields']['id'].' AS id FROM '.USERS_TABLE.' WHERE '.$conf['user_fields']['username'].' = \''.pwg_db_real_escape_string($username).'\' ;';

			// Query the user id
			$row = pwg_db_fetch_assoc(pwg_query($query));

			// Create new user if not exist
			if($row == null) {
			
				// Now actually create the user
				$id = register_user(
					$username,
					random_password(8),
					$mail,
					true
				);

				log_user($id, False);

			} else {

				$id = $row['id'];

				log_user($id, False);

			}
		}

		/*
		 * Do role mapping
		 */
		include_once(LDAP_LOGIN_PATH.'/include/ldap_group_mapping.php');
		map_ldap_groups($ldap, $info, $id);


		trigger_notify('login_success', stripslashes($username));
		return true;

	} else {

		trigger_notify('login_failure', stripslashes($username));
		return false;

	}
}
?>
