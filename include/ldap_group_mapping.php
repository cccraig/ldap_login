<?php

defined('LDAP_LOGIN_PATH') or die('Hacking attempt!');

function map_ldap_groups($ldap, $info, $id) {

	// Escaped User ID
	$uid = pwg_db_real_escape_string($id);

	// Get user groups and serialize
	$groups = serialize($info[0]['memberof']);

	// Get group mappings
	$mapping = $ldap -> config['group_mapping'];

	// $key is group id. $lg is an array of 
	// LDAP groups that should map to $key.
	foreach ($mapping as $key => $lg) {
		
		// Loop through each LDAP group
		foreach ($lg as $i => $v) {

			// Is the user in this group?
			$test = strpos($groups, $v);

			if($test == False) {

				$removals[] = $uid;

			} else {

				// Update their status for this group
				$inserts[] = array(
					'group_id' => $key,
					'user_id' => $uid,
				);

				// Exit inner loop
				break;
			}
		}
	}

	/*
	 * Remove any Piwigo groups the current user
	 * is no longer allowed to access based on their
	 * LDAP status. Then update their access to
	 * Piwigo groups based on LDAP status.
	 *
	 * This ensures that the user won't lose access
	 * because they went from, for example, being an
	 * undergrad to a graduate student. While their 
	 * Piwigo group might not change, If we delete 
	 * last, they lose access to the Piwigo group.
	 * Deleting first then updating prevents this.
	 */

	// The Delete
	$query = '
		DELETE FROM '. USER_GROUP_TABLE .'
		WHERE user_id = ' . $uid . '
		AND group_id IN('. implode(',', $removals) . ');';

	pwg_query($query);

	// The Insert
	mass_inserts(
		USER_GROUP_TABLE,
		array('group_id', 'user_id'),
		$inserts,
		array('ignore'=>true)
	);

	// The Cleanup
	include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
	invalidate_user_cache();
}

?>
