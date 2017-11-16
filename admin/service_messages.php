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
define('PAGE_TITLE', 'ADMIN_SERVICE_MESSAGES');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');
define('EXTRA_JS', '<script src="'.HESK_PATH.'internal-api/js/service-messages.js"></script>');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

/* Check permissions for this feature */
hesk_checkPermission('can_service_msg');

// Define required constants
define('WYSIWYG', 1);

/* Print header */
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

/* Print main manage users page */
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
    <section class="content">
    <div class="box">
        <div class="box-header with-border">
            <h1 class="box-title">
                <?php echo $hesklang['manage_service_messages']; ?>
                <i class="fa fa-question-circle settingsquestionmark" data-toggle="tooltip"
                   title="<?php echo hesk_makeJsString($hesklang['sm_intro']); ?>"
                    data-placement="bottom"></i>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button id="create-button" class="btn btn-success">
                        <i class="fa fa-plus-circle"></i>&nbsp;
                        <?php echo $hesklang['create_new']; ?>
                    </button>
                </div>
                <div class="col-sm-12">
                    <?php

                    if ($hesk_settings['kb_wysiwyg']) {
                        ?>
                        <script type="text/javascript">
                            tinyMCE.init({
                                mode: "exact",
                                elements: "content",
                                theme: "advanced",
                                convert_urls: false,
                                gecko_spellcheck: true,
                                plugins: "autolink",

                                theme_advanced_buttons1: "cut,copy,paste,|,undo,redo,|,formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
                                theme_advanced_buttons2: "sub,sup,|,charmap,|,bullist,numlist,|,outdent,indent,insertdate,inserttime,preview,|,forecolor,backcolor,|,hr,removeformat,visualaid,|,link,unlink,anchor,image,cleanup,code",
                                theme_advanced_buttons3: "",

                                theme_advanced_toolbar_location: "top",
                                theme_advanced_toolbar_align: "left",
                                theme_advanced_statusbar_location: "bottom",
                                theme_advanced_resizing: true
                            });
                        </script>
                        <input type="hidden" name="kb_wysiwyg" value="1">
                        <?php
                    } else {
                        ?>
                        <input type="hidden" name="kb_wysiwyg" value="0">
                        <?php
                    }
                    ?>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th style="display: none"><?php echo $hesklang['id']; ?></th>
                            <th><?php echo $hesklang['sm_mtitle']; ?></th>
                            <th><?php echo $hesklang['sm_author']; ?></th>
                            <th><?php echo $hesklang['sm_type']; ?></th>
                            <th><?php echo $hesklang['opt']; ?></th>
                        </tr>
                        </thead>
                        <tbody id="table-body">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="overlay" id="overlay">
            <i class="fa fa-spinner fa-spin"></i>
        </div>
    </div>
</section>
</div>
<div class="modal fade" id="service-message-modal" tabindex="-1" role="dialog" style="overflow: hidden">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancel-callback" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span id="edit-label"><?php echo $hesklang['edit_sm']; ?></span>
                    <span id="create-label"><?php echo $hesklang['new_sm']; ?></span>
                </h4>
            </div>
            <form id="service-message" class="form-horizontal" data-toggle="validator" method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="row" style="padding-bottom: 10px;">
                            <label for="style"
                                   class="col-md-2 control-label"><?php echo $hesklang['sm_style']; ?></label>

                            <div class="col-md-3">
                                <div class="radio alert pad-5" style="box-shadow: none; border-radius: 4px;">
                                    <label>
                                        <input type="radio" name="style" value="0" onclick="setIcon('')">
                                        <?php echo $hesklang['sm_none']; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="radio alert alert-success pad-5">
                                    <label style="margin-top: -5px">
                                        <input type="radio" name="style" value="1" onclick="setIcon('fa fa-check-circle')">
                                        <?php echo $hesklang['sm_success']; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="radio alert alert-info pad-5" onclick="setIcon('fa fa-comment')">
                                    <label style="margin-top: -5px">
                                        <input type="radio" name="style" value="2">
                                        <?php echo $hesklang['sm_info']; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 col-md-offset-2">
                                <div class="radio alert alert-warning pad-5">
                                    <label style="margin-top: -5px">
                                        <input type="radio" name="style" value="3"
                                               onclick="setIcon('fa fa-exclamation-triangle')">
                                        <?php echo $hesklang['sm_notice']; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="radio alert alert-danger pad-5">
                                    <label style="margin-top: -5px">
                                        <input type="radio" name="style" value="4" onclick="setIcon('fa fa-times-circle')">
                                        <?php echo $hesklang['sm_error']; ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="icon" class="col-md-2 control-label"><?php echo $hesklang['sm_icon']; ?></label>
                        <div class="col-md-10">
                            <p style="display:none" id="no-icon"><?php echo $hesklang['sm_no_icon']; ?></p>

                            <p style="display:none" id="search-icon"><?php echo $hesklang['sm_search_icon']; ?></p>

                            <p style="display:none"
                               id="footer-icon"><?php echo $hesklang['sm_iconpicker_footer_label']; ?></p>

                            <div name="icon" class="btn btn-default iconpicker-container" data-toggle="iconpicker"
                                 data-search="false" data-icon=""></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="type" class="col-md-2 control-label"><?php echo $hesklang['sm_type']; ?></label>

                        <div class="col-md-2">
                            <div class="radio pad-5">
                                <label>
                                    <input type="radio" name="type" value="0">
                                    <?php echo $hesklang['sm_published']; ?>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="radio pad-5">
                                <label>
                                    <input type="radio" name="type" value="1">
                                    <?php echo $hesklang['sm_draft']; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="title"
                               class="col-md-2 control-label"><?php echo $hesklang['sm_mtitle']; ?></label>
                        <div class="col-md-10">
                            <input class="form-control"
                                   placeholder="<?php echo htmlspecialchars($hesklang['sm_mtitle']); ?>"
                                   type="text" name="title" size="70" maxlength="255"
                                   data-error="<?php echo htmlspecialchars($hesklang['sm_e_title']); ?>" required>
                            <div class="help-block with-errors"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="message"
                               class="col-md-2 control-label"><?php echo $hesklang['sm_msg']; ?></label>

                        <div class="col-md-10">
                            <textarea placeholder="<?php echo htmlspecialchars($hesklang['sm_msg']); ?>"
                                      class="form-control" name="message" id="content"></textarea>
                        </div>
                    </div>
                    <div id="preview-pane"></div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="id">
                    <input type="hidden" name="order">
                    <div id="action-buttons" class="btn-group">
                        <button type="button" class="btn btn-default cancel-button cancel-callback" data-dismiss="modal">
                            <i class="fa fa-times-circle"></i>
                            <span><?php echo $hesklang['cancel']; ?></span>
                        </button>
                        <button type="button" class="btn btn-primary preview-button">
                            <i class="fa fa-search"></i>
                            <span><?php echo $hesklang['sm_preview']; ?></span>
                        </button>
                        <button type="submit" class="btn btn-success save-button">
                            <i class="fa fa-check-circle"></i>
                            <span><?php echo $hesklang['save']; ?></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
echo mfh_get_hidden_fields_for_language(array(
    'sm_published',
    'sm_draft',
    'no_sm',
    'sm_added',
    'sm_mdf',
    'error_saving_updating_sm',
    'sm_deleted',
    'error_deleting_sm',
    'error_sorting_categories',
    'error_retrieving_sm',
));

echo '<script>var users = [];';
$usersRs = hesk_dbQuery("SELECT `id`, `name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` WHERE `active` = '1'");
$users = array();
while ($row = hesk_dbFetchAssoc($usersRs)) {
    $users[] = $row;
    echo "users[" . $row['id'] . "] = {
        id: ".$row['id'].",
        name: '".$row['name']."'
    }\n";
}
echo '</script>';
?>
<script type="text/html" id="service-message-title-template">
<div class="{{CLASS}}">
    <i data-property="icon"></i>
    <b data-property="title"></b>
</div>
</script>
<script type="text/html" id="service-message-preview-template">
    <?php
    $sm = array(
        'icon' => 'fa',
        'style' => 0,
        'title' => '{{TITLE}}',
        'message' => '{{MESSAGE}}'
    );
    hesk_service_message($sm);
    ?>
</script>
<script type="text/html" id="service-message-template">
<tr>
    <td style="display: none"><span data-property="id" data-value="x"></span></td>
    <td><span data-property="title"></span></td>
    <td><span data-property="author"></span></td>
    <td><span data-property="type"></span></td>
    <td>
        <span class="sort-arrows">
            <a href="#" data-action="sort"
               data-direction="up">
                <i class="fa fa-fw fa-arrow-up icon-link green"
                   data-toggle="tooltip" title="<?php echo $hesklang['move_up']; ?>"></i>
            </a>
            <a href="#" data-action="sort"
               data-direction="down">
                <i class="fa fa-fw fa-arrow-down icon-link green"
                   data-toggle="tooltip" title="<?php echo $hesklang['move_dn'] ?>"></i>
            </a>
        </span>
        <a href="#" data-action="edit">
            <i class="fa fa-fw fa-pencil icon-link orange"
               data-toggle="tooltip" title="<?php echo $hesklang['edit']; ?>"></i>
        </a>
        <a href="#" data-action="delete">
            <i class="fa fa-fw fa-times icon-link red"
               data-toggle="tooltip" title="<?php echo $hesklang['delete']; ?>"></i>
        </a>
    </td>
</tr>
</script>

<?php

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

?>
