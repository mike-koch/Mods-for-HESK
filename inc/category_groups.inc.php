<?php
function mfh_get_category_group_tree($categories) {
    global $hesk_settings;

    $language = $hesk_settings['languages'][$hesk_settings['language']]['folder'];

    $category_groups = mfh_get_category_groups($language, $categories);

    $none_group = array();
    $none_group['name'] = 'CATEGORY_GROUP_NONE';
    $none_group['categories'] = array();
    foreach ($categories as $k => $v) {
        if ($v['mfh_category_group_id'] == null) {
            $none_group['categories'][$k] = $v;
        }
    }
    $category_groups[] = $none_group;

    return $category_groups;
}

function mfh_get_category_groups($language, $categories, $parent_id = null) {
    global $hesk_settings;

    $parent = $parent_id === null ? 'IS NULL' : '= ' . intval($parent_id);
    $categoryGroupsRs = hesk_dbQuery("SELECT `group`.`id` AS `id`, `group`.`parent_id` AS `parent_id`, `i18n`.`text` AS `name`
                            FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_category_groups` `group`
                            INNER JOIN `" . hesk_dbEscape($hesk_settings['db_pfix']) . "mfh_category_groups_i18n` `i18n`
                                ON `group`.`id` = `i18n`.`category_group_id`
                                AND `i18n`.`language` = '" . hesk_dbEscape($language) . "'
                                AND `group`.`parent_id` {$parent}");

    $categoryGroups = array();
    $group_ids = array();
    while ($row = hesk_dbFetchAssoc($categoryGroupsRs)) {
        $categoryGroup = array();
        $categoryGroup['id'] = $row['id'];
        $group_ids[] = $row['id'];
        $categoryGroup['parent_id'] = $row['parent_id'];
        $categoryGroup['name'] = $row['name'];
        $categoryGroup['categories'] = array();
        $categoryGroup['children'] = mfh_get_category_groups($language, $categories, $row['id']);

        $categoryGroups[$row['id']] = $categoryGroup;
    }

    foreach ($categories as $k => $v) {
        if (in_array($v['mfh_category_group_id'], $group_ids)) {
            $categoryGroups[$v['mfh_category_group_id']]['categories'][$k] = $v;
        }
    }

    return $categoryGroups;
}

function mfh_is_category_group_empty($category_group) {
    if (count($category_group['categories']) > 0) {
        return false;
    }

    foreach ($category_group['children'] as $child) {
        return mfh_is_category_group_empty($child);
    }

    return true;
}

function mfh_output_category_group_dropdown_options($category_group, $level, $trail = '', $selected = null) {
    $padding = ($level * 10 + 20) . 'px';
    var_dump($padding);
    ?>
    <option class="category-group-header" style="padding-left: <?php echo $padding; ?>" <?php if ($category_group['name'] == 'CATEGORY_GROUP_NONE'){echo 'data-divider="true"';} else { echo 'disabled'; } ?>><?php echo $category_group['name'] == 'CATEGORY_GROUP_NONE' ? '' : $category_group['name']; ?></option>
    <?php foreach ($category_group['categories'] as $k => $v):
        $current_category_group = ' / ' . $category_group['name'];
        if ($category_group['name'] == 'CATEGORY_GROUP_NONE') {
            $current_category_group = '';
        }
        $slash_before = '/';
        if ($current_category_group == '') {
            $slash_before = '';
        }
        $display_trail = '<span style="font-size: .8em">' . $trail . $current_category_group . ' ' . $slash_before . '</span> ' . $v['name'];
        ?>
    <option style="padding-left: <?php echo $padding; ?>"
            title="<?php echo hesk_htmlspecialchars($display_trail); ?>"
            data-description="<?php echo $v['mfh_description']; ?>"
            value="<?php echo $k; ?>"
            <?php if ($selected == $k) { echo 'selected'; } ?>>
        <?php echo $v['name']; ?>
    </option>
    <?php endforeach;
    foreach ($category_group['children'] as $child) {
        if ($trail != '') {
            $trail .= ' / ';
        }
        $trail .= $category_group['name'];

        mfh_output_category_group_dropdown_options($child, $level + 1, $trail, $selected);
    }
}