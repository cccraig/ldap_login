<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template;

/*
 * Initialize Ldap and load the configuration
 */
$ldap = new Ldap();
$config = $ldap->config;

/*
 * Specify the template for the admin page configuration tab
 */
$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/configuration.tpl') );



/*
 * Assign action to the configuration tab
 */
$template->assign(
  array(
    'PLUGIN_ACTION' => get_root_url().'admin.php?page=plugin-LdapLogin-configuration',
    'PLUGIN_CHECK' => get_root_url().'admin.php?page=plugin-LdapLogin-configuration',
    )
);




/*
 * Initialize new ldap class to check binding.
 * Use the try catch in case something major
 * goes wrong when we try to connect.
 */
function test_ldap() {
	global $template, $page;

  $ldap = new Ldap();

	try {
		if($ldap -> connect()) {
      array_push($page['infos'], l10n('LDAP connection successfully bound'));
		} else {
      array_push($page['errors'], l10n('LDAP failed to bind'));
		}

	} catch (adLDAPException $e) {
    array_push($page['errors'], l10n('Error: '.$e->getMessage()));
	}
}




/*
 * Initialize new ldap class to check binding.
 */
function test_ldap_user($username, $password) {
	global $template, $page;

  $ldap = new Ldap();

	try {

		if ($ldap -> connect()) {

			include_once(LDAP_LOGIN_PATH.'/include/check_cn_or_mail.php');

			list($username, $mail, $login_attr, $info, $found) = test_for_cn_or_mail($ldap, $username);

			if($found) {


				$x = $ldap -> authenticate2($login_attr, $password);

			} else {

				$x = false;

			}

			if($x) {
        array_push($page['infos'], l10n('User successfully authenticated'));
			} else {
        array_push($page['errors'], l10n('User could not be authenticated'));
			}
		}

	} catch(adLDAPException $e) {
    array_push($page['errors'], l10n('Error: '.$e->getMessage()));
	}
}




/*
 * First take care of configuration if submitted
 *
 * @var array
 */
if (isset($_POST['save'])) {
  $ldap = new Ldap();
  $config = $ldap->config;
	$config['account_suffix'] = $_POST['HOST'];
	$config['base_dn'] = $_POST['BASEDN'];
	$config['login_attr'] = $_POST['LOGIN_ATTR'];
	$config['username_attr'] = $_POST['USERNAME_ATTR'];
        $config['use_memberof'] = isset($_POST['USE_MEMBEROF']);
	$config['group_base_dn'] = $_POST['GROUP_BASEDN'];
	$config['groupid_attr'] = $_POST['GROUPID_ATTR'];
        $config['group_user_attr'] = $_POST['GROUP_USER_ATTR'];
        $config['group_use_fulldn'] = isset($_POST['GROUP_USE_FULLDN']);
	$config['domain_controllers'] = array($_POST['DOMAIN_CONTROLLER']);
	$config['ad_username'] = $_POST['LD_BINDDN'];
	$config['ad_password'] = $_POST['LD_BINDPW'];

	if (isset($_POST['LD_USE_SSL'])){

		$config['use_ssl'] = True;

	} else {

		$config['use_ssl'] = False;

	}

  $ldap->config = $config;
  $ldap->save_config();
  test_ldap();
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



/*
 * Assign variables to template
 */
$template->assign('HOST', 	$config['account_suffix']);
$template->assign('BASEDN',	$config['base_dn']);
$template->assign('LOGIN_ATTR',	array_key_exists('login_attr', $config) ? $config['login_attr'] : 'dn');
$template->assign('USERNAME_ATTR',    array_key_exists('username_attr', $config) ? $config['username_attr'] : 'cn');
$template->assign('USE_MEMBEROF', array_key_exists('use_memberof', $config) ? $config['use_memberof'] : true);
$template->assign('GROUP_BASEDN', array_key_exists('group_base_dn', $config) ? $config['group_base_dn'] : '');
$template->assign('GROUPID_ATTR', array_key_exists('groupid_attr', $config) ? $config['groupid_attr'] : 'cn');
$template->assign('GROUP_USER_ATTR', array_key_exists('group_user_attr', $config) ? $config['group_user_attr'] : 'memberuid');
$template->assign('GROUP_USE_FULLDN', array_key_exists('group_use_fulldn', $config) ? $config['group_use_fulldn'] : false);
$template->assign('DOMAIN_CONTROLLER',	$config['domain_controllers'][0]);
$template->assign('LD_USE_SSL',	$config['use_ssl']);
$template->assign('LD_BINDPW',	$config['ad_password']);
$template->assign('LD_BINDDN',	$config['ad_username']);
$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');

?>
