<?php

if (!defined('LDAP_LOGIN_PATH')) die('Hacking attempt!');

function test_for_cn_or_mail($ldap, $username) {

	// Check if using email or short name
	$pos = strrpos($username, $ldap->config['account_suffix']);

	// Get email if using cn
	if($pos === false) {

		// Filter query
		$filter = 'cn=' . $username;

		$info = $ldap -> get_ldap_info($username, $filter);

		// Quit if nothing is found
		if ($info == null) {
			return false;
		}

		$mail = $info[0]['mail'][0];

	} else {

		// Filter query
		$filter = 'userPrincipalName=' . $username;

		$info = $ldap -> get_ldap_info($username, $filter);

		// Quit if nothing is found
		if ($info == null) {
			return false;
		}

		$mail = $username;
		$username = $info[0]['samaccountname'][0];
	}

	return array($username, $mail, $info, true);
}

?>
