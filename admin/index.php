<?php
/*******************************************************************************
*  Title: Help Desk Software HESK
*  Version: 2.5.5 from 5th August 2014
*  Author: Klemen Stirn
*  Website: http://www.hesk.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2005-2013 Klemen Stirn. All Rights Reserved.
*  HESK is a registered trademark of Klemen Stirn.

*  The HESK may be used and modified free of charge by anyone
*  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
*  By using this code you agree to indemnify Klemen Stirn from any
*  liability that might arise from it's use.

*  Selling the code for this program, in part or full, without prior
*  written consent is expressly forbidden.

*  Using this code, in part or full, to create derivate work,
*  new scripts or products is expressly forbidden. Obtain permission
*  before redistributing this software over the Internet or in
*  any other medium. In all cases copyright and header must remain intact.
*  This Copyright is in full effect in any country that has International
*  Trade Agreements with the United States of America or
*  with the European Union.

*  Removing any of the copyright notices without purchasing a license
*  is expressly forbidden. To remove HESK copyright notice you must purchase
*  a license for this script. For more information on how to obtain
*  a license please visit the page below:
*  https://www.hesk.com/buy.php
*******************************************************************************/

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'nuMods_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();

/* What should we do? */
$action = hesk_REQUEST('a');

switch ($action)
{
    case 'do_login':
    	do_login();
        break;
    case 'login':
    	print_login();
        break;
    case 'logout':
    	logout();
        break;
    default:
    	hesk_autoLogin();
    	print_login();
}

/* Print footer */
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

/*** START FUNCTIONS ***/
function do_login()
{
	global $hesk_settings, $hesklang, $nuMods_settings;

    $hesk_error_buffer = array();

    $user = hesk_input( hesk_POST('user') );
    if (empty($user))
    {
		$myerror = $hesk_settings['list_users'] ? $hesklang['select_username'] : $hesklang['enter_username'];
        $hesk_error_buffer['user'] = $myerror;
    }
    define('HESK_USER', $user);

	$pass = hesk_input( hesk_POST('pass') );
	if (empty($pass))
	{
    	$hesk_error_buffer['pass'] = $hesklang['enter_pass'];
	}

	if ($hesk_settings['secimg_use'] == 2 && !isset($_SESSION['img_a_verified']))
	{
		// Using ReCaptcha?
		if ($hesk_settings['recaptcha_use'])
		{
			require_once(HESK_PATH . 'inc/recaptcha/recaptchalib.php');

			$resp = recaptcha_check_answer($hesk_settings['recaptcha_private_key'],
			$_SERVER['REMOTE_ADDR'],
			hesk_POST('recaptcha_challenge_field', ''),
			hesk_POST('recaptcha_response_field', '')
            );

			if ($resp->is_valid)
			{
				$_SESSION['img_a_verified']=true;
			}
			else
			{
				$hesk_error_buffer['mysecnum']=$hesklang['recaptcha_error'];
			}
		}
		// Using PHP generated image
		else
		{
			$mysecnum = intval( hesk_POST('mysecnum', 0) );

			if ( empty($mysecnum) )
			{
				$hesk_error_buffer['mysecnum'] = $hesklang['sec_miss'];
			}
			else
			{
				require(HESK_PATH . 'inc/secimg.inc.php');
				$sc = new PJ_SecurityImage($hesk_settings['secimg_sum']);
				if ( isset($_SESSION['checksum']) && $sc->checkCode($mysecnum, $_SESSION['checksum']) )
				{
					$_SESSION['img_a_verified'] = true;
				}
				else
				{
					$hesk_error_buffer['mysecnum'] = $hesklang['sec_wrng'];
				}
			}
		}
	}

    /* Any missing fields? */
	if (count($hesk_error_buffer)!=0)
	{
    	$_SESSION['a_iserror'] = array_keys($hesk_error_buffer);

	    $tmp = '';
	    foreach ($hesk_error_buffer as $error)
	    {
	        $tmp .= "<li>$error</li>\n";
	    }
	    $hesk_error_buffer = $tmp;

	    $hesk_error_buffer = $hesklang['pcer'].'<br /><br /><ul>'.$hesk_error_buffer.'</ul>';
	    hesk_process_messages($hesk_error_buffer,'NOREDIRECT');
        print_login();
        exit();
	}
    elseif (isset($_SESSION['img_a_verified']))
    {
		unset($_SESSION['img_a_verified']);
    }

	/* User entered all required info, now lets limit brute force attempts */
	hesk_limitBfAttempts();

	$result = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."users` WHERE `user` = '".hesk_dbEscape($user)."' LIMIT 1");
	if (hesk_dbNumRows($result) != 1)
	{
        hesk_session_stop();
    	$_SESSION['a_iserror'] = array('user','pass');
    	hesk_process_messages($hesklang['wrong_user'],'NOREDIRECT');
        print_login();
        exit();
	}

	$res=hesk_dbFetchAssoc($result);
	foreach ($res as $k=>$v)
	{
	    $_SESSION[$k]=$v;
	}

    // Check if the user should be authenticated via Active Directory / LDAP
    $usesLdap = $res['UsesLDAP'];
    if ($usesLdap) {
        //-- do AD-specific logic here.
        $application_user = $nuMods_settings['ldap_application_user'];
        $password = $nuMods_settings['ldap_application_password'];

        //-- Connect to LDAP server
        $connectionIp = $nuMods_settings['ldap_server_ip'];
        $port = $nuMods_settings['ldap_server_port'];
        $connection = ldap_connect($connectionIp, $port);
        if ($connection == false) {
            die("Couldn't connect to LDAP server.");
        }

        //-- Bind the application user to the connection
        $bind = ldap_bind($connection, $application_user, $password);
        if ($bind == false) {
            die("Couldn't authenticate as the application user.");
        }

        //-- Find the user's DN
        //TODO LDAP escape the $user string!
        $dnQuery = "(&(uid=" . $user . ")(objectClass=person))";
        $search_base = $nuMods_settings['ldap_search_base'];
        $search = ldap_search(
            $connection, $search_base, $dnQuery, array('dn')
        );
        if ($search == false) {
            die("Search failed.");
        }

        $search_result = ldap_get_entries($connection, $search);
        if ($search_result == false) {
            die("Couldn't pull information from LDAP/AD server");
        }
        $userdn = '';
        if ((int) @$search_result['count'] > 0) {
            // Definitely pulled something, we don't check here
            //     for this example if it's more results than 1,
            //     although you should.
            $userdn = $result[0]['dn'];
        }

        if (trim((string) $userdn) == '') {
            die("Empty DN. Something is wrong.");
        }

        // Authenticate with the newly found DN and user-provided password
        $auth_status = ldap_bind($connection, $userdn, $pass);
        if ($auth_status === FALSE) {
            //-- Login failed!
            $_SESSION['a_iserror'] = array('pass');
            hesk_process_messages($hesklang['wrong_pass'],'NOREDIRECT');
        }

    }

    /* Check password */
    if (hesk_Pass2Hash($pass) != $_SESSION['pass'])
    {
        hesk_session_stop();
        $_SESSION['a_iserror'] = array('pass');
        hesk_process_messages($hesklang['wrong_pass'],'NOREDIRECT');
        print_login();
        exit();
    }

    $pass_enc = hesk_Pass2Hash($_SESSION['pass'].strtolower($user).$_SESSION['pass']);

    /* Check if default password */
    if ($_SESSION['pass'] == '499d74967b28a841c98bb4baaabaad699ff3c079')
    {
        hesk_process_messages($hesklang['chdp'],'NOREDIRECT','NOTICE');
    }

    unset($_SESSION['pass']);

	/* Login successful, clean brute force attempts */
	hesk_cleanBfAttempts();

	/* Regenerate session ID (security) */
	hesk_session_regenerate_id();

	/* Remember username? */
	if ($hesk_settings['autologin'] && hesk_POST('remember_user') == 'AUTOLOGIN')
	{
		setcookie('hesk_username', "$user", strtotime('+1 year'));
		setcookie('hesk_p', "$pass_enc", strtotime('+1 year'));
	}
	elseif ( hesk_POST('remember_user') == 'JUSTUSER')
	{
		setcookie('hesk_username', "$user", strtotime('+1 year'));
		setcookie('hesk_p', '');
	}
	else
	{
		// Expire cookie if set otherwise
		setcookie('hesk_username', '');
		setcookie('hesk_p', '');
	}

    /* Close any old tickets here so Cron jobs aren't necessary */
	if ($hesk_settings['autoclose'])
    {
    	$revision = sprintf($hesklang['thist3'],hesk_date(),$hesklang['auto']);
		hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `status`='3', `history`=CONCAT(`history`,'".hesk_dbEscape($revision)."')  WHERE `status` = '2' AND `lastchange` <= '".hesk_dbEscape( date('Y-m-d H:i:s',time() - $hesk_settings['autoclose']*86400) )."'");
    }

	/* Redirect to the destination page */
	if ( hesk_isREQUEST('goto') )
	{
    	$url = hesk_REQUEST('goto');
	    $url = str_replace('&amp;','&',$url);

        /* goto parameter can be set to the local domain only */
        $myurl = parse_url($hesk_settings['hesk_url']);
        $goto  = parse_url($url);

        if (isset($myurl['host']) && isset($goto['host']))
        {
        	if ( str_replace('www.','',strtolower($myurl['host'])) != str_replace('www.','',strtolower($goto['host'])) )
            {
            	$url = 'admin_main.php';
            }
        }

	    header('Location: '.$url);
	}
	else
	{
	    header('Location: admin_main.php');
	}
	exit();
} // End do_login()


function print_login()
{
	global $hesk_settings, $hesklang;
    $hesk_settings['tmp_title'] = $hesk_settings['hesk_title'] . ' - ' .$hesklang['admin_login'];
	require_once(HESK_PATH . 'inc/header.inc.php');

	if ( hesk_isREQUEST('notice') )
	{
    	hesk_process_messages($hesklang['session_expired'],'NOREDIRECT');
	}

    if (!isset($_SESSION['a_iserror']))
    {
    	$_SESSION['a_iserror'] = array();
    }

	?>
    <div class="loginError"><?php
	            /* This will handle error, success and notice messages */
	            hesk_handle_messages();
	        ?></div>
    <div>
        <form class="form-signin form-horizontal" role="form" action="index.php" method="post" name="form1">
            
            <h2 class="form-signin-heading"><span <?php echo $display; ?>><span class="mega-octicon octicon-sign-in"></span>&nbsp;</span><?php echo $hesklang['admin_login']; ?></a></h2><br/>
            <?php if (in_array('pass',$_SESSION['a_iserror'])) { echo '<div class="form-group has-error">';} else { echo '<div class="form-group">';}?>
                <label for="user" class="col-sm-3 control-label"><?php echo $hesklang['username']; ?>:</label>
                <div class="col-sm-9">
                    <?php

				    if (defined('HESK_USER'))
				    {
					    $savedUser = HESK_USER;
				    }
				    else
				    {
					    $savedUser = hesk_htmlspecialchars( hesk_COOKIE('hesk_username') );
				    }

		            $is_1 = '';
		            $is_2 = '';
		            $is_3 = '';

				    $remember_user = hesk_POST('remember_user');

				    if ($hesk_settings['autologin'] && (isset($_COOKIE['hesk_p']) || $remember_user == 'AUTOLOGIN') )
		            {
		        	    $is_1 = 'checked="checked"';
		            }
		            elseif (isset($_COOKIE['hesk_username']) || $remember_user == 'JUSTUSER' )
		            {
		        	    $is_2 = 'checked="checked"';
		            }
		            else
		            {
		        	    $is_3 = 'checked="checked"';
		            }

				    if ($hesk_settings['list_users'])
				    {
				        echo '<select class="form-control" name="user" '.$cls.'>';
				        $res = hesk_dbQuery('SELECT * FROM `'.hesk_dbEscape($hesk_settings['db_pfix']).'users` ORDER BY `user` ASC');
				        while ($row=hesk_dbFetchAssoc($res))
				        {
				            $sel = (strtolower($savedUser) == strtolower($row['user'])) ? 'selected="selected"' : '';
				            echo '<option value="'.$row['user'].'" '.$sel.'>'.$row['user'].'</option>';
				        }
				        echo '</select>';

				    }
				    else
				    {
				        echo '<input class="form-control" type="text" name="user" size="35" placeholder="'.$hesklang['username'].'" value="'.$savedUser.'" />';
				    }
				    ?>
                </div>
            </div>
            <?php if (in_array('pass',$_SESSION['a_iserror'])) { echo '<div class="form-group has-error">';} else { echo '<div class="form-group">';}?>
                <label for="pass" class="col-sm-3 control-label"><?php echo $hesklang['pass']; ?>:</label>
                <div class="col-sm-9">
                    <input type="password" class="form-control" id="pass" name="pass" size="35" placeholder="<?php echo $hesklang['pass']; ?>"  />
                </div>
            </div>
		<?php
		if ($hesk_settings['secimg_use'] == 2)
	    {
		
				// SPAM prevention verified for this session
				if (isset($_SESSION['img_a_verified']))
				{
					echo '<img src="'.HESK_PATH.'img/success.png" width="16" height="16" border="0" alt="" style="vertical-align:text-bottom" /> '.$hesklang['vrfy'];
				}
				// Not verified yet, should we use Recaptcha?
				elseif ($hesk_settings['recaptcha_use'])
				{
					?>
					<script type="text/javascript">
					var RecaptchaOptions = {
					theme : '<?php echo ( isset($_SESSION['a_iserror']) && in_array('mysecnum',$_SESSION['a_iserror']) ) ? 'red' : 'white'; ?>',
					custom_translations : {
						visual_challenge : "<?php echo hesk_slashJS($hesklang['visual_challenge']); ?>",
						audio_challenge : "<?php echo hesk_slashJS($hesklang['audio_challenge']); ?>",
						refresh_btn : "<?php echo hesk_slashJS($hesklang['refresh_btn']); ?>",
						instructions_visual : "<?php echo hesk_slashJS($hesklang['instructions_visual']); ?>",
						instructions_context : "<?php echo hesk_slashJS($hesklang['instructions_context']); ?>",
						instructions_audio : "<?php echo hesk_slashJS($hesklang['instructions_audio']); ?>",
						help_btn : "<?php echo hesk_slashJS($hesklang['help_btn']); ?>",
						play_again : "<?php echo hesk_slashJS($hesklang['play_again']); ?>",
						cant_hear_this : "<?php echo hesk_slashJS($hesklang['cant_hear_this']); ?>",
						incorrect_try_again : "<?php echo hesk_slashJS($hesklang['incorrect_try_again']); ?>",
						image_alt_text : "<?php echo hesk_slashJS($hesklang['image_alt_text']); ?>",
					},
					};
					</script>
					<?php
					require_once(HESK_PATH . 'inc/recaptcha/recaptchalib.php');
					echo recaptcha_get_html($hesk_settings['recaptcha_public_key'], null, $hesk_settings['recaptcha_ssl']);
				}
				// At least use some basic PHP generated image (better than nothing)
				else
				{
					$cls = in_array('mysecnum',$_SESSION['a_iserror']) ? ' class="isError" ' : '';

					echo $hesklang['sec_enter'].'<br />&nbsp;<br /><img src="'.HESK_PATH.'print_sec_img.php?'.rand(10000,99999).'" width="150" height="40" alt="'.$hesklang['sec_img'].'" title="'.$hesklang['sec_img'].'" border="1" name="secimg" style="vertical-align:text-bottom" /> '.
					'<a href="javascript:void(0)" onclick="javascript:document.form1.secimg.src=\''.HESK_PATH.'print_sec_img.php?\'+ ( Math.floor((90000)*Math.random()) + 10000);"><img src="'.HESK_PATH.'img/reload.png" height="24" width="24" alt="'.$hesklang['reload'].'" title="'.$hesklang['reload'].'" border="0" style="vertical-align:text-bottom" /></a>'.
					'<br />&nbsp;<br /><input type="text" name="mysecnum" size="20" maxlength="5" '.$cls.' />';
				}
		} // End if $hesk_settings['secimg_use'] == 2

		if ($hesk_settings['autologin'])
		{
			?>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
                    <div class="radio">
				        <label><input type="radio" name="remember_user" value="AUTOLOGIN" <?php echo $is_1; ?> /> <?php echo $hesklang['autologin']; ?></label>
                    </div>
                    <div class="radio">
				        <label><input type="radio" name="remember_user" value="JUSTUSER" <?php echo $is_2; ?> /> <?php echo $hesklang['just_user']; ?></label>
                    </div>
                    <div class="radio">
				        <label><input type="radio" name="remember_user" value="NOTHANKS" <?php echo $is_3; ?> /> <?php echo $hesklang['nothx']; ?></label>
                    </div>
                </div>
			</div>
			<?php
		}
		else
		{
			?>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
				    <div class="checkbox">
                        <label><input type="checkbox" name="remember_user" value="JUSTUSER" <?php echo $is_2; ?> /> <?php echo $hesklang['remember_user']; ?></label>
                    </div>
			    </div>
            </div>
			<?php
		} // End if $hesk_settings['autologin']
		?>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <input type="submit" value="<?php echo $hesklang['click_login']; ?>" class="btn btn-default" />
                <input type="hidden" name="a" value="do_login" />
		        <?php
		        if ( hesk_isREQUEST('goto') && $url=hesk_REQUEST('goto') )
		        {
			        echo '<input type="hidden" name="goto" value="'.$url.'" />';
		        }
		        ?>
            </div>
        </div>

        </form>
    </div>

    <p>&nbsp;</p>

	<?php
	hesk_cleanSessionVars('a_iserror');

    require_once(HESK_PATH . 'inc/footer.inc.php');
    exit();
} // End print_login()


function logout() {
	global $hesk_settings, $hesklang;

    if ( ! hesk_token_check('GET', 0))
    {
		print_login();
        exit();
    }

    /* Delete from Who's online database */
	if ($hesk_settings['online'])
	{
    	require(HESK_PATH . 'inc/users_online.inc.php');
		hesk_setOffline($_SESSION['id']);
	}
    /* Destroy session and cookies */
	hesk_session_stop();

    /* If we're using the security image for admin login start a new session */
	if ($hesk_settings['secimg_use'] == 2)
    {
    	hesk_session_start();
    }

	/* Show success message and reset the cookie */
    hesk_process_messages($hesklang['logout_success'],'NOREDIRECT','SUCCESS');
    setcookie('hesk_p', '');

    /* Print the login form */
	print_login();
	exit();
} // End logout()

?>
