<?php

define('IN_SCRIPT', 1);
define('HESK_PATH', '../');
define('PAGE_TITLE', 'ADMIN_CUSTOM_NAV_ELEMENTS');
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

//hesk_checkPermission('can_man_email_tpl');

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
                    <?php if ($showEditPanel): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4>EDIT CUSTOM NAV ELEMENT[!]</h4>
                                    </div>
                                    <div class="panel-body">
                                        <form action="manage_custom_nav_elements.php" method="post">
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
                                <tbody>
                                <?php
                                if (hesk_dbNumRows($customElementsRs) === 0) {
                                    echo '<tr><td colspan="6">No custom navigation elements</td></tr>';
                                }

                                while ($row = hesk_dbFetchAssoc($customElementsRs)):
                                    $localizedTextRs = hesk_dbQuery("SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "custom_nav_element_to_text`
                                        WHERE `nav_element_id` = " . intval($row['id']));
                                    $languageText = array();
                                    while ($textRow = hesk_dbFetchAssoc($localizedTextRs)) {
                                        $languageText[$textRow['language']] = $textRow;
                                    } ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <ul class="list-unstyled">
                                                <?php foreach ($languageText as $key => $value): ?>
                                                    <li>
                                                        <b><?php echo $key; ?>: </b>
                                                        <?php echo $value['text']; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                        <td>
                                            <ul class="list-unstyled">
                                                <?php
                                                if ($row['place'] == 0) {
                                                    foreach ($languageText as $key => $value): ?>
                                                        <li>
                                                            <b><?php echo $key; ?>: </b>
                                                            <?php echo $value['subtext']; ?>
                                                        </li>
                                                    <?php endforeach;
                                                } else {
                                                    echo '-';
                                                } ?>
                                            </ul>
                                        </td>
                                        <td>
                                            <?php if ($row['image_url'] !== null) {
                                                echo $row['image_url'];
                                            } else {
                                                echo '<i class="' . $row['font_icon'] . '"></i>';
                                            } ?>
                                        </td>
                                        <td>
                                            <?php if ($row['place'] == 0) {
                                                echo 'Homepage - Block';
                                            } elseif ($row['place'] == 1) {
                                                echo 'Customer Navbar';
                                            } elseif ($row['place'] == 2) {
                                                echo 'Staff Navbar';
                                            } else {
                                                echo 'INVALID!!';
                                            } ?>
                                        </td>
                                        <td>EDIT, DELETE</td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php
require_once(HESK_PATH . 'inc/footer.inc.php');
exit();

function save()
{
}