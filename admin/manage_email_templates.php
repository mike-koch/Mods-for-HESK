<?php

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_EMAIL_TEMPLATES');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

hesk_checkPermission('can_man_email_tpl');

define('WYSIWYG', 1);

// Are we performing an action?
$showEditPanel = false;
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'edit') {
        $showEditPanel = true;
    }
}

// Are we saving?
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'save') {
        save();
    }
}
/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

if ($modsForHesk_settings['html_emails']) {
    echo '<script type="text/javascript">
        tinyMCE.init({
                        mode : "textareas",
                        editor_selector : "htmlEditor",
                        elements : "content",
                        theme : "advanced",
                        convert_urls : false,
                        gecko_spellcheck: true,

                        theme_advanced_buttons1 : "cut,copy,paste,|,undo,redo,|,formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
                        theme_advanced_buttons2 : "sub,sup,|,charmap,|,bullist,numlist,|,outdent,indent,insertdate,inserttime,preview,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,link,unlink,anchor,image,cleanup,code",
                        theme_advanced_buttons3 : "",

                        theme_advanced_toolbar_location : "top",
                        theme_advanced_toolbar_align : "left",
                        theme_advanced_statusbar_location : "bottom",
                        theme_advanced_resizing : true
                    });
                </script>';
}

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
    <section class="content">
    <div class="box">
        <div class="box-body">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs" role="tablist">
                    <?php
                    // Show a link to banned_emails.php if user has permission
                    if (hesk_checkPermission('can_ban_emails', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['banemail'] . '" href="banned_emails.php">' . $hesklang['banemail'] . '</a>
            </li>
            ';
                    }
                    if (hesk_checkPermission('can_ban_ips', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['banip'] . '" href="banned_ips.php">' . $hesklang['banip'] . '</a>
            </li>';
                    }
                    // Show a link to status_message.php if user has permission to do so
                    if (hesk_checkPermission('can_service_msg', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['sm_title'] . '" href="service_messages.php">' . $hesklang['sm_title'] . '</a>
            </li>';
                    }
                    ?>
                    <li role="presentation" class="active">
                        <a href="#"><?php echo $hesklang['email_templates']; ?> <i
                                class="fa fa-question-circle settingsquestionmark" data-toggle="popover"
                                title="<?php echo $hesklang['email_templates']; ?>"
                                data-content="<?php echo $hesklang['email_templates_intro']; ?>"></i></a>
                    </li>
                    <?php
                    if (hesk_checkPermission('can_man_ticket_statuses', 0)) {
                        echo '
            <li role="presentation">
                <a title="' . $hesklang['statuses'] . '" href="manage_statuses.php">' . $hesklang['statuses'] . '</a>
            </li>
            ';
                    }

                    if (hesk_checkPermission('can_man_settings', 0)) {
                        echo '
                    <li role="presentation">
						<a title="' . $hesklang['tab_4'] . '" href="custom_fields.php">' .
                            $hesklang['tab_4']
                            . '</a>
					</li>
                        ';
                    }
                    ?>
                </ul>
                <div class="tab-content summaryList tabPadding">
                    <?php if ($showEditPanel): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4>
                                            <?php
                                            $isHtml = ($_GET['html'] == 'true');
                                            $class = 'plaintext-editor';
                                            if ($isHtml) {
                                                $class = 'htmlEditor';
                                                echo sprintf($hesklang['editing_html_template'], $_GET['template']);
                                            } else {
                                                echo sprintf($hesklang['editing_plain_text_template'], $_GET['template']);
                                            } ?>
                                        </h4>
                                    </div>
                                    <div class="panel-body">
                                        <?php
                                        $fileContent = '';
                                        if ($isHtml) {
                                            $fileContent = file_get_contents(HESK_PATH . 'language/' . urldecode($_GET['language']) . '/emails/html/' . $_GET['template']);
                                        } else {
                                            $fileContent = file_get_contents(HESK_PATH . 'language/' . urldecode($_GET['language']) . '/emails/' . $_GET['template']);
                                        }
                                        if ($fileContent === false) {
                                            //throw error
                                        }
                                        ?>
                                        <a href="#" id="showSpecialTags"
                                           onclick="toggleContainers(['specialTags'],['showSpecialTags'])">
                                            <?php echo $hesklang['show_special_tags']; ?>
                                        </a>

                                        <div id="specialTags" style="display: none">
                                            <a href="#" onclick="toggleContainers(['showSpecialTags'],['specialTags'])">
                                                <?php echo $hesklang['hide_special_tags']; ?>
                                            </a>
                                            <table class="table table-striped table-responsive table-condensed">
                                                <thead>
                                                <tr>
                                                    <th><?php echo $hesklang['special_tag']; ?></th>
                                                    <th><?php echo $hesklang['description'] ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php
                                                $tags = getSpecialTagMap();
                                                foreach ($tags as $tag => $text): ?>
                                                    <tr>
                                                        <td><?php echo $tag; ?></td>
                                                        <td><?php echo $text; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <form action="manage_email_templates.php" method="post">
                                    <textarea name="text" rows="15"
                                              class="form-control <?php echo $class; ?>"><?php echo $fileContent; ?></textarea>
                                            <input type="hidden" name="action" value="save">
                                            <input type="hidden" name="template"
                                                   value="<?php echo htmlspecialchars($_GET['template']); ?>">
                                            <input type="hidden" name="language"
                                                   value="<?php echo htmlspecialchars($_GET['language']); ?>">
                                            <input type="hidden" name="html" value="<?php echo $isHtml; ?>">
                                            <br>
                                            <?php
                                            $fileWritable = false;
                                            if ($isHtml) {
                                                $fileWritable = is_writable(HESK_PATH . 'language/' . $_GET['language'] . '/emails/html/' . $_GET['template']);
                                            } else {
                                                $fileWritable = is_writable(HESK_PATH . 'language/' . $_GET['language'] . '/emails/' . $_GET['template']);
                                            }

                                            if (!$fileWritable) {
                                                echo '<div class="alert alert-danger">
                                    <p>' . sprintf($hesklang['email_template_directory_not_writable'], $_GET['template']) . '</p>
                                    </div>';
                                            } else {
                                                echo '<input type="submit" class="btn btn-default" value="' . $hesklang['save'] . '">';
                                            }
                                            ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            /* This will handle error, success and notice messages */
                            hesk_handle_messages();

                            // Output list of templates, and provide links to edit the plaintext and HTML versions for each language
                            // First get list of languages
                            $languages = array();
                            foreach ($hesk_settings['languages'] as $key => $value) {
                                $languages[$key] = $hesk_settings['languages'][$key]['folder'];
                            }

                            // Get all files, but don't worry about index.htm, items beginning with '.', or the html folder
                            // We'll also assume the template file exists in all language folders and in the html folder
                            reset($languages);
                            $firstKey = key($languages);
                            $firstDirectory = HESK_PATH . 'language/' . $languages[$firstKey] . '/emails';
                            $directoryListing = preg_grep('/^([^.])/', scandir($firstDirectory));
                            $emailTemplates = array_diff($directoryListing, array('html', 'index.htm'));

                            ?>
                            <table class="table table-striped table-responsive">
                                <thead>
                                <tr>
                                    <th><?php echo $hesklang['file_name']; ?></th>
                                    <?php foreach ($languages as $language => $languageCode): ?>
                                        <th><?php echo $language; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($emailTemplates as $template): ?>
                                    <tr>
                                        <td><?php echo $template; ?></td>
                                        <?php foreach ($languages as $language => $languageCode): ?>
                                            <td>
                                                <?php
                                                echo getTemplateMarkup($template, $languageCode);
                                                echo '&nbsp;&nbsp;&nbsp;';
                                                if ($modsForHesk_settings['html_emails']) {
                                                    echo getTemplateMarkup($template, $languageCode, true);
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

function getTemplateMarkup($template, $languageCode, $html = false)
{
    global $hesklang;

    $templateUrl = urlencode($template);
    $languageCodeUrl = urlencode($languageCode);
    if ($html) {
        $markup = '<a href="manage_email_templates.php?action=edit&template=' . $templateUrl . '&language=' . $languageCodeUrl . '&html=true">';
        $markup .= '<i class="fa fa-html5 font-size-150" data-toggle="tooltip" title="' . $hesklang['edit_html_template'] . '"></i>';
        $markup .= '</a>';
        return $markup;
    } else {
        $markup = '<a href="manage_email_templates.php?action=edit&template=' . $templateUrl . '&language=' . $languageCodeUrl . '&html=false">';
        $markup .= '<i class="fa fa-file-text-o font-size-150" data-toggle="tooltip" title="' . $hesklang['edit_plain_text_template'] . '"></i>';
        $markup .= '</a>';
        return $markup;
    }
}

function save()
{
    global $hesklang;

    $filePath = HESK_PATH . 'language/' . $_POST['language'] . '/emails/' . $_POST['template'];
    if ($_POST['html'] == '1') {
        $filePath = HESK_PATH . 'language/' . $_POST['language'] . '/emails/html/' . $_POST['template'];
    }

    $success = file_put_contents($filePath, $_POST['text']);
    if ($success === false) {
        hesk_process_messages($hesklang['email_template_not_saved'], 'manage_email_templates.php');
    } else {
        $message = sprintf($hesklang['email_template_saved'], $_POST['template']);
        hesk_process_messages($message, 'manage_email_templates.php', 'SUCCESS');
    }
}

function getSpecialTagMap()
{
    global $hesk_settings, $modsForHesk_settings, $hesklang;

    $map = array();
    $map['%%NAME%%'] = $hesklang['customer_name'];
    $map['%%EMAIL%%'] = $hesklang['customer_email'];
    $map['%%SUBJECT%%'] = $hesklang['ticket_subject'];
    $map['%%MESSAGE%%'] = $hesklang['ticket_message'];
    $map['%%MESSAGE_NO_ATTACHMENTS%%'] = $hesklang['ticket_message_no_attachments'];
    $map['%%CREATED%%'] = $hesklang['ticket_created'];
    $map['%%UPDATED%%'] = $hesklang['ticket_updated'];
    $map['%%TRACK_ID%%'] = $hesklang['ticket_trackID'];
    $map['%%TRACK_URL%%'] = $hesklang['ticket_url'];
    $map['%%SITE_TITLE%%'] = $hesklang['wbst_title'];
    $map['%%SITE_URL%%'] = $hesklang['wbst_url'];
    $map['%%CATEGORY%%'] = $hesklang['ticket_category'];
    $map['%%OWNER%%'] = $hesklang['ticket_owner'];
    $map['%%PRIORITY%%'] = $hesklang['ticket_priority'];
    $map['%%STATUS%%'] = $hesklang['ticket_status'];

    $i = 1;
    foreach ($hesk_settings['custom_fields'] as $key => $value) {
        if ($value['use']) {
            $uppercaseKey = strtoupper($key);
            $map['%%' . $uppercaseKey . '%%'] = sprintf($hesklang['custom_field_x'], $i++);
        }
    }

    return $map;
}