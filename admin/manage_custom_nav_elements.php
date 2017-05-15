<?php

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_CUSTOM_NAV_ELEMENTS');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');
define('EXTRA_JS', '<script src="'.HESK_PATH.'internal-api/js/manage-custom-nav-elements.js"></script>');

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

//hesk_checkPermission('can_man_custom_nav');

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
                    Custom Nav Menu Elements[!]
                </h1>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        /* This will handle error, success and notice messages */
                        hesk_handle_messages();

                        $languages = array();
                        foreach ($hesk_settings['languages'] as $key => $value) {
                            $languages[$key] = $hesk_settings['languages'][$key]['folder'];
                        }

                        $customElementsRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element`");
                        ?>
                        <table class="table table-default">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Text</th>
                                <th>Subtext</th>
                                <th>Image URL / Font Icon</th>
                                <th>Place</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="table-body">
                            <tr id="loader">
                                <td colspan="6">
                                    <i class="fa fa-spinner fa-spin"></i> Loading Custom Nav Elements&hellip;
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="nav-element-modal" tabindex="-1" role="dialog" style="overflow: hidden">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="cursor: move">
                <button type="button" class="close cancel-callback" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo 'Edit Custom Nav Element'; ?>
                </h4>
            </div>
            <form id="create-form" class="form-horizontal" data-toggle="validator">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="place" class="col-md-4 col-sm-12 control-label">Place[!]</label>
                                <div class="col-md-8 col-sm-12">
                                    <select name="place" id="place" class="form-control">
                                        <option value="1">Homepage - Block</option>
                                        <option value="2">Customer Navbar</option>
                                        <option value="3">Staff Navbar</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <h4>Text[!]</h4>
                            <?php foreach ($hesk_settings['languages'] as $language => $value): ?>
                                <div class="form-group">
                                    <label for="text[<?php echo $language; ?>]" class="col-md-4 col-sm-12 control-label">
                                        <?php echo $language; ?>
                                    </label>
                                    <div class="col-md-8 col-sm-12">
                                        <input type="text" name="text[]" class="form-control"
                                               data-text-language="<?php echo $language; ?>"
                                               id="text[<?php echo $language; ?>" placeholder="<?php echo $language; ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div id="subtext">
                                <h4>Subtext[!]</h4>
                                <?php foreach ($hesk_settings['languages'] as $language => $value): ?>
                                    <div class="form-group">
                                        <label for="subtext[<?php echo $language; ?>]" class="col-md-4 col-sm-12 control-label">
                                            <?php echo $language; ?>
                                        </label>
                                        <div class="col-md-8 col-sm-12">
                                            <input type="text" name="subtext[]" class="form-control"
                                                   data-subtext-language="<?php echo $language; ?>"
                                                   id="subtext[<?php echo $language; ?>" placeholder="<?php echo $language; ?>">
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <h4>Image[!]</h4>
                            <div class="form-group">
                                <label for="image-type" class="col-md-4 col-sm-12 control-label">Image Type[!]</label>
                                <div class="col-md-8 col-sm-12">
                                    <select name="image-type" id="image-type" class="form-control">
                                        <option value="image-url">Image URL</option>
                                        <option value="font-icon">Font Icon</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" id="image-url-group">
                                <label for="image-url" class="col-md-4 col-sm-12 control-label">Image URL [!]</label>
                                <div class="col-md-8 col-sm-12">
                                    <input type="text" name="image-url" class="form-control" placeholder="Image URL[!]">
                                </div>
                            </div>
                            <div class="form-group" id="font-icon-group">
                                <label for="font-icon" class="col-md-4 col-sm-12 control-label">Font Icon [!]</label>
                                <div class="col-md-8 col-sm-12">
                                    <div class="btn btn-default iconpicker-container" data-toggle="iconpicker">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default cancel-callback" data-dismiss="modal">
                            <i class="fa fa-times-circle"></i>
                            <span><?php echo $hesklang['cancel']; ?></span>
                        </button>
                        <button type="button" class="btn btn-success callback-btn">
                            <i class="fa fa-check-circle"></i>
                            <span><?php echo $hesklang['save']; ?></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<p style="display: none" id="lang_edit"><?php echo $hesklang['edit']; ?></p>
<p style="display: none" id="lang_delete"><?php echo $hesklang['delete']; ?></p>
<script type="text/html" id="nav-element-template">
    <tr id="nav-element-template">
        <td><span data-property="id"></span></td>
        <td><span>
                <ul data-property="text" class="list-unstyled"></ul>
            </span></td>
        <td><span>
                <ul data-property="subtext" class="list-unstyled"></ul>
            </span></td>
        <td><span data-property="image-or-font"></span></td>
        <td style="display: none"><span data-property="place-id"></span></td>
        <td><span data-property="place"></span></td>
        <td>
            <a href="#" data-action="edit">
                <i class="fa fa-pencil icon-link orange"
                   data-toggle="tooltip" title="<?php echo $hesklang['edit']; ?>"></i>
            </a>
            <a href="#" data-action="delete">
                <i class="fa fa-times icon-link red"
                   data-toggle="tooltip" title="<?php echo $hesklang['delete']; ?>"></i>
            </a>
        </td>
    </tr>
</script>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');