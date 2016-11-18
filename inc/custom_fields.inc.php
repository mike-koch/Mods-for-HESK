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
if (!defined('IN_SCRIPT')) {die('Invalid attempt');}

// Get and append custom fields setup to the settings
hesk_load_custom_fields();

// Save number of custom fields
$hesk_settings['num_custom_fields'] = count($hesk_settings['custom_fields']);

// Load custom fields for admin functions
if (function_exists('hesk_checkPermission'))
{
	foreach ($hesk_settings['custom_fields'] as $k => $v)
	{
		$hesk_settings['possible_ticket_list'][$k] = $hesk_settings['custom_fields'][$k]['title'];
	}
}


/*** FUNCTIONS ***/


function hesk_load_custom_fields($category=0, $use_cache=1)
{
	global $hesk_settings, $hesklang;

	// Do we have a cached version available
	$cache_dir = dirname(dirname(__FILE__)).'/'.$hesk_settings['cache_dir'].'/';
	$cache_file = $cache_dir . 'cf_' . sha1($hesk_settings['language']).'.cache.php';

	if ($use_cache && file_exists($cache_file))
	{
		require($cache_file);
		return true;
	}

	// Get custom fields from the database
	$hesk_settings['custom_fields'] = array();

    // Make sure we have database connection
    hesk_load_database_functions();
    hesk_dbConnect();

	$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` WHERE `use` IN ('1', '2') ORDER BY `place` ASC, `order` ASC");
	while ($row = hesk_dbFetchAssoc($res))
	{
		$id = 'custom' . $row['id'];
		unset($row['id']);

		// Let's set field name for current language (or the first one we find)
		$names = json_decode($row['name'], true);
		$row['name'] = (isset($names[$hesk_settings['language']])) ? $names[$hesk_settings['language']] : reset($names);

        // Name for display in ticket list; punctuation removed and shortened
        $row['title'] = hesk_remove_punctuation($row['name']);
        $row['title'] = strlen($row['title']) > 30 ?  substr($row['title'], 0, 30) . '...' : $row['title'];

        // A version with forced punctuation
        $row['name:'] = in_array(substr($row['name'], -1), array(':', '?', '!', '.') ) ? $row['name'] : $row['name'] . ':';

		// Decode categories
		$row['category'] = strlen($row['category']) ? json_decode($row['category'], true) : array();

		// Decode options
		$row['value'] = json_decode($row['value'], true);

		// Add to custom_fields array
		$hesk_settings['custom_fields'][$id] = $row;
	}

    // Try to cache results
    if ($use_cache && (is_dir($cache_dir) || ( @mkdir($cache_dir, 0777) && is_writable($cache_dir) ) ) )
    {
        // Is there an index.htm file?
        if ( ! file_exists($cache_dir.'index.htm'))
        {
            @file_put_contents($cache_dir.'index.htm', '');
        }

        // Write data
        @file_put_contents($cache_file, '<?php if (!defined(\'IN_SCRIPT\')) {die();} $hesk_settings[\'custom_fields\']=' . var_export($hesk_settings['custom_fields'], true) . ';' );
    }

	return true;
} // END hesk_load_custom_fields()


function hesk_is_custom_field_in_category($custom_id, $category_id)
{
	global $hesk_settings;

	return (
			empty($hesk_settings['custom_fields'][$custom_id]['category']) ||
			in_array($category_id, $hesk_settings['custom_fields'][$custom_id]['category'])
			) ? true : false;
} // END hesk_is_custom_field_in_category()


function hesk_custom_field_type($type)
{
	global $hesklang;

	switch ($type)
	{
		case 'text':
			return $hesklang['stf'];
		case 'textarea':
			return $hesklang['stb'];
		case 'radio':
			return $hesklang['srb'];
		case 'select':
			return $hesklang['ssb'];
		case 'checkbox':
			return $hesklang['scb'];
		case 'email':
			return $hesklang['email'];
		case 'date':
			return $hesklang['date'];
		case 'hidden':
			return $hesklang['sch'];
		case 'readonly':
			return $hesklang['readonly'];
		default:
			return false;
	}

} // END hesk_custom_field_type()


function hesk_custom_date_display_format($timestamp, $format = 'F j, Y')
{
	global $hesklang;

	if ($timestamp == '')
	{
		return '';
	}

    if ( ! is_int($timestamp))
    {
        $timestamp = $timestamp * 1;
    }

	if ($hesklang['LANGUAGE']=='English')
    {
		return gmdate($format, $timestamp);
    }

    // Attempt to translate date for non-English users

	$translate_months = array(
		'January' => $hesklang['m1'],
		'February' => $hesklang['m2'],
		'March' => $hesklang['m3'],
		'April' => $hesklang['m4'],
		'May' => $hesklang['m5'],
		'June' => $hesklang['m6'],
		'July' => $hesklang['m7'],
		'August' => $hesklang['m8'],
		'September' => $hesklang['m9'],
		'October' => $hesklang['m10'],
		'November' => $hesklang['m11'],
		'December' => $hesklang['m12']
    );

	$translate_months_short = array(
		'Jan' => $hesklang['ms01'],
		'Feb' => $hesklang['ms02'],
		'Mar' => $hesklang['ms03'],
		'Apr' => $hesklang['ms04'],
		'May' => $hesklang['ms05'],
		'Jun' => $hesklang['ms06'],
		'Jul' => $hesklang['ms07'],
		'Aug' => $hesklang['ms08'],
		'Sep' => $hesklang['ms09'],
		'Oct' => $hesklang['ms10'],
		'Nov' => $hesklang['ms11'],
		'Dec' => $hesklang['ms12']
    );

	$translate_days = array(
		'Monday' => $hesklang['d1'],
		'Tuesday' => $hesklang['d2'],
		'Wednesday' => $hesklang['d3'],
		'Thursday' => $hesklang['d4'],
		'Friday' => $hesklang['d5'],
		'Saturday' => $hesklang['d6'],
		'Sunday' => $hesklang['d0']
    );

	$translate_days_short = array(
		'Mon' => $hesklang['mo'],
		'Tuw' => $hesklang['tu'],
		'Wes' => $hesklang['we'],
		'Thu' => $hesklang['th'],
		'Fri' => $hesklang['fr'],
		'Sat' => $hesklang['sa'],
		'Sun' => $hesklang['su']
    );

    $date_translate = array();

	if (strpos($format, 'F') !== false)
    {
    	$date_translate = array_merge($date_translate, $translate_months);
    }

	if (strpos($format, 'M') !== false)
    {
    	$date_translate = array_merge($date_translate, $translate_months_short);
    }

	if (strpos($format, 'l') !== false)
    {
    	$date_translate = array_merge($date_translate, $translate_days);
    }

	if (strpos($format, 'D') !== false)
    {
    	$date_translate = array_merge($date_translate, $translate_days_short);
    }

	if (count($date_translate))
    {
		return str_replace( array_keys($date_translate), array_values($date_translate), gmdate($format, $timestamp));
    }

    return gmdate($format, $timestamp);

} // END hesk_custom_date_display_format()


function hesk_remove_punctuation($in)
{
    return rtrim($in, ':?!.');
} // END hesk_remove_punctuation()
