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

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {
    die('Invalid attempt');
}


function hesk_profile_tab($session_array = 'new', $is_profile_page = true, $action = 'profile_page')
{
    global $hesk_settings, $hesklang, $can_reply_tickets, $can_view_tickets, $can_view_unassigned;
    ?>
    <div role="tabpanel" class="nav-tabs-custom">

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#profile-info" aria-controls="profile-info" role="tab"
                                                      data-toggle="tab"><?php echo $hesklang['pinfo']; ?></a></li>
            <?php
            if (!$is_profile_page) {
                ?>
                <li role="presentation"><a href="#permissions" aria-controls="permissions" role="tab"
                                           data-toggle="tab"><?php echo $hesklang['permissions']; ?></a></li>
                <?php
            }
            ?>
            <li role="presentation"><a href="#signature" aria-controls="signature" role="tab"
                                       data-toggle="tab"><?php echo $hesklang['sig']; ?></a></li>
            <li role="presentation"><a href="#preferences" aria-controls="preferences" role="tab"
                                       data-toggle="tab"><?php echo $hesklang['pref']; ?></a></li>
            <li role="presentation"><a href="#notifications" aria-controls="notifications" role="tab"
                                       data-toggle="tab"><?php echo $hesklang['notn']; ?></a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content summaryList tabPadding">
            <div role="tabpanel" class="tab-pane fade in active" id="profile-info">
                <div class="form-group">
                    <label for="name" class="col-md-3 control-label"><?php echo $hesklang['real_name']; ?>
                        <span class="important">*</span></label>

                    <div class="col-md-9">
                        <input type="text" class="form-control" name="name" size="40" maxlength="50"
                               value="<?php echo $_SESSION[$session_array]['name']; ?>"
                               placeholder="<?php echo htmlspecialchars($hesklang['real_name']); ?>"
                               data-error="<?php echo htmlspecialchars($hesklang['enter_real_name']); ?>"
                               required>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email" class="col-md-3 control-label"><?php echo $hesklang['email']; ?>
                        <span class="important">*</span></label>

                    <div class="col-md-9">
                        <input type="email" class="form-control" name="email" size="40" maxlength="255"
                               placeholder="<?php echo htmlspecialchars($hesklang['email']); ?>"
                               value="<?php echo $_SESSION[$session_array]['email']; ?>"
                               data-error="<?php echo htmlspecialchars($hesklang['enter_valid_email']); ?>"
                               required>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <?php
                if (!$is_profile_page || $_SESSION['isadmin']) {
                    ?>
                    <div class="form-group">
                        <label for="user" class="col-md-3 control-label"><?php echo $hesklang['username']; ?>
                            <span class="important">*</span></label>

                        <div class="col-md-9">
                            <input type="text" class="form-control" name="user" size="40" maxlength="20"
                                   autocomplete="off"
                                   value="<?php echo $_SESSION[$session_array]['user']; ?>"
                                   placeholder="<?php echo htmlspecialchars($hesklang['username']); ?>"
                                   data-error="<?php echo htmlspecialchars($hesklang['enter_username']); ?>"
                                   required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <?php
                }
                $passwordValidator = 'data-error="'.htmlspecialchars($hesklang['password_not_valid']).'" data-minlength="5" required';
                $confirmPasswordValidator = 'data-error="'.htmlspecialchars($hesklang['passwords_not_same']).'" data-match="#newpass" required';
                $passwordRequiredSpan = '';
                if ($action != 'create_user') {
                    $passwordValidator = '';
                    $confirmPasswordValidator = '';
                    $passwordRequiredSpan = 'display:none';
                }
                ?>
                <div class="form-group">
                    <label for="pass"
                           class="col-md-3 control-label"><?php echo $is_profile_page ? $hesklang['new_pass'] : $hesklang['pass']; ?>
                        <span class="important" style="<?php echo $passwordRequiredSpan; ?>">*</span></label>

                    <div class="col-md-9">
                        <input type="password" class="form-control" id="newpass" name="newpass" autocomplete="off" size="40"
                               placeholder="<?php echo htmlspecialchars($hesklang['pass']); ?>"
                               value="<?php echo isset($_SESSION[$session_array]['cleanpass']) ? $_SESSION[$session_array]['cleanpass'] : ''; ?>"
                               onkeyup="javascript:hesk_checkPassword(this.value)" <?php echo $passwordValidator; ?>>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirmPass" class="col-md-3 control-label"><?php echo $hesklang['confirm_pass']; ?>
                        <span class="important" style="<?php echo $passwordRequiredSpan; ?>">*</span></label>

                    <div class="col-md-9">
                        <input type="password" name="newpass2" class="form-control" autocomplete="off"
                               placeholder="<?php echo htmlspecialchars($hesklang['confirm_pass']); ?>" size="40"
                               value="<?php echo isset($_SESSION[$session_array]['cleanpass']) ? $_SESSION[$session_array]['cleanpass'] : ''; ?>"
                               <?php echo $confirmPasswordValidator; ?>>
                        <div class="help-block with-errors"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pwStrength" class="col-md-3 control-label"><?php echo $hesklang['pwdst']; ?></label>

                    <div class="col-md-9">
                        <div class="progress">
                            <div id="progressBar" class="progress-bar progress-bar-danger" role="progressbar"
                                 aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (!$is_profile_page) {
                    ?>
                    <div class="blankSpace"></div>
                    <div class="form-group">
                        <div class="col-md-9 col-md-offset-3">
                            <?php
                            if ($hesk_settings['autoassign']) {
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="autoassign"
                                                  value="Y" <?php if (!isset($_SESSION[$session_array]['autoassign']) || $_SESSION[$session_array]['autoassign'] == 1) {
                                            echo 'checked="checked"';
                                        } ?> /> <?php echo $hesklang['user_aa']; ?></label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
            if (!$is_profile_page) {
                ?>
                <div role="tabpanel" class="tab-pane fade" id="permissions">
                    <div class="form-group">
                        <label for="administrator"
                               class="col-md-3 control-label"><?php echo $hesklang['permission_template_colon']; ?></label>

                        <div class="col-md-9">
                            <?php
                            // Get list of permission templates. If current user is not admin, exclude permission tpl 1
                            $excludeSql = $_SESSION['isadmin'] ? '' : " WHERE `heskprivileges` <> 'ALL'";
                            $res = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates`" . $excludeSql);
                            $templates = array();
                            echo '<select name="template" id="permission-tpl" class="form-control" onchange="updateCheckboxes()">';
                            while ($row = hesk_dbFetchAssoc($res)) {
                                array_push($templates, $row);
                                $selected = $_SESSION[$session_array]['permission_template'] == $row['id'] ? 'selected' : '';
                                echo '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
                            }
                            $selected = $_SESSION[$session_array]['permission_template'] == '-1' ? 'selected' : '';
                            echo '<option value="-1" ' . $selected . '>' . htmlspecialchars($hesklang['custom']) . '</option>';
                            echo '</select>';
                            outputCheckboxJavascript();
                            ?>
                        </div>
                    </div>
                    <div id="options">
                        <div class="form-group">
                            <label for="categories[]"
                                   class="col-md-3 control-label"><?php echo $hesklang['allowed_cat']; ?> <span
                                    class="important">*</span></label>

                            <div class="col-md-9">
                                <?php
                                foreach ($hesk_settings['categories'] as $catid => $catname) {
                                    echo '<div class="checkbox"><label><input id="cat-' . $catid . '" class="cat-checkbox"
                                    type="checkbox" name="categories[]" onchange="setTemplateToCustom()" value="' . $catid . '" ';
                                    if (in_array($catid, $_SESSION[$session_array]['categories'])) {
                                        echo ' checked="checked" ';
                                    }
                                    echo ' />' . $catname . '</label></div> ';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="features[]"
                                   class="col-md-3 control-label"><?php echo $hesklang['allow_feat']; ?> <span
                                    class="important">*</span></label>

                            <div class="col-md-9">
                                <?php
                                foreach ($hesk_settings['features'] as $k) {
                                    echo '<div class="checkbox"><label><input id="feat-' . $k . '" class="feat-checkbox"
                                    type="checkbox" name="features[]" onchange="setTemplateToCustom()" value="' . $k . '" ';
                                    if (in_array($k, $_SESSION[$session_array]['features'])) {
                                        echo ' checked="checked" ';
                                    }
                                    echo ' />' . $hesklang[$k] . '</label></div> ';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
            <div role="tabpanel" class="tab-pane fade" id="signature">
                <div class="form-group">
                    <label for="signature" class="col-md-3 control-label"><?php echo $hesklang['signature_max']; ?></label>
                    <div class="col-md-9">
                        <textarea class="form-control" name="signature" rows="6"
                                  placeholder="<?php echo htmlspecialchars($hesklang['sig']); ?>"
                                  cols="40"><?php echo $_SESSION[$session_array]['signature']; ?></textarea>
                        <?php echo $hesklang['sign_extra']; ?>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="preferences">
                <?php
                if (!$is_profile_page || $can_reply_tickets) {
                    ?>
                    <div class="form-group">
                        <label for="afterreply" class="col-sm-3 control-label"><?php echo $hesklang['aftrep']; ?></label>

                        <div class="col-sm-9">
                            <div class="radio">
                                <label><input type="radio" name="afterreply"
                                              value="0" <?php if (!$_SESSION[$session_array]['afterreply']) {
                                        echo 'checked="checked"';
                                    } ?>/> <?php echo $hesklang['showtic']; ?></label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="afterreply"
                                              value="1" <?php if ($_SESSION[$session_array]['afterreply'] == 1) {
                                        echo 'checked="checked"';
                                    } ?>/> <?php echo $hesklang['gomain']; ?></label>
                            </div>
                            <div class="radio">
                                <label><input type="radio" name="afterreply"
                                              value="2" <?php if ($_SESSION[$session_array]['afterreply'] == 2) {
                                        echo 'checked="checked"';
                                    } ?>/> <?php echo $hesklang['shownext']; ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label"><?php echo $hesklang['defaults']; ?>:</label>

                        <div class="col-sm-9">
                            <?php
                            if ($hesk_settings['time_worked']) {
                                ?>
                                <div class="checkbox">
                                    <label><input type="checkbox" name="autostart"
                                                  value="1" <?php if (!empty($_SESSION[$session_array]['autostart'])) {
                                            echo 'checked="checked"';
                                        } ?> /> <?php echo $hesklang['autoss']; ?></label>
                                </div>
                                <?php
                            }

                            if (empty($_SESSION[$session_array]['autoreload'])) {
                                $reload_time = 30;
                                $sec = 'selected';
                                $min = '';
                            } else {
                                $reload_time = intval($_SESSION[$session_array]['autoreload']);

                                if ($reload_time >= 60 && $reload_time % 60 == 0) {
                                    $reload_time = $reload_time / 60;
                                    $sec = '';
                                    $min = 'selected';
                                } else {
                                    $sec = 'selected';
                                    $min = '';
                                }
                            }
                            ?>
                            <div class="checkbox form-inline">
                                <label><input type="checkbox" name="autoreload" value="1" <?php if (!empty($_SESSION[$session_array]['autoreload'])) {echo 'checked="checked"';}?> /> <?php echo $hesklang['arpp']; ?></label>
                                <input type="text" class="form-control" name="reload_time" value="<?php echo $reload_time; ?>" size="5" maxlength="5" onkeyup="this.value=this.value.replace(/[^\d]+/,'')" />
                                <select name="secmin" class="form-control">
                                    <option value="sec" <?php echo $sec; ?>><?php echo $hesklang['seconds']; ?></option>
                                    <option value="min" <?php echo $min; ?>><?php echo $hesklang['minutes']; ?></option>
                                </select>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="notify_customer_new"
                                              value="1" <?php if (!empty($_SESSION[$session_array]['notify_customer_new'])) {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['pncn']; ?></label><br/>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="notify_customer_reply"
                                              value="1" <?php if (!empty($_SESSION[$session_array]['notify_customer_reply'])) {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['pncr']; ?></label><br/>
                            </div>
                            <div class="checkbox">
                                <label><input type="checkbox" name="show_suggested"
                                              value="1" <?php if (!empty($_SESSION[$session_array]['show_suggested'])) {
                                        echo 'checked="checked"';
                                    } ?> /> <?php echo $hesklang['pssy']; ?></label><br/>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="form-group">
                    <label for="default-calendar-view" class="col-sm-3 control-label">
                        <?php echo $hesklang['default_view']; ?>
                    </label>
                    <div class="col-sm-9">
                        <select class="form-control" name="default-calendar-view">
                            <option value="0" <?php if ($_SESSION[$session_array]['default_calendar_view'] == 0) { echo 'selected'; } ?>>
                                <?php echo $hesklang['month']; ?>
                            </option>
                            <option value="1" <?php if ($_SESSION[$session_array]['default_calendar_view'] == 1) { echo 'selected'; } ?>>
                                <?php echo $hesklang['week']; ?>
                            </option>
                            <option value="2" <?php if ($_SESSION[$session_array]['default_calendar_view'] == 2) { echo 'selected'; } ?>>
                                <?php echo $hesklang['calendar_day']; ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="notifications">
                <?php $disabledText =
                    (!$_SESSION[$session_array]['isadmin'] && isset($_SESSION[$session_array]['heskprivileges']) && strpos($_SESSION[$session_array]['heskprivileges'], 'can_change_notification_settings') === false)
                        ? 'disabled' : '';
                if (!$is_profile_page) {
                    $disabledText = '';
                }
                if ($disabledText == 'disabled') { ?>
                    <div class="alert alert-info"><?php echo $hesklang['notifications_disabled_info']; ?></div>
                <?php }
                ?>
                <div class="form-group">
                    <?php
                    if (!$is_profile_page || $can_view_tickets) {
                        if (!$is_profile_page || $can_view_unassigned) {
                            ?>
                            <div class="col-md-9 col-md-offset-3">
                                <div class="checkbox"><label><input type="checkbox" name="notify_new_unassigned"
                                                                    value="1" <?php if (!empty($_SESSION[$session_array]['notify_new_unassigned'])) {
                                            echo 'checked="checked"';
                                        }
                                        echo ' ' . $disabledText ?> /> <?php echo $hesklang['nwts']; ?> <?php echo $hesklang['unas']; ?>
                                    </label></div>
                            </div>

                            <?php
                            if ($disabledText == 'disabled') { ?>
                                <input type="hidden" name="notify_new_unassigned"
                                       value="<?php echo !empty($_SESSION[$session_array]['notify_new_unassigned']) ? '1' : '0'; ?>">
                            <?php }
                        } else {
                            ?>
                            <input type="hidden" name="notify_new_unassigned" value="0"/>
                            <?php
                        }
                        ?>
                        <div class="col-md-9 col-md-offset-3">
                            <div class="checkbox"><label><input type="checkbox" name="notify_new_my"
                                                                value="1" <?php if (!empty($_SESSION[$session_array]['notify_new_my'])) {
                                        echo 'checked="checked"';
                                    }
                                    echo ' ' . $disabledText ?> /> <?php echo $hesklang['nwts']; ?> <?php echo $hesklang['s_my']; ?>
                                </label></div>
                        </div>
                        <?php
                        if ($disabledText == 'disabled') { ?>
                            <input type="hidden" name="notify_new_my"
                                   value="<?php echo !empty($_SESSION[$session_array]['notify_new_my']) ? '1' : '0'; ?>">
                        <?php }

                        if (!$is_profile_page || $can_view_unassigned) {
                            ?>
                            <div class="col-md-9 col-md-offset-3">
                                <div class="checkbox"><label><input type="checkbox" name="notify_reply_unassigned"
                                                                    value="1" <?php if (!empty($_SESSION[$session_array]['notify_reply_unassigned'])) {
                                            echo 'checked="checked"';
                                        }
                                        echo ' ' . $disabledText ?> /> <?php echo $hesklang['ncrt']; ?> <?php echo $hesklang['unas']; ?>
                                    </label></div>
                            </div>
                            <?php
                            if ($disabledText == 'disabled') { ?>
                                <input type="hidden" name="notify_reply_unassigned"
                                       value="<?php echo !empty($_SESSION[$session_array]['notify_reply_unassigned']) ? '1' : '0'; ?>">
                            <?php }
                        } else {
                            ?>
                            <input type="hidden" name="notify_reply_unassigned" value="0"/>
                            <?php
                        }
                        ?>
                        <div class="col-md-9 col-md-offset-3">
                            <div class="checkbox"><label><input type="checkbox" name="notify_reply_my"
                                                                value="1" <?php if (!empty($_SESSION[$session_array]['notify_reply_my'])) {
                                        echo 'checked="checked"';
                                    }
                                    echo ' ' . $disabledText ?> /> <?php echo $hesklang['ncrt']; ?> <?php echo $hesklang['s_my']; ?>
                                </label></div>
                            <?php if ($disabledText == 'disabled') { ?>
                            <input type="hidden" name="notify_reply_my"
                                   value="<?php echo !empty($_SESSION[$session_array]['notify_reply_my']) ? '1' : '0'; ?>">
                            <?php } ?>
                        </div>
                        <div class="col-md-9 col-md-offset-3">
                            <div class="checkbox"><label><input type="checkbox" name="notify_assigned"
                                                                value="1" <?php if (!empty($_SESSION[$session_array]['notify_assigned'])) {
                                        echo 'checked="checked"';
                                    }
                                    echo ' ' . $disabledText ?> /> <?php echo $hesklang['ntam']; ?></label></div>
                            <?php if ($disabledText == 'disabled') { ?>
                                <input type="hidden" name="notify_assigned"
                                       value="<?php echo !empty($_SESSION[$session_array]['notify_assigned']) ? '1' : '0'; ?>">
                            <?php } ?>
                        </div>
                        <div class="col-md-9 col-md-offset-3">
                            <div class="checkbox"><label><input type="checkbox" name="notify_note"
                                                                value="1" <?php if (!empty($_SESSION[$session_array]['notify_note'])) {
                                        echo 'checked="checked"';
                                    }
                                    echo ' ' . $disabledText ?> /> <?php echo $hesklang['ntnote']; ?></label></div>
                            <?php if ($disabledText == 'disabled') { ?>
                                <input type="hidden" name="notify_note"
                                       value="<?php echo !empty($_SESSION[$session_array]['notify_note']) ? '1' : '0'; ?>">
                            <?php } ?>
                        </div>
                        <div class="col-md-9 col-md-offset-3">
                            <div class="checkbox"><label><input type="checkbox" name="notify_pm"
                                                                value="1" <?php if (!empty($_SESSION[$session_array]['notify_pm'])) {
                                        echo 'checked="checked"';
                                    }
                                    echo ' ' . $disabledText ?> /> <?php echo $hesklang['npms']; ?></label></div>
                            <?php if ($disabledText == 'disabled') { ?>
                                <input type="hidden" name="notify_pm"
                                       value="<?php echo !empty($_SESSION[$session_array]['notify_pm']) ? '1' : '0'; ?>">
                            <?php } ?>
                        </div>
                        <?php
                        if ($_SESSION['isadmin']) { ?>
                            <div class="col-md-9 col-md-offset-3">
                                <div class="checkbox"><label><input type="checkbox" name="notify_note_unassigned"
                                                                    value="1" <?php if (!empty($_SESSION[$session_array]['notify_note_unassigned'])) {
                                            echo 'checked="checked"';
                                        } ?>> <?php echo $hesklang['notify_note_unassigned']; ?></label></div>
                            </div>
                            <?php if ($disabledText == 'disabled') { ?>
                                <input type="hidden" name="notify_note_unassigned"
                                       value="<?php echo !empty($_SESSION[$session_array]['notify_note_unassigned']) ? '1' : '0'; ?>">
                            <?php }
                        } ?>
                        <div class="col-md-9 col-md-offset-3">
                            <div class="checkbox"><label><input type="checkbox" name="notify_overdue_unassigned"
                                                                value="1" <?php if (!empty($_SESSION[$session_array]['notify_overdue_unassigned'])) {
                                        echo 'checked="checked"';
                                    }
                                    echo ' ' . $disabledText ?> /> <?php echo $hesklang['notify_overdue_unassigned']; ?></label></div>
                        </div>
                        <?php if ($disabledText == 'disabled') { ?>
                            <input type="hidden" name="notify_overdue_unassigned"
                                   value="<?php echo !empty($_SESSION[$session_array]['notify_overdue_unassigned']) ? '1' : '0'; ?>">
                        <?php }
                    }
                    ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-9 col-md-offset-3">
                    <?php
                    if ($action == 'profile_page')
                    { ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>">
                        <input type="submit" class="btn btn-default" value="<?php echo $hesklang['update_profile']; ?>">
                    <?php
                    } elseif ($action == 'create_user')
                    { ?>
                        <input type="hidden" name="a" value="new" />
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                        <div class="btn-group">
                            <input type="submit" value="<?php echo $hesklang['create_user']; ?>" class="btn btn-default">
                            <a href="manage_users.php?a=reset_form" class="btn btn-danger"><?php echo $hesklang['refi']; ?></a>
                        </div>
                    <?php
                    } elseif ($action == 'edit_user')
                    { ?>
                        <input type="hidden" name="a" value="save" />
                        <input type="hidden" name="userid" value="<?php echo intval( hesk_GET('id') ); ?>" />
                        <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
                        <input type="hidden" name="active" value="<?php echo $_SESSION[$session_array]['active']; ?>">
                        <div class="btn-group">
                            <input class="btn btn-default" type="submit" value="<?php echo $hesklang['save_changes']; ?>">
                            <a class="btn btn-danger" href="manage_users.php"><?php echo $hesklang['dich']; ?></a>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script language="Javascript" type="text/javascript"><!--
        hesk_checkPassword(document.form1.newpass.value);
        //-->
    </script>

    <?php
} // END hesk_profile_tab()

function outputCheckboxJavascript()
{
    global $hesk_settings, $hesklang;

    // Get categories and features for each template
    $res = hesk_dbQuery("SELECT `categories`, `heskprivileges`, `id` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "permission_templates`");
    $templates = array();
    $finalCatMarkup = "var categories = [];\n";
    $finalFeatMarkup = "var features = [];\n";
    while ($row = hesk_dbFetchAssoc($res)) {
        $templates[$row['id']]['features'] = explode(',', $row['heskprivileges']);
        $templates[$row['id']]['categories'] = explode(',', $row['categories']);
        $jsFeatureArray = array();
        $jsCategoryArray = array();
        foreach ($templates[$row['id']]['features'] as $array) {
            $goodText = "'" . $array . "'";
            array_push($jsFeatureArray, $goodText);
        }
        foreach ($templates[$row['id']]['categories'] as $array) {
            $goodText = "'" . $array . "'";
            array_push($jsCategoryArray, $goodText);
        }
        $builtFeatureArray = implode(',', $jsFeatureArray);
        $builtCategoryArray = implode(',', $jsCategoryArray);
        $finalCatMarkup .= "categories[" . $row['id'] . "] = [" . $builtCategoryArray . "];\n";
        $finalFeatMarkup .= "features[" . $row['id'] . "] = [" . $builtFeatureArray . "];\n";
    }

    echo "<script>
    " . $finalCatMarkup . "
    " . $finalFeatMarkup . "
    function updateCheckboxes() {
        // Get the value from the dropdown
        var dropdownValue = $('#permission-tpl').val();
        updateCategoriesAndFeatures(dropdownValue);
    }
    function updateCategoriesAndFeatures(dropdownValue) {
        // Get the category array
        var newCats = categories[dropdownValue];
        var newFeats = features[dropdownValue];
        // Uncheck everything
        $('.cat-checkbox').prop('checked', false);
        $('.feat-checkbox').prop('checked', false);
        newCats.forEach(function(entry) {
            if (entry == 'ALL') {
                $('.cat-checkbox').prop('checked', true);
            } else {
                $('#cat-'+entry).prop('checked', true);
            }
        });
        newFeats.forEach(function(entry) {
            if (entry == 'ALL') {
                $('.feat-checkbox').prop('checked', true);
            } else {
                $('#feat-'+entry).prop('checked', true);
            }
        });
    }
    function setTemplateToCustom() {
        $('#permission-tpl').val('-1');
    }
    </script>";
}