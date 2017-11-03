<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template;

/*
 * Specify the template for the admin page configuration tab
 */
$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/configuration.tpl') );



/*
 * Assign action to the configuration tab
 */
$template->assign(
  array(
    'PLUGIN_ACTION' => get_root_url().'admin.php?page=plugin-Ldap_Login-configuration',
    'PLUGIN_CHECK' => get_root_url().'admin.php?page=plugin-Ldap_Login-configuration',
    ));




/*
 * Function to save the user entered configuration
 *
 * @var array
 *
 * @return void
 */
function save_config($config)
{
	$file = fopen( LDAP_LOGIN_PATH.'/config/data.dat', 'w' );

  if($file) {
    fwrite($file, serialize($config) );
    fclose( $file );
  }
}






/*
 * Initialize new ldap class to check binding.
 * Use the try catch in case something major
 * goes wrong when we try to connect.
 */
function test_ldap() {
	global $template, $config;

	try {

		// Initialize LDAP
		$ldap = new Ldap( $config );

		// Load config in case it's empty
		$config = $ldap -> config;

		/*
		 * Assign variables to template
		 */
		$template->assign('HOST', 	$config['account_suffix']);
		$template->assign('BASEDN',	$config['base_dn']);
		$template->assign('DOMAIN_CONTROLLER',	$config['domain_controllers'][0]);
		$template->assign('LD_USE_SSL',	$config['use_ssl']);
		$template->assign('LD_BINDPW',	$config['ad_password']);
		$template->assign('LD_BINDDN',	$config['ad_username']);

		// try to bind LDAP
		if($ldap -> connect()) {

			$html_msg = '<p style="color:green;">LDAP connection successfully bound </p>';

		} else {

			$html_msg = '<p style="color:red;">LDAP connection not bound </p>';

		}

		$template -> assign('LD_CHECK_LDAP', $html_msg);

	} catch (adLDAPException $e) {

		$html_msg = '<p style="color:red;">ERROR: ' . $e -> getMessage() . '</p>';

		$template -> assign('LD_CHECK_LDAP', $html_msg);
	}
}




/*
 * Initialize new ldap class to check binding.
 */
function test_ldap_user($username, $password) {
	global $template;

	try {

		// Initialize LDAP
		$ldap = new Ldap();

		if ($ldap -> connect()) {

			include_once(LDAP_LOGIN_PATH.'/include/check_cn_or_mail.php');

			list($username, $mail, $info, $found) = test_for_cn_or_mail($ldap, $username);


			if($found) {

				$x = $ldap -> authenticate2($mail, $password);

			} else {

				$x = false;

			}

			if($x) {

				$html_msg = '<p style="color:green;">User successfully authenticated</p>';

			} else {

				$html_msg = '<p style="color:red;">User authentication failed</p>';

			}

			$template -> assign('LD_CHECK_LDAP_USER', $html_msg);
		}

	} catch(adLDAPException $e) {

		$html_msg = '<p style="color:red;">ERROR: ' . $e -> getMessage() . '</p>';

		$template -> assign('LD_CHECK_LDAP_USER', $html_msg);
	}
}





/*
 * First take care of configuration if submitted
 *
 * @var array
 */
$config = array();

if (isset($_POST['save'])){
	$config['account_suffix'] = $_POST['HOST'];
	$config['base_dn'] = $_POST['BASEDN'];
	$config['domain_controllers'] = array($_POST['DOMAIN_CONTROLLER']);
	$config['ad_username'] = $_POST['LD_BINDDN'];
	$config['ad_password'] = $_POST['LD_BINDPW'];

	if (isset($_POST['LD_USE_SSL'])){

		$config['use_ssl'] = True;

	} else {

		$config['use_ssl'] = False;

	}

	save_config( $config );

}





/*
 * Check if we're testing the LDAP connection
 *
 * @var array
 */
if (isset($_POST['check_ldap'])){

	$username = $_POST['USERNAME'];
	$password = $_POST['PASSWORD'];

	test_ldap_user($username, $password);
}


test_ldap();

$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');

?>
