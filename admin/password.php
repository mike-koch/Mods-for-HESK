<?php
/**
 *
 * This file is part of HESK - PHP Help Desk Software.
 *
 * (c) Copyright Klemen Stirn. All rights reserved.
 * https://www.hesk.com
 *
 * For the full copyright and license agreement information visit
 * https://www.hesk.com/eula.php
 *
 */

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'LOGIN');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

// Connect to database and check for brute force attempts
hesk_load_database_functions();
hesk_dbConnect();

$modsForHesk_settings = mfh_getSettings();

// Is the password reset function enabled?
if (!$hesk_settings['reset_pass']) {
    die($hesklang['attempt']);
}

// Allow additional 5 attempts in case the user is already blocked
$hesk_settings['attempt_limit'] += 5;

// Start session
hesk_session_start();

if (!isset($_SESSION['a_iserror'])) {
    $_SESSION['a_iserror'] = array();
}

$hesk_error_buffer = array();

// If this is a POST method, check input
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify security image
    if ($hesk_settings['secimg_use']) {
        // Using ReCaptcha?
        if ($hesk_settings['recaptcha_use'] == 1) {
            require_once(HESK_PATH . 'inc/recaptcha/recaptchalib.php');

            $resp = recaptcha_check_answer($hesk_settings['recaptcha_private_key'],
                $_SERVER['REMOTE_ADDR'],
                hesk_POST('recaptcha_challenge_field', ''),
                hesk_POST('recaptcha_response_field', '')
            );

            if ($resp->is_valid) {
                //$_SESSION['img_a_verified']=true;
            } else {
                $hesk_error_buffer['mysecnum'] = $hesklang['recaptcha_error'];
            }
        } // Using ReCaptcha API v2?
        elseif ($hesk_settings['recaptcha_use'] == 2) {
            require(HESK_PATH . 'inc/recaptcha/recaptchalib_v2.php');

            $resp = null;
            $reCaptcha = new ReCaptcha($hesk_settings['recaptcha_private_key']);

            // Was there a reCAPTCHA response?
            if (isset($_POST["g-recaptcha-response"])) {
                $resp = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"], hesk_POST("g-recaptcha-response"));
            }

            if ($resp != null && $resp->success) {
                //$_SESSION['img_a_verified']=true;
            } else {
                $hesk_error_buffer['mysecnum'] = $hesklang['recaptcha_error'];
            }
        } // Using PHP generated image
        else {
            $mysecnum = intval(hesk_POST('mysecnum', 0));

            if (empty($mysecnum)) {
                $hesk_error_buffer['mysecnum'] = $hesklang['sec_miss'];
            } else {
                require(HESK_PATH . 'inc/secimg.inc.php');
                $sc = new PJ_SecurityImage($hesk_settings['secimg_sum']);
                if (isset($_SESSION['checksum']) && $sc->checkCode($mysecnum, $_SESSION['checksum'])) {
                    //$_SESSION['img_a_verified'] = true;
                } else {
                    $hesk_error_buffer['mysecnum'] = $hesklang['sec_wrng'];
                }
            }
        }
    }
    hesk_limitBfAttempts();

    // Get email
    $email = hesk_validateEmail(hesk_POST('email'), 'ERR', 0) or $hesk_error_buffer['email'] = $hesklang['enter_valid_email'];

    // Any errors?
    if (count($hesk_error_buffer) != 0) {
        $_SESSION['a_iserror'] = array_keys($hesk_error_buffer);

        $tmp = '';
        foreach ($hesk_error_buffer as $error) {
            $tmp .= "<li>$error</li>\n";
        }
        $hesk_error_buffer = $tmp;

        $hesk_error_buffer = $hesklang['pcer'] . '<br /><br /><ul>' . $hesk_error_buffer . '</ul>';
        hesk_process_messages($hesk_error_buffer, 'NOREDIRECT');
    } elseif (defined('HESK_DEMO')) {
        hesk_process_messages($hesklang['ddemo'], 'NOREDIRECT');
    } else {
        // Get user data from the database
        $res = hesk_dbQuery("SELECT `id`, `name`, `pass` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `email` LIKE '" . hesk_dbEscape($email) . "' LIMIT 1");
        if (hesk_dbNumRows($res) != 1) {
            hesk_process_messages($hesklang['noace'], 'NOREDIRECT');
        } else {
            $row = hesk_dbFetchAssoc($res);
            $hash = sha1(microtime() . $_SERVER['REMOTE_ADDR'] . mt_rand() . $row['id'] . $row['name'] . $row['pass']);

            // Insert the verification hash into the database
            hesk_dbQuery("INSERT INTO `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reset_password` (`user`, `hash`, `ip`) VALUES (" . intval($row['id']) . ", '{$hash}', '" . hesk_dbEscape($_SERVER['REMOTE_ADDR']) . "') ");

            // Prepare and send email
            require(HESK_PATH . 'inc/email_functions.inc.php');

            // Get the email message
            $msg = hesk_getEmailMessage('reset_password', array(), $modsForHesk_settings, 1, 0, 1);
            $htmlMsg = hesk_getHtmlMessage('reset_password', array(), $modsForHesk_settings, 1, 0, 1);

            // Replace message special tags
            $msg = str_replace('%%NAME%%', hesk_msgToPlain($row['name'], 1, 1), $msg);
            $msg = str_replace('%%SITE_URL%%', $hesk_settings['site_url'], $msg);
            $msg = str_replace('%%SITE_TITLE%%', $hesk_settings['site_title'], $msg);
            $msg = str_replace('%%PASSWORD_RESET%%', $hesk_settings['hesk_url'] . '/' . $hesk_settings['admin_dir'] . '/password.php?h=' . $hash, $msg);
            $htmlMsg = str_replace('%%NAME%%', hesk_msgToPlain($row['name'], 1, 1), $htmlMsg);
            $htmlMsg = str_replace('%%SITE_URL%%', $hesk_settings['site_url'], $htmlMsg);
            $htmlMsg = str_replace('%%SITE_TITLE%%', $hesk_settings['site_title'], $htmlMsg);
            $htmlMsg = str_replace('%%PASSWORD_RESET%%', $hesk_settings['hesk_url'] . '/' . $hesk_settings['admin_dir'] . '/password.php?h=' . $hash, $htmlMsg);

            // Send email
            hesk_mail($email, $hesklang['reset_password'], $msg, $htmlMsg, $modsForHesk_settings);

            // Show success
            hesk_process_messages($hesklang['pemls'], 'NOREDIRECT', 'SUCCESS');
        }
    }
} // If the "h" parameter is set verify it and reset the password
elseif (isset($_GET['h'])) {
    // Get the hash
    $hash = preg_replace('/[^a-zA-Z0-9]/', '', $_GET['h']);

    // Connect to database
    hesk_dbConnect();

    // Expire verification hashes older than 2 hours
    hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reset_password` WHERE `dt` < (NOW() - INTERVAL 2 HOUR)");

    // Verify the hash exists
    $res = hesk_dbQuery("SELECT `user`, `ip` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reset_password` WHERE `hash` = '{$hash}' LIMIT 1");
    if (hesk_dbNumRows($res) != 1) {
        // Not a valid hash
        hesk_limitBfAttempts();
        hesk_process_messages($hesklang['ehash'], 'NOREDIRECT');
    } else {
        // Get info from database
        $row = hesk_dbFetchAssoc($res);

        // Only allow resetting password from the same IP address that submitted password reset request
        if ($row['ip'] != $_SERVER['REMOTE_ADDR']) {
            hesk_limitBfAttempts();
            hesk_process_messages($hesklang['ehaip'], 'NOREDIRECT');
        } else {
            // Expire all verification hashes for this user
            hesk_dbQuery("DELETE FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "reset_password` WHERE `user`=" . intval($row['user']));

            // Load additional required functions
            require(HESK_PATH . 'inc/admin_functions.inc.php');

            // Get user details
            $res = hesk_dbQuery('SELECT * FROM `' . $hesk_settings['db_pfix'] . "users` WHERE `id`=" . intval($row['user']) . " LIMIT 1");
            $row = hesk_dbFetchAssoc($res);
            foreach ($row as $k => $v) {
                $_SESSION[$k] = $v;
            }

            // Set a tag that will be used to expire sessions after username or password change
            $_SESSION['session_verify'] = hesk_activeSessionCreateTag($_SESSION['user'], $_SESSION['pass']);

            // We don't need the password hash anymore
            unset($_SESSION['pass']);

            // Clean brute force attempts
            hesk_cleanBfAttempts();

            // Regenerate session ID (security)
            hesk_session_regenerate_id();

            // Get allowed categories
            if (empty($_SESSION['isadmin'])) {
                $_SESSION['categories'] = explode(',', $_SESSION['categories']);
            }

            // Redirect to the profile page
            hesk_process_messages($hesklang['resim'], 'profile.php', 'NOTICE');
            exit();

        } // End IP matches
    }
}

// Tell header to load reCaptcha API if needed
if ($hesk_settings['recaptcha_use'] == 2) {
    define('RECAPTCHA', 1);
}

$hesk_settings['tmp_title'] = $hesk_settings['hesk_title'] . ' - ' . $hesklang['passr'];
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');
?>
<div class="login-box">
    <div class="login-logo">
        <?php echo $hesk_settings['hesk_title']; ?>
    </div>
    <div class="login-box-body">
        <h4 class="login-box-msg">
            <?php echo $hesklang['passr']; ?>
        </h4>
        <form action="password.php" method="post" name="form1" class="form-horizontal" role="form">
            <?php
            /* This will handle error, success and notice messages */
            hesk_handle_messages();

            $has_error = '';
            if (in_array('email', $_SESSION['a_iserror'])) {
                $has_error = 'has-error';
            }

            $form_email = '';
            if (isset($email)) {
                $form_email = stripslashes(hesk_input($email));
            }
            ?>
            <div class="form-group <?php echo $has_error; ?>">
                <label for="email" class="col-sm-3 control-label">
                    <?php echo $hesklang['email']; ?>
                </label>
                <div class="col-sm-9">
                    <input type="text" name="email" size="35" value="<?php echo $form_email; ?>"
                           class="form-control" placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>">
                </div>
            </div>
            <?php
            if ($hesk_settings['secimg_use']) {
                ?>
                <div class="form-group">
                    <div class="col-sm-11 col-sm-offset-1">
                        <?php
                        // Should we use Recaptcha?
                        if ($hesk_settings['recaptcha_use'] == 1) {
                            ?>
                            <script type="text/javascript">
                                var RecaptchaOptions = {
                                    theme: '<?php echo ( isset($_SESSION['a_iserror']) && in_array('mysecnum',$_SESSION['a_iserror']) ) ? 'red' : 'white'; ?>',
                                    custom_translations: {
                                        visual_challenge: "<?php echo hesk_slashJS($hesklang['visual_challenge']); ?>",
                                        audio_challenge: "<?php echo hesk_slashJS($hesklang['audio_challenge']); ?>",
                                        refresh_btn: "<?php echo hesk_slashJS($hesklang['refresh_btn']); ?>",
                                        instructions_visual: "<?php echo hesk_slashJS($hesklang['instructions_visual']); ?>",
                                        instructions_context: "<?php echo hesk_slashJS($hesklang['instructions_context']); ?>",
                                        instructions_audio: "<?php echo hesk_slashJS($hesklang['instructions_audio']); ?>",
                                        help_btn: "<?php echo hesk_slashJS($hesklang['help_btn']); ?>",
                                        play_again: "<?php echo hesk_slashJS($hesklang['play_again']); ?>",
                                        cant_hear_this: "<?php echo hesk_slashJS($hesklang['cant_hear_this']); ?>",
                                        incorrect_try_again: "<?php echo hesk_slashJS($hesklang['incorrect_try_again']); ?>",
                                        image_alt_text: "<?php echo hesk_slashJS($hesklang['image_alt_text']); ?>"
                                    }
                                };
                            </script>
                        <?php
                        require_once(HESK_PATH . 'inc/recaptcha/recaptchalib.php');
                        echo recaptcha_get_html($hesk_settings['recaptcha_public_key'], null, true);
                        }
                        // Use reCaptcha API v2?
                        elseif ($hesk_settings['recaptcha_use'] == 2)
                        {
                        ?>
                            <div class="g-recaptcha"
                                 data-sitekey="<?php echo $hesk_settings['recaptcha_public_key']; ?>"></div>
                            <?php
                        }
                        // At least use some basic PHP generated image (better than nothing)
                        else {
                            $cls = in_array('mysecnum', $_SESSION['a_iserror']) ? ' class="isError" ' : '';

                            echo $hesklang['sec_enter'] . '<br />&nbsp;<br /><img src="' . HESK_PATH . 'print_sec_img.php?' . rand(10000, 99999) . '" width="150" height="40" alt="' . $hesklang['sec_img'] . '" title="' . $hesklang['sec_img'] . '" border="1" name="secimg" style="vertical-align:text-bottom" /> ' .
                                '<a href="javascript:void(0)" onclick="javascript:document.form1.secimg.src=\'' . HESK_PATH . 'print_sec_img.php?\'+ ( Math.floor((90000)*Math.random()) + 10000);"><img src="' . HESK_PATH . 'img/reload.png" height="24" width="24" alt="' . $hesklang['reload'] . '" title="' . $hesklang['reload'] . '" border="0" style="vertical-align:text-bottom" /></a>' .
                                '<br />&nbsp;<br /><input type="text" name="mysecnum" size="20" maxlength="5" ' . $cls . ' />';
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3">
                    <input type="submit" value="<?php echo $hesklang['passs']; ?>" class="btn btn-default">
                </div>
            </div>
        </form>
    </div>
</div>
<?php
// Clean session errors
hesk_cleanSessionVars('a_iserror');
hesk_cleanSessionVars('img_a_verified');
?>
