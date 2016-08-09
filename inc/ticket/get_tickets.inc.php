<?php

/**
 * @param $search_filter Filter to search by. Valid criteria:
 *     //TODO
 */
function get_tickets($search_filter, $hesk_settings) {
    $sql = "SELECT `id`, `trackid`, `name`, `email`, `category`, `priority`, `subject`, LEFT(`message`, 400) AS `message`,
        `dt`, `lastchange`, `firstreply`, `closedat`, `status`, `openedby`, `firstreplyby`, `closedby`, `replies`, `staffreplies`, `owner`,
        `time_worked`, `lastreplier`, `replierid`, `archive`, `locked`, `merged`, `due_date`, `latitude`, `longitude`, `user_agent`, 
        `screen_resolution_width`, `screen_resolution_height`";

    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        if ($v['use']) {
            $sql .= ", `" . $k . "`";
        }
    }

    $sql .= " FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "tickets` WHERE ";

    // --> CATEGORY
    $category = $search_filter['category'];
    if ($category > 0 && hesk_okCategory($category, 0)) {
        $sql .= " `category`='{$category}' ";
    } else {
        $sql .= hesk_myCategories();
    }

    // --> TAGGED
    $tagged = $search_filter['tagged'];
    if ($tagged) {
        $sql .= " AND `archive`='1' ";
    }

    // --> TICKET ASSIGNMENT
    $sql = handle_ticket_assignments($search_filter, $sql);
    
    // --> TICKET STATUS
    $statuses = $search_filter['status'];
    if (count($statuses) > 0) {
        $sql .= " AND `status` IN ('" . implode("','", $statuses) . "') ";
    }

    // --> TICKET PRIORITY
    $priorities = $search_filter['priority'];

    if (count($priorities) > 0) {
        $sql .= " AND `priority` IN ('" . implode("','", array_keys($priority)) . "') ";
    }

    // Sorting
    $sql .= " ORDER BY ";
    
    // --> PUSH TO TOP
    $force_to_top = $search_filter['force_to_top'];
    $direction = $search_filter['force_direction'];
    if ($force_to_top != NULL) {
        if ($force_to_top == 'owner') {
            $sql .= " CASE WHEN `owner` = '".intval($_SESSION['id'])."' THEN 1 ELSE 0 END DESC, `owner` ASC, ";
        } else {
            $sql .= ' `'.hesk_dbEscape($force_to_top).'` ';
            $sql .= $direction == 'ascending' ? 'ASC, ' : 'DESC, ';
        }
    }

    // --> CRITICAL ON TOP
    $critical_on_top = $search_filter['critical_on_top'];
    if ($critical_on_top) {
        $sql .= " CASE WHEN `priority` = '0' THEN 1 ELSE 0 END DESC , ";
    }

    // --> SORT BY
    $sort_by = $search_filter['sort_by'];
    $sort_direction = $search_filter['sort_direction'];

    if ($sort_by) {
        $sql .= $sort_by == 'lastreplier'
            ? " CASE WHEN `lastreplier` = '0' THEN 0 ELSE 1 END DESC, COALESCE(`replierid`, NULLIF(`lastreplier`, '0'), `name`) "
            : ' `'.hesk_dbEscape($sort_by).'` ';
    } else {
        $sql .= ' `status` ';
        $sort_by = 'status';
    }

    $sql .= $sort_direction == 'ascending'
        ? ' ASC '
        : ' DESC ';

    if ($sort_by != 'priority') {
        $sql .= ' , `priority` ASC ';
    }

    //Uncomment for debugging purposes
    //echo "SQL: $sql";
    $results = hesk_dbQuery($sql);

    return $results;
}

function handle_ticket_assignments($search_filter, $sql) {
    $assigned_to_self = $search_filter['assignment']['self'];
    $assigned_to_others = $search_filter['assignment']['others'];
    $assigned_to_no_one = $search_filter['assignment']['no_one'];

    if (!$assigned_to_self && !$assigned_to_others && !$assigned_to_no_one) {
        $assigned_to_self = true;
        $assigned_to_others = true;
        $assigned_to_no_one = true;

        if (!defined('MAIN_PAGE')) {
            hesk_show_notice($hesklang['e_nose']);
        }
    }

    /* If the user doesn't have permission to view assigned to others block those */
    if (!hesk_checkPermission('can_view_ass_others',0)) {
        $assigned_to_others = 0;
    }

    /* If the user doesn't have permission to view unassigned tickets block those */
    if (!hesk_checkPermission('can_view_unassigned',0)) {
        $assigned_to_no_one = 0;
    }

    /* Process assignments */
    if (!$assigned_to_self || !$assigned_to_others || !$assigned_to_no_one) {
        if ($assigned_to_self && $assigned_to_others) {
            // All but unassigned
            $sql .= " AND `owner` > 0 ";
        } elseif ($assigned_to_self && $assigned_to_no_one) {
            // My tickets + unassigned
            $sql .= " AND `owner` IN ('0', '" . intval($_SESSION['id']) . "') ";
        } elseif ($assigned_to_others && $assigned_to_no_one) {
            // Assigned to others + unassigned
            $sql .= " AND `owner` != '" . intval($_SESSION['id']) . "' ";
        }
        elseif ($assigned_to_self) {
            // Assigned to me only
            $sql .= " AND `owner` = '" . intval($_SESSION['id']) . "' ";
        } elseif ($assigned_to_others) {
            // Assigned to others
            $sql .= " AND `owner` NOT IN ('0', '" . intval($_SESSION['id']) . "') ";
        } elseif ($assigned_to_no_one) {
            // Only unassigned
            $sql .= " AND `owner` = 0 ";
        }
    }

    return $sql;
}

function get_empty_filter() {
    $search_filter['category'] = NULL;
    $search_filter['tagged'] = NULL;
    $search_filter['status'] = NULL;
    $search_filter['priority'] = NULL;
    $search_filter['force_to_top'] = NULL;
    $search_filter['force_direction'] = NULL;
    $search_filter['critical_on_top'] = NULL;
    $search_filter['sort_by'] = NULL;
    $search_filter['sort_direction'] = NULL;
    $search_filter['assignment'] = array();
    $search_filter['assignment']['self'] = 0;
    $search_filter['assignment']['others'] = 0;
    $search_filter['assignment']['no_one'] = 0;

    return $search_filter;
}