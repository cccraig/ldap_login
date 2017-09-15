<?php

// Require the updated LDAP class
require_once("adLDAP.php");

// Define Global variable
global $conf;



/**
 * Extend the Piwigo LDAP class using the adLDAP class.
 * That way we take advantage of code someone a lot smarter
 * than me has already written.
 */
class Ldap extends adLDAP {


	/**
	 * The configuration variable
	 *
	 * @var array
	 */
	public $config;



	/**
	 * Load the configuration parameters
	 * and call the parent constructor.
	 */
	function __construct( $options=array()) {

		$this -> load_config();

		parent::__construct( $this -> config );
	}



	/**
	 * Load the default LDAP configuration
	 */
	public function load_default_config() {

		// For LDAP only
		$d['account_suffix'] = "@mydomain.local";
		$d['base_dn'] = "DC=mydomain,DC=local";
		$d['ad_username'] = "pjenkins";
		$d['ad_password'] = "mySecretPass";
		$d['domain_controllers'] = array ("dc01.mydomain.local");
		$d['use_ssl'] = False;

		// Other options
		$d['allow_newusers'] = False;
		$d['advertise_admin_new_ldapuser'] = False;
		$d['send_password_by_mail_ldap'] = False;

		// Group Mappings
		$d['group_mapping'] = '';

		return $d;
	}



	/*
	 * Search for user
	 *
	 *@return Bool
	 */
	public function get_ldap_info($param, $filter) {
		$res = ldap_search(
			$this->_conn,
			$this->_base_dn,
			$filter,
			array(
				'samaccountname',
				'mail',
				'memberof',
				'department',
				'displayname',
				'telephonenumber',
				'primarygroupid',
				'objectsid'
			)
		);

		$entries = ldap_get_entries($this->_conn, $res);

		if(isset($entries[0])) {

			return $entries;

		}

		return null;
	}




	/*
	 * Load the saved configuration
	 *
	 *@return Bool
	 */
	public function load_config() {

		$conf_file = @file_get_contents( LDAP_LOGIN_PATH.'data.dat' );

		$defaults = $this -> load_default_config();

		if ($conf_file!==false)
		{
			
			$config = unserialize($conf_file);

			foreach ($config as $key => $value) {
				$defaults[$key] = $value;
			}
		}

		$this -> config = $defaults;

	}



	/*
	 * Function to save the user entered configuration
	 *
	 * @var array
	 *
	 * @return void
	 */
	public function save_config()
	{
		$file = fopen( LDAP_LOGIN_PATH.'/data.dat', 'w' );
		fwrite($file, serialize($this -> config) );
		fclose( $file );
	}



	/*
	 * Load the Ldap admin menu
	 */
	public function ldap_admin_menu($menu)
	{
		array_push($menu,
		array(
		'NAME' => 'Ldap Login',
		'URL' => get_admin_plugin_menu_link(LDAP_LOGIN_PATH.'/admin.php') )
		);
		return $menu;
	}



    /**
    * Check if successfully bound
    * 
    * @return bool
    */
    public function was_successful()
    {
          return $this -> _bind;
    }




	/**
	* Validate a user's login credentials
	* 
	* @param string $username A user's AD username
	* @param string $password A user's AD password
	* @param bool optional $prevent_rebind
	* @return bool
	*/
	function authenticate2($username, $password, $prevent_rebind = false) {

	    // Prevent null binding
	    if ($username === NULL || $password === NULL) { return false; } 
	    if (empty($username) || empty($password)) { return false; }
	    
	    // Bind as the user
	    $this->_bind = @ldap_bind($this->_conn, $username, $password);


	    if (!$this->_bind){

	    	return false;

	    } else {

	    	return true;

	    }
	}
}

?>
