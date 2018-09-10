<?php

if (!defined('LDAP_LOGIN_PATH')) die('Hacking attempt!');

function test_for_cn_or_mail($ldap, $username) {

	// Check if using email or short name
	$pos = strrpos($username, $ldap->config['account_suffix']);

	// Get email if using cn
	if($pos === false) {

		// Filter query
		$filter = $ldap->config['username_attr'] . '=' . $username;

		$info = $ldap -> get_ldap_info($username, $filter);

		// Quit if nothing is found
		if ($info == null) {
			return false;
		}

		$mail = $info[0]['mail'][0];
                $login_attr = $ldap->config['login_attr'] === 'dn' ? $info[0]['dn'] : $info[0][$ldap->config['login_attr']][0];

	} else {

		// Filter query
		$filter = 'userPrincipalName=' . $username;

		$info = $ldap -> get_ldap_info($username, $filter);

		// Quit if nothing is found
		if ($info == null) {
			return false;
		}

		$mail = $username;
		$username = $info[0][$ldap->config['username_attr']][0];
                $login_attr = $ldap->config['login_attr'] === 'dn' ? $info[0]['dn'] : $info[0][$ldap->config['login_attr']][0];
	}

	return array($username, $mail, $login_attr, $info, true);
}

?>
