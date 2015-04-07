<?php

define('IN_SCRIPT',1);
define('HESK_PATH','../');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// TODO Check permissions for this feature

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>

<div class="row" style="padding: 20px">
    <ul class="nav nav-tabs" role="tablist">
        <?php
        // Show a link to banned_emails.php if user has permission
        if ( hesk_checkPermission('can_ban_emails',0) )
        {
            echo '
            <li role="presentation">
                <a title="' . $hesklang['banemail'] . '" href="banned_emails.php">'.$hesklang['banemail'].'</a>
            </li>
            ';
        }
        if ( hesk_checkPermission('can_ban_ips',0) )
        {
            echo '
            <li role="presentation">
                <a title="' . $hesklang['banip'] . '" href="banned_ips.php">'.$hesklang['banip'].'</a>
            </li>';
        }
        // Show a link to status_message.php if user has permission to do so
        if ( hesk_checkPermission('can_service_msg',0) )
        {
            echo '
            <li role="presentation">
                <a title="' . $hesklang['sm_title'] . '" href="service_messages.php">' . $hesklang['sm_title'] . '</a>
            </li>';
        }
        ?>
        <li role="presentation" class="active">
            <a href="#"><?php echo $hesklang['email_templates']; ?> <i class="fa fa-question-circle settingsquestionmark" data-toggle="popover" title="<?php echo $hesklang['email_templates']; ?>" data-content="<?php echo $hesklang['email_templates_intro']; ?>"></i></a>
        </li>
    </ul>
    <div class="tab-content summaryList tabPadding">
        <div class="row">
            <div class="col-md-12">
                <br><br>
                <?php
                /* This will handle error, success and notice messages */
                hesk_handle_messages();

                // Output list of templates, and provide links to edit the plaintext and HTML versions for each language
                // First get list of languages
                $languages = array();
                foreach ($hesk_settings['languages'] as $key => $value) {
                    $languages[$key] = $hesk_settings['languages'][$key]['folder'];
                }

                // Get all files, but don't worry about index.htm, .., ., or the html folder as we'll assume they both exist
                // We'll also assume the template file exists in all language folders
                reset($languages);
                $firstKey = key($languages);
                $firstDirectory = HESK_PATH . 'language/'.$languages[$firstKey].'/emails';
                $directoryListing = preg_grep('/^([^.])/', scandir($firstDirectory));
                $emailTemplates = array_diff($directoryListing, array('html', 'index.htm'));

                ?>
                <table class="table table-striped table-responsive">
                    <thead>
                        <tr>
                            <th><?php echo $hesklang['file_name']; ?></th>
                            <?php foreach ($languages as $key=>$value): ?>
                                <th><?php echo $key; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emailTemplates as $template): ?>
                            <tr>
                                <td><?php echo $template; ?></td>
                                <td>
                                    <?php
                                    echo getTemplateMarkup($template, 'en');
                                    echo '&nbsp;&nbsp;&nbsp;';
                                    echo getTemplateMarkup($template, 'en', true);
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Output list of templates, and provide links to edit the plaintext and HTML versions -->

            </div>
        </div>
    </div>
</div>

<?php
    function getTemplateMarkup($template, $languageCode, $html = false) {
        global $hesklang;

        $linkPlaintext = HESK_PATH . 'language/%s/emails/%s'; // First %s: language code, second: template file
        $linkHtml = HESK_PATH . 'language/%s/emails/html/%s'; // First %s: language code, second: template file

        if ($html) {
            $link = sprintf($linkHtml, $languageCode, $template);
            return
                '<a href="'.$link.'"><i class="fa fa-html5" style="font-size: 1.5em" data-toggle="tooltip" title="'.$hesklang['edit_html_template'].'"></i></a>';
        } else {
            $link = sprintf($linkPlaintext, $languageCode, $template);
            return
                '<a href="'.$link.'"><i class="fa fa-file-text-o" style="font-size: 1.5em" data-toggle="tooltip" title="'.$hesklang['edit_plain_text_template'].'"></i></a>';
        }
    }