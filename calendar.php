<?php

define('IN_SCRIPT', 1);
define('HESK_PATH', './');
define('PAGE_TITLE', 'CUSTOMER_CALENDAR');
define('MFH_CUSTOMER_CALENDAR', 1);
define('USE_JQUERY_2', 1);

/* Get all the required files and functions */
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');

// Are we in maintenance mode?
hesk_check_maintenance();

hesk_load_database_functions();

hesk_session_start();
/* Connect to database */
hesk_dbConnect();
$modsForHesk_settings = mfh_getSettings();

// Is the calendar enabled?
if ($modsForHesk_settings['enable_calendar'] != '1') {
    hesk_error($hesklang['calendar_disabled']);
}

$categories = array();
$orderBy = $modsForHesk_settings['category_order_column'];
$categorySql = "SELECT * FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "categories` WHERE `usage` <> 1 AND `type` = '0' ORDER BY '" . $orderBy . "'";
$categoryRs = hesk_dbQuery($categorySql);
while ($row = hesk_dbFetchAssoc($categoryRs))
{
    $row['css_style'] = "background: {$row['background_color']};";
    $row['background_volatile'] = 'background-volatile';
    if ($row['foreground_color'] != 'AUTO') {
        $row['background_volatile'] = '';
        $row['css_style'] .= " color: {$row['foreground_color']};";

        if ($row['display_border_outline'] == '1') {
            $row['css_style'] .= " border: solid 1px {$row['foreground_color']};";
        }
    }
    $categories[] = $row;
}

require_once(HESK_PATH . 'inc/header.inc.php');
?>

<div class="row pad-20">
    <div class="col-lg-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4><?php echo $hesklang['calendar_categories']; ?></h4>
            </div>
            <div class="panel-body">
                <ul class="list-unstyled">
                    <div class="btn-group btn-group-sm">
                        <button id="select-all" class="btn btn-default" data-select-all="category-toggle"><?php echo $hesklang['select_all_title_case']; ?></button>
                        <button id="deselect-all" class="btn btn-default" data-deselect-all="category-toggle"><?php echo $hesklang['deselect_all_title_case']; ?></button>
                    </div>
                    <?php foreach ($categories as $category): ?>
                        <li class="move-down-20 move-right-20">
                            <div class="checkbox">
                                <input type="checkbox" data-select-target="category-toggle" name="category-toggle" value="<?php echo $category['id']; ?>" checked>
                            </div>
                            <div class="hide-on-overflow no-wrap event-category <?php echo $category['background_volatile']; ?>"
                                 style="<?php echo $category['css_style']; ?>">
                                <?php echo $category['name']; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4>
                    <?php echo $hesklang['calendar_title_case']; ?>
                </h4>
            </div>
            <div class="panel-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>
<div class="popover-template" style="display: none">
    <div>
        <div class="popover-location">
            <strong><?php echo $hesklang['event_location']; ?></strong>
            <span></span>
        </div>
        <div class="popover-category">
            <strong><?php echo $hesklang['category']; ?></strong>
            <span></span>
        </div>
        <div class="popover-from">
            <strong><?php echo $hesklang['from']; ?></strong>
            <span></span>
        </div>
        <div class="popover-to">
            <strong><?php echo $hesklang['to_title_case']; ?></strong>
            <span></span>
        </div>
        <div class="popover-comments">
            <strong><?php echo $hesklang['event_comments']; ?></strong>
            <span></span>
        </div>
    </div>
</div>
<div style="display: none">
    <p id="lang_error_loading_events"><?php echo $hesklang['error_loading_events']; ?></p>
    <p id="setting_default_view"><?php echo $modsForHesk_settings['default_calendar_view']; ?></p>
    <p id="setting_first_day_of_week"><?php echo $modsForHesk_settings['first_day_of_week']; ?></p>
</div>