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
                <?php echo $hesklang['manage_service_messages']; ?> <a href="javascript:void(0)"
                                                          onclick="alert('<?php echo hesk_makeJsString($hesklang['sm_intro']); ?>')"><i
                            class="fa fa-question-circle settingsquestionmark"></i></a>
            </h1>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
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
                        <?php
                    }
                    ?>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th><?php echo $hesklang['sm_mtitle']; ?></th>
                            <th><?php echo $hesklang['sm_author']; ?></th>
                            <th><?php echo $hesklang['sm_type']; ?></th>
                            <th><?php echo $hesklang['opt']; ?></th>
                        </tr>
                        </thead>
                        <tbody id="table-body">
                        </tbody>
                    </table>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4><a name="new_article"></a><?php echo hesk_SESSION('edit_sm') ? $hesklang['edit_sm'] : $hesklang['new_sm']; ?></h4>
                        </div>
                        <div class="panel-body">
                            <form action="service_messages.php" method="post" name="form1" role="form" class="form-horizontal" data-toggle="validator">
                                <div class="form-group">
                                    <label for="style"
                                           class="col-md-2 control-label"><?php echo $hesklang['sm_style']; ?></label>

                                    <div class="col-md-2">
                                        <div class="radio alert pad-5" style="box-shadow: none; border-radius: 4px;">
                                            <label>
                                                <input type="radio" name="style" value="0" onclick="setIcon('')"
                                                    <?php if (!isset($_SESSION['new_sm']['style']) || (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 0)) {
                                                        echo 'checked';
                                                    } ?>>
                                                <?php echo $hesklang['sm_none']; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="radio alert alert-success pad-5">
                                            <label style="margin-top: -5px">
                                                <input type="radio" name="style" value="1"
                                                       onclick="setIcon('fa fa-check-circle')"
                                                    <?php if (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 1) {
                                                        echo 'checked';
                                                    } ?>>
                                                <?php echo $hesklang['sm_success']; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="radio alert alert-info pad-5" onclick="setIcon('fa fa-comment')">
                                            <label style="margin-top: -5px">
                                                <input type="radio" name="style" value="2"
                                                    <?php if (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 2) {
                                                        echo 'checked';
                                                    } ?>>
                                                <?php echo $hesklang['sm_info']; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="radio alert alert-warning pad-5"
                                             onclick="setIcon('fa fa-exclamation-triangle')">
                                            <label style="margin-top: -5px">
                                                <input type="radio" name="style" value="3"
                                                    <?php if (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 3) {
                                                        echo 'checked';
                                                    } ?>>
                                                <?php echo $hesklang['sm_notice']; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="radio alert alert-danger pad-5" onclick="setIcon('fa fa-times-circle')">
                                            <label style="margin-top: -5px">
                                                <input type="radio" name="style" value="4"
                                                    <?php if (isset($_SESSION['new_sm']['style']) && $_SESSION['new_sm']['style'] == 4) {
                                                        echo 'checked';
                                                    } ?> >
                                                <?php echo $hesklang['sm_error']; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="icon" class="col-md-2 control-label"><?php echo $hesklang['sm_icon']; ?></label>
                                    <?php
                                    $icon = '';
                                    if (isset($_SESSION['new_sm']['icon'])) {
                                        $icon = $_SESSION['new_sm']['icon'];
                                    }
                                    ?>
                                    <div class="col-md-10">
                                        <p style="display:none" id="no-icon"><?php echo $hesklang['sm_no_icon']; ?></p>

                                        <p style="display:none" id="search-icon"><?php echo $hesklang['sm_search_icon']; ?></p>

                                        <p style="display:none"
                                           id="footer-icon"><?php echo $hesklang['sm_iconpicker_footer_label']; ?></p>

                                        <div name="icon" class="btn btn-default iconpicker-container" data-toggle="iconpicker"
                                             data-icon="<?php echo $icon; ?>"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="type" class="col-md-2 control-label"><?php echo $hesklang['sm_type']; ?></label>

                                    <div class="col-md-2">
                                        <div class="radio pad-5">
                                            <label>
                                                <input type="radio" name="type" value="0"
                                                    <?php if (!isset($_SESSION['new_sm']['type']) || (isset($_SESSION['new_sm']['type']) && $_SESSION['new_sm']['type'] == 0)) {
                                                        echo 'checked';
                                                    } ?> >
                                                <?php echo $hesklang['sm_published']; ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="radio pad-5">
                                            <label>
                                                <input type="radio" name="type" value="1"
                                                    <?php if (isset($_SESSION['new_sm']['type']) && $_SESSION['new_sm']['type'] == 1) {
                                                        echo 'checked';
                                                    } ?> >
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
                                            <?php if (isset($_SESSION['new_sm']['title'])) {
                                                echo 'value="' . $_SESSION['new_sm']['title'] . '"';
                                            } ?> data-error="<?php echo htmlspecialchars($hesklang['sm_e_title']); ?>" required>
                                        <div class="help-block with-errors"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="message"
                                           class="col-md-2 control-label"><?php echo $hesklang['sm_msg']; ?></label>

                                    <div class="col-md-10">
                                    <textarea placeholder="<?php echo htmlspecialchars($hesklang['sm_msg']); ?>"
                                              class="form-control" name="message" rows="25" cols="70" id="content">
                                        <?php if (isset($_SESSION['new_sm']['message'])) {
                                            echo $_SESSION['new_sm']['message'];
                                        } ?>
                                    </textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php echo isset($_SESSION['edit_sm']) ? '<input type="hidden" name="a" value="save_sm" /><input type="hidden" name="id" value="' . intval($_SESSION['new_sm']['id']) . '" />' : '<input type="hidden" name="a" value="new_sm" />'; ?>
                                    <input type="hidden" name="token" value="<?php hesk_token_echo(); ?>"/>

                                    <div class="col-md-10 col-md-offset-2">
                                        <div class="btn-group" role="group">
                                            <input type="submit" name="sm_save" value="<?php echo $hesklang['sm_save']; ?>"
                                                   class="btn btn-primary">
                                            <input type="submit" name="sm_preview"
                                                   value="<?php echo $hesklang['sm_preview']; ?>" class="btn btn-default">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="overlay" id="overlay">
            <i class="fa fa-spinner fa-spin"></i>
        </div>
    </div>
</section>
</div>
<?php
echo mfh_get_hidden_fields_for_language(array(
    'sm_published',
    'sm_draft',
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
<tr data-property="id" data-value="x">
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
