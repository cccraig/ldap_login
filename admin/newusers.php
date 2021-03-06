<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template, $page;

/*
 * Specify the template for the admin page new users tab
 */
$template->set_filenames( array('plugin_admin_content' => dirname(__FILE__).'/newusers.tpl') );


/*
 * Assign action to the newuser tab
 */
$template->assign(
  array('PLUGIN_NEWUSERS' => get_root_url().'admin.php?page=plugin-LdapLogin-newusers')
);



/*
 * Get all piwigo groups. Note $groups
 * is combined differently below.
 */
$res = query2array('SELECT id, name FROM ' . GROUPS_TABLE . ' GROUP BY id;');
$group_name = array_column($res, "name");
$group_ids = array_column($res, "id");
$groups = array_combine($group_ids, $group_name);



/*
 * Initialize ldap class. No need to
 * connect with ldap server, just need
 * configuration information.
 */
$ldap = new Ldap();


/*
 * Process form post
 */
if (isset($_POST['save'])) {

	if (isset($_POST['ALLOW_NEWUSERS'])){
		$ldap->config['allow_newusers'] = True;
	} else {
		$ldap->config['allow_newusers'] = False;
	}

	if (isset($_POST['ADVERTISE_ADMINS'])){
		$ldap->config['advertise_admin_new_ldapuser'] = True;
	} else {
		$ldap->config['advertise_admin_new_ldapuser'] = False;
	}

	if (isset($_POST['SEND_CASUAL_MAIL'])){
		$ldap->config['send_password_by_mail_ldap'] = True;
	} else {
		$ldap->config['send_password_by_mail_ldap'] = False;
	}

	$ldap_groups = (isset($_POST['GROUP1A'])) ? explode(",", pwg_db_real_escape_string(str_replace(" ", "", $_POST['GROUP1A']))) : '';
	$piwigo_groups = (isset($_POST['GROUP1B'])) ? explode(",", pwg_db_real_escape_string(str_replace(" ", "", $_POST['GROUP1B']))) : '';

	if(count($piwigo_groups) != count($ldap_groups)) {

    array_push($page['errors'], l10n('Unequal number of LDAP and Piwigo groups'));

  } else {

		$map = array();
    $g = array_combine($group_name, $group_ids);

		foreach ($ldap_groups as $key => $lg) {
      if ($lg !== "") {
			     $map[$g[$piwigo_groups[$key]]][] = $lg;
      }
		}

		$ldap -> config['group_mapping'] = $map;
	}

	// Save the new configuration
	$ldap -> save_config();

  array_push($page['info'], l10n('LDAP group to Piwigo group mapping successfully saved.'));
}



/*
 * Test ldap user groups. Does not authenticate the user.
 */
if (isset($_POST['check_groups'])){

	// Don't do anything unless they entered a username/email to
	if(isset($_POST['TUSER']) && $_POST['TUSER'] != "") {

		$username = $_POST['TUSER'];

		try {

			// Ldap is already initialized. Open a connection
			if($ldap -> connect()) {

        $path = rtrim(LDAP_LOGIN_PATH, '/') . '/include/check_cn_or_mail.php';

				include_once($path);

				list($username, $mail, $login_attr, $info, $found) = test_for_cn_or_mail($ldap, $username);

				if(!$found) {

            array_push($page['errors'], l10n('User not found'));

				} else {

					$groups = $info[0]['memberof'];
					unset($groups['count']);

					$html_msg = '<p style="color:green;"><ol>';

					// Process data to nice html format
					foreach ($groups as $key => $value) {
						$split = explode(',', $value);
						$group = str_replace("CN=", "", strtoupper($split[0]));
						$html_msg = $html_msg . "<li>" . $group . "</li>";
					}

					$html_msg = $html_msg . "</ul></p>";

          $template -> assign('USER_LDAP_GROUPS', $html_msg);
				}

			} else {
        array_push($page['errors'], l10n('LDAP connection unsuccessful'));
			}

		} catch(adLDAPException $e) {

      array_push($page['errors'], l10n('ERROR:'.$e->getMessage()));

		}

	} else {

    array_push($page['errors'], l10n('You must specify a user!'));

	}
}



/*
 * Assign configuration values to page template
 */
$template->assign('ALLOW_NEWUSERS',		$ldap->config['allow_newusers']);
$template->assign('ADVERTISE_ADMINS',	$ldap->config['advertise_admin_new_ldapuser']);
$template->assign('SEND_CASUAL_MAIL',	$ldap->config['send_password_by_mail_ldap']);

/*
 * Loop through group mappings and assign
 * value is an array
 */
list($g1, $g2) = ['', ''];

if($ldap -> config['group_mapping'] !== '') {
	foreach ($ldap -> config['group_mapping'] as $key => $value) {
		foreach ($value as $i => $v) {
			if($key !== "") {
				$g2 = $g2 . ', ' . $groups[$key];
				$g1 = $g1 . ', ' . $v;
			}
		}
	}
}

$g1 = ltrim($g1, ',');
$g2 = ltrim($g2, ',');

$template->assign('GROUP1A', $g1);
$template->assign('GROUP1B', $g2);

/*
 * Serve the template content
 */
$template->assign_var_from_handle( 'ADMIN_CONTENT', 'plugin_admin_content');

?>
