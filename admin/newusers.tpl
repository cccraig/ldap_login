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
    border-radius: 10px;
    font-family: "arial-black";
    font-size: 14px;
    color: black;
    padding: 4px 8px;
}

</style>
{/literal}

<div class="titrePage">
	<h2>{'LDAP Login'|@translate}</h2>
</div>

<form method="post" action="{$PLUGIN_NEWUSERS}" class="general">

<fieldset>
	<legend>{'LDAP Login Configuration'|@translate}</legend>
	
    <p>
	{if $ALLOW_NEWUSERS}
		<input type="checkbox" id="allow_newusers" name="ALLOW_NEWUSERS" value="{$ALLOW_NEWUSERS}" checked />
	{else}
		<input type="checkbox" id="allow_newusers" name="ALLOW_NEWUSERS" value="{$ALLOW_NEWUSERS}" />
	{/if}
	{'Do you want to allow new piwigo users to be created when they authenticate succesfully?'|@translate}
    </p>

<!--     <p>
	{if $ADVERTISE_ADMINS}
		<input type="checkbox" id="advertise_admin_new_ldapuser" name="ADVERTISE_ADMINS" value="{$ADVERTISE_ADMINS}" checked />
	{else}
		<input type="checkbox" id="advertise_admin_new_ldapuser" name="ADVERTISE_ADMINS" value="{$ADVERTISE_ADMINS}" />
	{/if}
	{'Do you want admins to be notified by mail when new users are created after ldap login?'|@translate}
    </p>
    
    <p>
	{if $SEND_CASUAL_MAIL}
		<input type="checkbox" id="send_password_by_mail_ldap" name="SEND_CASUAL_MAIL" value="{$SEND_CASUAL_MAIL}" checked />
	{else}
		<input type="checkbox" id="send_password_by_mail_ldap" name="SEND_CASUAL_MAIL" value="{$SEND_CASUAL_MAIL}" />
	{/if}
	{'Do you want to send mail to the new users, like casual piwigo users receive?'|@translate}
    </p> -->
</fieldset>




<fieldset>
	<legend>{'LDAP Group Mapping'|@translate}</legend>
	<ul>
		<li>
			<label for="group1a">{'Comma separated list of LDAP groups'|@translate}</label><br></br>
			<input class="nice" size="100" type="text" id="group1a" name="GROUP1A" value="{$GROUP1A}" /><br></br>
		</li>
		
		<li>
			<label style="width:300px;" for="group1b">{'Comma separated list of Piwigo groups'|@translate}</label><br></br>
			<input class="nice" size="100" type="text" id="group1b" name="GROUP1B" value="{$GROUP1B}" /><br></br>
		</li>
	</ul>
</fieldset>
 
<p> <input type="submit" value="{'Save'|@translate}" name="save" /> </p>
	{if (!empty($ISSUE_WITH_GROUPS))}
		{$ISSUE_WITH_GROUPS}
	{/if}
</form>


<!-- Load LDAP groups for a user name -->
<form method="post" action="{$PLUGIN_NEWUSERS}" class="general">
	<fieldset>
		<legend>{'Load LDAP groups for a user'|@translate}</legend>
		<ul>
			<li>
				<label for="username">{'Test Username'|@translate}</label><br></br>
				<input class="nice" size="70" type="text" id="tuser" name="TUSER" value="{$TUSER}" /><br></br>
			</li>
		</ul>
		<i>LDAP must have been successfully configured previously</i>
	</fieldset>

	<p> <input type="submit" value="{'Check Groups'|@translate}" name="check_groups" /> </p>
	
	{if (!empty($USER_LDAP_GROUPS))}
		{$USER_LDAP_GROUPS}
	{/if}
</form>
