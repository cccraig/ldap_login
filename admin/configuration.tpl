{literal}
<style>
label
{
    display: block;
    width: 250px;
    float: left;
}

.nice{
    display: inline-block;
    background: #000;
    border-radius: 10px;
    font-family: "arial-black";
    font-size: 14px;
    color: black;
    padding: 4px 8px;
}

</style>
{/literal}

<h2>{'Ldap Login'|@translate}</h2>

<div id="configContent">

<form method="post" action="{$PLUGIN_ACTION}" class="general">

	{if (!extension_loaded('ldap'))}
		<p style="color:red;">{'Warning: LDAP Extension missing.'|@translate}</p>
		<br />
	{/if}
	
	<fieldset class="mainConf">
	<legend>{'LDAP server host connection'|@translate}</legend>
	
	<ul>
		<li>
			<label for="host">{'LDAP server account suffix'|@translate}</label><br></br>
			<input class="nice" size="70" type="text" id="host" name="HOST" value="{$HOST}" /><br></br>
		</li>
	
		<li>
			<label for="port">{'Ldap domain controller'|@translate}</label><br></br>
			<input class="nice" size="70" type="text" id="port" name="DOMAIN_CONTROLLER" value="{$DOMAIN_CONTROLLER}" /><br></br>
		</li>

		<li>
			<label for="ld_use_ssl">
			{if $LD_USE_SSL }
				<input type="checkbox" id="ld_use_ssl" name="LD_USE_SSL" value="{$LD_USE_SSL}" checked />
			{else}
				<input type="checkbox" id="ld_use_ssl" name="LD_USE_SSL" value="{$LD_USE_SSL}" />
			{/if}
			{'Secure connexion'|@translate}</label>
		</li>
		</ul>

    </fieldset>
    
    <fieldset class="mainConf">
	<legend>{'Ldap attributes'|@translate}</legend>
	<ul>
		<li>
			<label style="width:500px;" for="basedn">{'Base DN (e.g.: ou=users,dc=example,dc=com)'|@translate}</label><br></br>
			<input class="nice" size="70" type="text" id="basedn" name="BASEDN" value="{$BASEDN}" />
		</li>
	</ul>
    </fieldset>
    
    <fieldset class="mainConf">
	<legend>{'Ldap connection credentials'|@translate}</legend>
	<ul>
		<li>
			<label style="width:300px;" for="ld_binddn">{'Bind Username'|@translate}</label><br></br>
			<input class="nice" size="70" type="text" id="ld_binddn" name="LD_BINDDN" value="{$LD_BINDDN}" /><br></br>
		</li>
		
		<li>
			<label for="ld_bindpw">{'Bind password'|@translate}</label><br></br>
			<input class="nice" size="70" type="password" id="ld_bindpw" name="LD_BINDPW" value="{$LD_BINDPW}" /><br></br>
		</li>
	</ul>

</fieldset>
 
<p>
<input type="submit" value="{'Save'|@translate}" name="save" />
</p>

	{if (!empty($LD_CHECK_LDAP))}
 		{$LD_CHECK_LDAP}
	{/if}

</form>

<form method="post" action="{$PLUGIN_CHECK}" class="general">
<fieldset class="mainConf">
<legend>{'Ldap_Login Test'|@translate}</legend>
<i>{'You must save the settings with the Save button above before testing here.'|@translate}</i>
	<ul>
		<li>
			<label for="username">{'Username'|@translate}</label><br></br>
			<input size="70" type="text" id="username" name="USERNAME" value="{$USERNAME}" /><br></br>
		</li>
		
		<li>
			<label for="ld_attr">{'Your password'|@translate}</label><br></br>
			<input size="70" type="password" id="password" name="PASSWORD" value="{$PASSWORD}" /><br></br>
		</li>
	</ul>

</fieldset>
<p><input type="submit" value="{'Test Settings'|@translate}" name="check_ldap" /></p>


{if (!empty($LD_CHECK_LDAP_USER))}
		{$LD_CHECK_LDAP_USER}
{/if}

</form>
</div>
