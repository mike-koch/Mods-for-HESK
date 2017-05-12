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

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
<p style="display: none" id="lang_edit"><?php echo $hesklang['edit']; ?></p>
<p style="display: none" id="lang_delete"><?php echo $hesklang['delete']; ?></p>
<script type="text/html" id="nav-element-template">
    <tr id="nav-element-template">
        <td><span data-property="id"></span></td>
        <td><span>
                <ul data-property="text" class="list-unstyled"></ul>
            </span></td>
        <td><span data-property="subtext"></span></td>
        <td><span data-property="image-or-font"></span></td>
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