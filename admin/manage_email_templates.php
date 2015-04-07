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

define('WYSIWYG',1);
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
                                    if ($modsForHesk_settings['html_emails']) {
                                        echo getTemplateMarkup($template, 'en', true);
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Output markup for the modals -->
<?php foreach ($emailTemplates as $template) {
    echo getModalMarkup($template, 'en');
    if ($modsForHesk_settings['html_emails']) {
        echo getModalMarkup($template, 'en', true);
    }
} ?>

<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

function getTemplateMarkup($template, $languageCode, $html = false) {
    global $hesklang;

    $templateId = str_replace('.', '-', $template);
    $templateId = str_replace(' ', '-', $templateId);
    $languageCodeId = str_replace('.', '-', $languageCode);
    $languageCodeId = str_replace(' ', '-', $languageCodeId);
    if ($html) {
        $markup = '<a href="#" data-toggle="modal" data-target="#modal-html-'.$languageCodeId.'-'.$templateId.'">';
        $markup .= '<i class="fa fa-html5" style="font-size: 1.5em" data-toggle="tooltip" title="'.$hesklang['edit_html_template'].'"></i>';
        $markup .= '</a>';
        return $markup;
    } else {
        $markup = '<a href="#" data-toggle="modal" data-target="#modal-'.$languageCodeId.'-'.$templateId.'">';
        $markup .= '<i class="fa fa-file-text-o" style="font-size: 1.5em" data-toggle="tooltip" title="'.$hesklang['edit_plain_text_template'].'"></i>';
        $markup .= '</a>';
        return $markup;
    }
}

function getModalMarkup($template, $languageCode, $html = false) {
    global $hesklang;

    $templateId = str_replace('.', '-', $template);
    $templateId = str_replace(' ', '-', $templateId);
    $languageCodeId = str_replace('.', '-', $languageCode);
    $languageCodeId = str_replace(' ', '-', $languageCodeId);
    $id = 'modal-html-'.$languageCodeId.'-'.$templateId;
    $class = '';

    if ($html) {
        $title = sprintf($hesklang['editing_html_template'], $template);
        $content = file_get_contents(HESK_PATH . 'language/'.$languageCode.'/emails/html/'.$template);
        $class = 'htmlEditor';
    } else {
        $id = str_replace('html-', '', $id);
        $title = sprintf($hesklang['editing_template'], $template);
        $content = file_get_contents(HESK_PATH . 'language/'.$languageCode.'/emails/'.$template);
    }
    return '
        <div class="modal fade" id="'.$id.'" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="'.$id.'Label">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="'.$id.'Label">'.$title.'</h4>
                    </div>
                    <div class="modal-body">
                        <textarea class="'.$class.' form-control">'.$content.'</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </div>
        </div>';
}