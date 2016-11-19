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
if (!isset($status)) {
    $status = array();
    $allStatuses = mfh_getAllStatuses();
    //-- We don't want to check statuses that are considered "closed"
    foreach ($allStatuses as $row) {
        if ($row['IsClosed'] == 1) {
            continue;
        }

        $status[$row['ID']] = mfh_getDisplayTextForStatusId($row['ID']);
    }
}

if (!isset($priority)) {
    $priority = array(
        0 => 'CRITICAL',
        1 => 'HIGH',
        2 => 'MEDIUM',
        3 => 'LOW',
    );
}

if (!isset($what)) {
    $what = 'trackid';
}

if (!isset($owner_input)) {
    $owner_input = 0;
}

if (!isset($date_input)) {
    $date_input = '';
}

/* Can view tickets that are unassigned or assigned to others? */
$can_view_ass_others = hesk_checkPermission('can_view_ass_others', 0);
$can_view_unassigned = hesk_checkPermission('can_view_unassigned', 0);

/* Category options */
$category_options = '';
if (isset($hesk_settings['categories']) && count($hesk_settings['categories'])) {
    foreach ($hesk_settings['categories'] as $row['id'] => $row['name']) {
        $row['name'] = (strlen($row['name']) > 30) ? substr($row['name'], 0, 30) . '...' : $row['name'];
        $selected = ($row['id'] == $category) ? 'selected="selected"' : '';
        $category_options .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['name'] . '</option>';
    }
} else {
    $res2 = hesk_dbQuery('SELECT `id`, `name` FROM `' . hesk_dbEscape($hesk_settings['db_pfix']) . 'categories` WHERE ' . hesk_myCategories('id') . ' ORDER BY `cat_order` ASC');
    while ($row = hesk_dbFetchAssoc($res2)) {
        $row['name'] = (strlen($row['name']) > 30) ? substr($row['name'], 0, 30) . '...' : $row['name'];
        $selected = ($row['id'] == $category) ? 'selected="selected"' : '';
        $category_options .= '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['name'] . '</option>';
    }
}

/* List of staff */
if ($can_view_ass_others && !isset($admins)) {
    $admins = array();
    $res2 = hesk_dbQuery("SELECT `id`,`name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ORDER BY `name` ASC");
    while ($row = hesk_dbFetchAssoc($res2)) {
        $admins[$row['id']] = $row['name'];
    }
}

$more = empty($_GET['more']) ? 0 : 1;
$more2 = empty($_GET['more2']) ? 0 : 1;

#echo "SQL: $sql";
?>

<!-- ** START SHOW TICKET FORM ** -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?php echo $hesklang['show_tickets']; ?></h4>
    </div>
    <div class="panel-body">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td valign="top">
                    <form name="showt" action="show_tickets.php" method="get">
                        <table class="table" style="border-top: 0 !important" border="0" cellpadding="3" cellspacing="0"
                               width="100%">
                            <tr>
                                <td style="border-top: 0px" width="20%" class="alignTop">
                                    <b><?php echo $hesklang['status']; ?></b>: &nbsp; </td>
                                <td style="border-top: 0px" width="80%">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <?php
                                            $rowCounter = 1;
                                            $statuses = mfh_getAllStatuses();
                                            foreach ($statuses as $row) {
                                                if ($rowCounter > 3) {
                                                    echo '</tr><tr>';
                                                    $rowCounter = 1;
                                                }
                                                echo '<td width=';
                                                if ($rowCounter != 3) {
                                                    echo '"33%"';
                                                } else {
                                                    echo '"34%"';
                                                }
                                                echo '<label><input type="checkbox" name="s' . $row['ID'] . '" value="1"';
                                                if (isset($status[$row['ID']])) {
                                                    echo 'checked="checked"';
                                                }
                                                echo '/> <span style="font-weight: bold;color: ' . $row['TextColor'] . ';">' . mfh_getDisplayTextForStatusId($row['ID']) . '</span></label></td>';

                                                $rowCounter++;
                                            }
                                            ?>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <div id="topSubmit" style="display:<?php echo $more ? 'none' : 'block'; ?>">
                            <div class="btn-group">
                                <input class="btn btn-default" type="submit"
                                       value="<?php echo $hesklang['show_tickets']; ?>"/>
                                <a class="btn btn-default" href="javascript:void(0)"
                                   onclick="Javascript:hesk_toggleLayerDisplay('divShow');Javascript:hesk_toggleLayerDisplay('topSubmit');document.showt.more.value='1';"><?php echo $hesklang['mopt']; ?></a>
                            </div>
                        </div>

                        <div id="divShow" style="display:<?php echo $more ? 'block' : 'none'; ?>">

                            <table class="table table-striped" border="0" cellpadding="3" cellspacing="0" width="100%">
                                <tr>
                                    <td width="20%" class="borderTop alignTop">
                                        <b><?php echo $hesklang['priority']; ?></b>: &nbsp; </td>
                                    <td width="80%" class="borderTop alignTop">

                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td width="33%"><label><input type="checkbox" name="p0"
                                                                              value="1" <?php if (isset($priority[0])) {
                                                            echo 'checked="checked"';
                                                        } ?> /> <span
                                                            class="critical"><?php echo $hesklang['critical']; ?></span></label>
                                                </td>
                                                <td width="33%"><label><input type="checkbox" name="p2"
                                                                              value="1" <?php if (isset($priority[2])) {
                                                            echo 'checked="checked"';
                                                        } ?> /> <span
                                                            class="medium"><?php echo $hesklang['medium']; ?></span></label>
                                                </td>
                                                <td width="34%">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td width="33%"><label><input type="checkbox" name="p1"
                                                                              value="1" <?php if (isset($priority[1])) {
                                                            echo 'checked="checked"';
                                                        } ?> /> <span
                                                            class="important"><?php echo $hesklang['high']; ?></span></label>
                                                </td>
                                                <td width="33%"><label><input type="checkbox" name="p3"
                                                                              value="1" <?php if (isset($priority[3])) {
                                                            echo 'checked="checked"';
                                                        } ?> /> <span
                                                            class="normal"><?php echo $hesklang['low']; ?></span></label>
                                                </td>
                                                <td width="34%">&nbsp;</td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>

                                <tr>
                                    <td class="borderTop alignTop"><b><?php echo $hesklang['show']; ?></b>: &nbsp; </td>
                                    <td class="borderTop">

                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td width="33%" class="alignTop">
                                                    <label><input type="checkbox" name="s_my"
                                                                  value="1" <?php if ($s_my[1]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['s_my']; ?>
                                                    </label>
                                                    <?php
                                                    if ($can_view_unassigned) {
                                                        ?>
                                                        <br/>
                                                        <label><input type="checkbox" name="s_un"
                                                                      value="1" <?php if ($s_un[1]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['s_un']; ?>
                                                        </label>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                                <td width="33%" class="alignTop">
                                                    <?php
                                                    if ($can_view_ass_others) {
                                                        ?>
                                                        <label><input type="checkbox" name="s_ot"
                                                                      value="1" <?php if ($s_ot[1]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['s_ot']; ?>
                                                        </label>
                                                        <br/>
                                                        <?php
                                                    }
                                                    ?>
                                                    <label><input type="checkbox" name="archive"
                                                                  value="1" <?php if ($archive[1]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['disp_only_archived']; ?>
                                                    </label></td>
                                                <td width="34%">&nbsp;</td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>

                                <tr>
                                    <td class="borderTop alignTop"><b><?php echo $hesklang['sort_by']; ?></b>: &nbsp;
                                    </td>
                                    <td class="borderTop">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <?php
                                            array_unshift($hesk_settings['ticket_list'], 'priority');
                                            $hesk_settings['possible_ticket_list']['priority'] = $hesklang['priority'];

                                            $column = 1;

                                            foreach ($hesk_settings['ticket_list'] as $key) {
                                                if ($column == 1) {
                                                    echo '<tr><td width="34%">';
                                                } else {
                                                    echo '<td width="33%">';
                                                }

                                                echo '<label><input type="radio" name="sort" value="' . $key . '" ' . ($sort == $key ? 'checked="checked"' : '') . ' /> ' . $hesk_settings['possible_ticket_list'][$key] . '</label></td>';

                                                if ($column == 3) {
                                                    echo '</tr>';
                                                    $column = 1;
                                                } else {
                                                    $column++;
                                                }
                                            }

                                            // End table if needed
                                            if ($column == 3) {
                                                echo '<td width="33%">&nbsp;</td></tr>';
                                            } elseif ($column == 2) {
                                                echo '<td width="33%">&nbsp;</td><td width="33%">&nbsp;</td></tr>';
                                            }
                                            ?>
                                        </table>

                                    </td>
                                </tr>

                                <tr>
                                    <td class="alignTop"><b><?php echo $hesklang['gb']; ?></b>: &nbsp; </td>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td width="33%"><label><input type="radio" name="g"
                                                                              value="" <?php if (!$group) {
                                                            echo 'checked="checked"';
                                                        } ?> /> <?php echo $hesklang['dg']; ?></label></td>
                                                <td width="33%"><?php
                                                    if ($can_view_unassigned || $can_view_ass_others) {
                                                        ?>
                                                        <label><input type="radio" name="g"
                                                                      value="owner" <?php if ($group == 'owner') {
                                                                echo 'checked="checked"';
                                                            } ?> /> <?php echo $hesklang['owner']; ?></label>
                                                        <?php
                                                    } else {
                                                        echo '&nbsp;';
                                                    }
                                                    ?>
                                                </td>
                                                <td width="34%">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td width="33%"><label><input type="radio" name="g"
                                                                              value="category" <?php if ($group == 'category') {
                                                            echo 'checked="checked"';
                                                        } ?> /> <?php echo $hesklang['category']; ?></label></td>
                                                <td width="33%"><label><input type="radio" name="g"
                                                                              value="priority" <?php if ($group == 'priority') {
                                                            echo 'checked="checked"';
                                                        } ?> /> <?php echo $hesklang['priority']; ?></label></td>
                                                <td width="34%">&nbsp;</td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>

                                <tr>
                                    <td class="alignMiddle"><b><?php echo $hesklang['category']; ?></b>: &nbsp; </td>
                                    <td class="alignMiddle">
                                        <div class="col-md-4" style="padding-left: 0px"><select class="form-control"
                                                                                                name="category">
                                                <option value="0"><?php echo $hesklang['any_cat']; ?></option>
                                                <?php echo $category_options; ?>
                                            </select></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td><b><?php echo $hesklang['display']; ?></b>: &nbsp; </td>
                                    <td>
                                        <div class="col-md-2" style="padding-left: 0px; padding-right:0px"><input
                                                type="text" class="form-control" name="limit"
                                                value="<?php echo $maxresults; ?>" size="4"/></div>
                                        <div class="col-md-3"
                                             style="line-height: 40px"><?php echo $hesklang['tickets_page']; ?></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="alignMiddle"><b><?php echo $hesklang['order']; ?></b>: &nbsp; </td>
                                    <td class="alignMiddle">
                                        <label><input type="radio" name="asc" value="1" <?php if ($asc) {
                                                echo 'checked="checked"';
                                            } ?> /> <?php echo $hesklang['ascending']; ?></label>
                                        |
                                        <label><input type="radio" name="asc" value="0" <?php if (!$asc) {
                                                echo 'checked="checked"';
                                            } ?> /> <?php echo $hesklang['descending']; ?></label></td>
                                </tr>

                                <tr>
                                    <td class="alignTop"><b><?php echo $hesklang['opt']; ?></b>: &nbsp; </td>
                                    <td>

                                        <label><input type="checkbox" name="cot" value="1" <?php if ($cot) {
                                                echo 'checked="checked"';
                                            } ?> /> <?php echo $hesklang['cot']; ?></label><br/>
                                        <label><input type="checkbox" name="def"
                                                      value="1"/> <?php echo $hesklang['def']; ?></label> (<a
                                            href="admin_main.php?reset=1&amp;token=<?php echo hesk_token_echo(0); ?>"><?php echo $hesklang['redv']; ?></a>)

                                    </td>

                            </table>

                            <div class="btn-group">
                                <input class="btn btn-default" type="submit"
                                       value="<?php echo $hesklang['show_tickets']; ?>"/>
                                <a class="btn btn-default" href="javascript:void(0)"
                                   onclick="Javascript:hesk_toggleLayerDisplay('divShow');Javascript:hesk_toggleLayerDisplay('topSubmit');document.showt.more.value='0';"><?php echo $hesklang['lopt']; ?></a>
                            </div>
                            <input type="hidden" name="more" value="<?php echo $more ? 1 : 0; ?>"/>
                        </div>

                    </form>

                </td>
            </tr>
        </table>
    </div>
</div>

<!-- ** END SHOW TICKET FORM ** -->
<div class="blankSpace"></div>

<!-- ** START SEARCH TICKETS FORM ** -->
<div class="panel panel-default">
    <div class="panel-heading">
        <h4><?php echo $hesklang['find_ticket_by']; ?></h4>
    </div>
    <div class="panel-body">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td valign="top">

                    <form data-toggle="validator" class="form-inline" action="find_tickets.php" method="get" name="findby" id="findby">

                        <table class="table" style="width: auto" border="0" cellpadding="3" cellspacing="0">
                            <tr style="border: none">
                                <td class="text-left" style="border: none">
                                    <b style="color: #000"><?php echo $hesklang['s_for']; ?></b><br/>
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="q" size="30" <?php if (isset($q)) {
                                            echo 'value="' . $q . '"';
                                        } ?>>
                                    </div>
                                </td>
                                <td class="text-left" style="border: none">
                                    <b style="color: #000"><?php echo $hesklang['s_in']; ?></b><br/>
                                    <select class="form-control" name="what">
                                        <option style="background: #ffffff"
                                                value="trackid" <?php if ($what == 'trackid') {
                                            echo 'selected="selected"';
                                        } ?> ><?php echo $hesklang['trackID']; ?></option>
                                        <?php
                                        if ($hesk_settings['sequential']) {
                                            ?>
                                            <option style="background: #ffffff"
                                                    value="seqid" <?php if ($what == 'seqid') {
                                                echo 'selected="selected"';
                                            } ?> ><?php echo $hesklang['seqid']; ?></option>
                                            <?php
                                        }
                                        ?>
                                        <option style="background: #ffffff" value="name" <?php if ($what == 'name') {
                                            echo 'selected="selected"';
                                        } ?> ><?php echo $hesklang['name']; ?></option>
                                        <option style="background: #ffffff" value="email" <?php if ($what == 'email') {
                                            echo 'selected="selected"';
                                        } ?> ><?php echo $hesklang['email']; ?></option>
                                        <option style="background: #ffffff"
                                                value="subject" <?php if ($what == 'subject') {
                                            echo 'selected="selected"';
                                        } ?> ><?php echo $hesklang['subject']; ?></option>
                                        <option style="background: #ffffff"
                                                value="message" <?php if ($what == 'message') {
                                            echo 'selected="selected"';
                                        } ?> ><?php echo $hesklang['message']; ?></option>
                                        <?php
                                        foreach ($hesk_settings['custom_fields'] as $k => $v) {
                                            $selected = ($what == $k) ? 'selected="selected"' : '';
                                            if ($v['use']) {
                                                if ($modsForHesk_settings['custom_field_setting']) {
                                                    $v['name'] = $hesklang[$v['name']];
                                                }

                                                $v['name'] = (strlen($v['name']) > 30) ? substr($v['name'], 0, 30) . '...' : $v['name'];
                                                echo '<option style="background: #ffffff" value="' . $k . '" ' . $selected . '>' . $v['name'] . '</option>';
                                            }
                                        }
                                        ?>
                                        <option style="background: #ffffff" value="notes" <?php if ($what == 'notes') {
                                            echo 'selected="selected"';
                                        } ?> ><?php echo $hesklang['notes']; ?></option>
                                    </select>
                                </td>
                            </tr>
                        </table>

                        <div id="topSubmit2" style="display:<?php echo $more2 ? 'none' : 'block'; ?>">
                            <div class="btn-group">
                                <input class="btn btn-default" type="submit"
                                       value="<?php echo $hesklang['find_ticket']; ?>"/>
                                <a class="btn btn-default" href="javascript:void(0)"
                                   onclick="Javascript:hesk_toggleLayerDisplay('divShow2');Javascript:hesk_toggleLayerDisplay('topSubmit2');document.findby.more2.value='1';"><?php echo $hesklang['mopt']; ?></a>
                            </div>
                        </div>

                        <div id="divShow2" style="display:<?php echo $more2 ? 'block' : 'none'; ?>">

                            &nbsp;<br/>

                            <table class="table table-striped" border="0" cellpadding="3" cellspacing="0" width="100%">
                                <tr>
                                    <td class="alignMiddle" width="20%"><b><?php echo $hesklang['category']; ?></b>:
                                        &nbsp; </td>
                                    <td class="alignMiddle" width="80%">
                                        <select class="form-control" name="category">
                                            <option value="0"><?php echo $hesklang['any_cat']; ?></option>
                                            <?php echo $category_options; ?>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                                if ($can_view_ass_others) {
                                    ?>
                                    <tr>
                                        <td class="alignMiddle"><b><?php echo $hesklang['owner']; ?></b>: &nbsp; </td>
                                        <td class="alignMiddle">
                                            <select class="form-control" name="owner">
                                                <option value="0"><?php echo $hesklang['anyown']; ?></option>
                                                <?php
                                                foreach ($admins as $staff_id => $staff_name) {
                                                    echo '<option value="' . $staff_id . '" ' . ($owner_input == $staff_id ? 'selected="selected"' : '') . '>' . $staff_name . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td class="alignMiddle"><b><?php echo $hesklang['date']; ?></b>: &nbsp; </td>
                                    <td class="alignMiddle">
                                        <div class="col-md-3" style="padding-left: 0px"><input class="form-control datepicker"
                                                                                               type="text" name="dt"
                                                                                               id="dt"
                                                                                               size="10" <?php if ($date_input) {
                                                echo 'value="' . $date_input . '"';
                                            } ?> /></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="alignTop"><b><?php echo $hesklang['s_incl']; ?></b>: &nbsp; </td>
                                    <td>
                                        <label><input type="checkbox" name="s_my"
                                                      value="1" <?php if ($s_my[2]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['s_my']; ?>
                                        </label>
                                        <?php
                                        if ($can_view_ass_others) {
                                            ?>
                                            <br/>
                                            <label><input type="checkbox" name="s_ot"
                                                          value="1" <?php if ($s_ot[2]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['s_ot']; ?>
                                            </label>
                                            <?php
                                        }

                                        if ($can_view_unassigned) {
                                            ?>
                                            <br/>
                                            <label><input type="checkbox" name="s_un"
                                                          value="1" <?php if ($s_un[2]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['s_un']; ?>
                                            </label>
                                            <?php
                                        }
                                        ?>
                                        <br/>
                                        <label><input type="checkbox" name="archive"
                                                      value="1" <?php if ($archive[2]) echo 'checked="checked"'; ?> /> <?php echo $hesklang['disp_only_archived']; ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b><?php echo $hesklang['display']; ?></b>: &nbsp; </td>
                                    <td>
                                        <div class="col-md-2 text-right" style="padding-left: 0px; padding-right: 0px;">
                                            <input class="form-control" type="text" name="limit"
                                                   value="<?php echo $maxresults; ?>" size="4"/></div>
                                        <div class="col-md-10"
                                             style="line-height: 40px"><?php echo $hesklang['results_page']; ?></div>
                                    </td>
                                </tr>
                            </table>
                            <div class="btn-group">
                                <input class="btn btn-default" type="submit"
                                       value="<?php echo $hesklang['find_ticket']; ?>"/>
                                <a class="btn btn-default" href="javascript:void(0)"
                                   onclick="Javascript:hesk_toggleLayerDisplay('divShow2');Javascript:hesk_toggleLayerDisplay('topSubmit2');document.findby.more2.value='0';"><?php echo $hesklang['lopt']; ?></a>
                            </div>
                            <input type="hidden" name="more2" value="<?php echo $more2 ? 1 : 0; ?>"/>
                        </div>
                    </form>
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- ** END SEARCH TICKETS FORM ** -->

