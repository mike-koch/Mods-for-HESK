<?php
/*******************************************************************************
 *  Title: Help Desk Software HESK
 *  Version: 2.6.5 from 28th August 2015
 *  Author: Klemen Stirn
 *  Website: http://www.hesk.com
 ********************************************************************************
 *  COPYRIGHT AND TRADEMARK NOTICE
 *  Copyright 2005-2015 Klemen Stirn. All Rights Reserved.
 *  HESK is a registered trademark of Klemen Stirn.
 *  The HESK may be used and modified free of charge by anyone
 *  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
 *  By using this code you agree to indemnify Klemen Stirn from any
 *  liability that might arise from it's use.
 *  Selling the code for this program, in part or full, without prior
 *  written consent is expressly forbidden.
 *  Using this code, in part or full, to create derivate work,
 *  new scripts or products is expressly forbidden. Obtain permission
 *  before redistributing this software over the Internet or in
 *  any other medium. In all cases copyright and header must remain intact.
 *  This Copyright is in full effect in any country that has International
 *  Trade Agreements with the United States of America or
 *  with the European Union.
 *  Removing any of the copyright notices without purchasing a license
 *  is expressly forbidden. To remove HESK copyright notice you must purchase
 *  a license for this script. For more information on how to obtain
 *  a license please visit the page below:
 *  https://www.hesk.com/buy.php
 *******************************************************************************/

require_once(HESK_PATH . 'build.php');

/* Check if this is a valid include */
if (!defined('IN_SCRIPT')) {
    die('Invalid attempt');
}
if (!function_exists('mfh_getSettings')) {
    die('Mods for HESK settings are not accessible!');
}

$modsForHesk_settings = array();
if (is_dir(HESK_PATH . 'install')) {
    $modsForHesk_settings['navbar_title_url'] = 'javascript:;';
    $modsForHesk_settings['rtl'] = 0;
    $modsForHesk_settings['use_bootstrap_theme'] = 1;
    $modsForHesk_settings['show_icons'] = 1;
    $modsForHesk_settings['navbarBackgroundColor'] = '#414a5c';
    $modsForHesk_settings['navbarBrandColor'] = '#d4dee7';
    $modsForHesk_settings['navbarBrandHoverColor'] = '#ffffff';
    $modsForHesk_settings['navbarItemTextColor'] = '#d4dee7';
    $modsForHesk_settings['navbarItemTextHoverColor'] = '#ffffff';
    $modsForHesk_settings['navbarItemTextSelectedColor'] = '#ffffff';
    $modsForHesk_settings['navbarItemSelectedBackgroundColor'] = '#2d3646';
    $modsForHesk_settings['dropdownItemTextColor'] = '#333333';
    $modsForHesk_settings['dropdownItemTextHoverColor'] = '#262626';
    $modsForHesk_settings['dropdownItemTextHoverBackgroundColor'] = '#f5f5f5';
    $modsForHesk_settings['questionMarkColor'] = '#000000';
} else {
    $modsForHesk_settings = mfh_getSettings();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo(isset($hesk_settings['tmp_title']) ? $hesk_settings['tmp_title'] : $hesk_settings['hesk_title']); ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=<?php echo $hesklang['ENCODING']; ?>"/>
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <meta name="theme-color" content="<?php echo '#414a5c'; ?>">
    <?php if ($modsForHesk_settings['rtl']) { ?>
        <link href="<?php echo HESK_PATH; ?>hesk_style_RTL.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" type="text/css"
              rel="stylesheet"/>
    <?php } else { ?>
        <link href="<?php echo HESK_PATH; ?>hesk_style.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" type="text/css"
              rel="stylesheet"/>
    <?php } ?>
    <link href="<?php echo HESK_PATH; ?>css/datepicker.css" type="text/css" rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" type="text/css"
          rel="stylesheet"/>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap-theme.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" type="text/css"
          rel="stylesheet" <?php if ($modsForHesk_settings['use_bootstrap_theme'] == 0) {
        echo 'disabled';
    } ?>>
    <?php if ($modsForHesk_settings['rtl']) { ?>
        <link href="<?php echo HESK_PATH; ?>css/bootstrap-rtl.min.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>"
              type="text/css" rel="stylesheet"/>
        <link href="<?php echo HESK_PATH; ?>css/mods-for-hesk.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" type="text/css"
              rel="stylesheet"/>
        <link href="<?php echo HESK_PATH; ?>css/hesk_newStyleRTL.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>"
              type="text/css" rel="stylesheet"/>
    <?php } else { ?>
        <link href="<?php echo HESK_PATH; ?>css/mods-for-hesk.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" type="text/css"
              rel="stylesheet"/>
        <link href="<?php echo HESK_PATH; ?>css/hesk_newStyle.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" type="text/css"
              rel="stylesheet"/>
    <?php } ?>
    <link href="<?php echo HESK_PATH; ?>css/bootstrap-iconpicker.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/octicons.css" type="text/css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/dropzone.min.css">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/dropzone-basic.min.css">
    <script src="<?php echo HESK_PATH; ?>js/jquery-1.10.2.min.js"></script>
    <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>hesk_javascript.js"></script>
    <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/dropzone.min.js"></script>
    <script language="Javascript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>js/modsForHesk-javascript.js"></script>
    <script language="JavaScript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/iconset-fontawesome-4.3.0.js"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/iconset-octicon-2.1.2.js"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap-iconpicker.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/platform.js"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap-validator.min.js"></script>
    <style>
        .navbar-default {
            background-color: <?php echo $modsForHesk_settings['navbarBackgroundColor']; ?>;
            background-image: none;
            filter: none;
        }

        .navbar-default .navbar-brand {
            color: <?php echo $modsForHesk_settings['navbarBrandColor']; ?>;
        }

        .navbar-default .navbar-brand:focus, .navbar-default .navbar-brand:hover {
            color: <?php echo $modsForHesk_settings['navbarBrandHoverColor']; ?>;
            background-color: transparent;
        }

        .navbar-default .navbar-nav > li > a {
            color: <?php echo $modsForHesk_settings['navbarItemTextColor']; ?>;
        }

        .navbar-default .navbar-nav > li > a:focus, .navbar-default .navbar-nav > li > a:hover {
            color: <?php echo $modsForHesk_settings['navbarItemTextHoverColor']; ?>;
            background-color: transparent;
        }

        .dropdown-menu > li > a {
            color: <?php echo $modsForHesk_settings['dropdownItemTextColor']; ?>;
        }

        .dropdown-menu > li > a:focus, .dropdown-menu > li > a:hover {
            color: <?php echo $modsForHesk_settings['dropdownItemTextHoverColor']; ?>;
            text-decoration: none;
            background-color: <?php echo $modsForHesk_settings['dropdownItemTextHoverBackgroundColor']; ?>;
        }

        .navbar-default .navbar-nav > .open > a,
        .navbar-default .navbar-nav > .open > a:focus,
        .navbar-default .navbar-nav > .open > a:hover,
        .navbar-default .navbar-nav > .active > a,
        .navbar-default .navbar-nav > .active > a:focus,
        .navbar-default .navbar-nav > .active > a:hover {
            color: <?php echo $modsForHesk_settings['navbarItemTextSelectedColor']; ?>;
            background-color: <?php echo $modsForHesk_settings['navbarItemSelectedBackgroundColor']; ?>;
            background-image: none;
        }

        .settingsquestionmark {
            color: <?php echo $modsForHesk_settings['questionMarkColor']; ?>;
            cursor: pointer;
        }

        .h3questionmark {
            color: <?php echo $modsForHesk_settings['questionMarkColor']; ?>;
        }
    </style>

    <?php

    /* Prepare Javascript that browser should load on page load */
    $onload = "javascript:var i=new Image();i.src='" . HESK_PATH . "img/orangebtnover.gif';var i2=new Image();i2.src='" . HESK_PATH . "img/greenbtnover.gif';";

    /* Tickets shouldn't be indexed by search engines */
    if (defined('HESK_NO_ROBOTS')) {
        ?>
        <meta name="robots" content="noindex, nofollow"/>
        <?php
    }

    /* If page requires calendar include calendar Javascript and CSS */
    if (defined('CALENDAR')) {
        ?>
        <script language="Javascript" type="text/javascript"
                src="<?php echo HESK_PATH; ?>inc/calendar/tcal.php"></script>
        <link href="<?php echo HESK_PATH; ?>inc/calendar/tcal.css" type="text/css" rel="stylesheet"/>
        <?php
    }

    /* If page requires WYSIWYG editor include TinyMCE Javascript */
    if (defined('WYSIWYG') && $hesk_settings['kb_wysiwyg']) {
        ?>
        <script type="text/javascript" src="<?php echo HESK_PATH; ?>inc/tiny_mce/3.5.11/tiny_mce.js"></script>
        <?php
    }

    /* If page requires tabs load tabs Javascript and CSS */
    if (defined('LOAD_TABS')) {
        ?>
        <link href="<?php echo HESK_PATH; ?>inc/tabs/tabber.css" type="text/css" rel="stylesheet"/>
        <?php
    }

    /* If page requires timer load Javascript */
    if (defined('TIMER')) {
        ?>
        <script language="Javascript" type="text/javascript"
                src="<?php echo HESK_PATH; ?>inc/timer/hesk_timer.js"></script>
        <?php

        /* Need to load default time or a custom one? */
        if (isset($_SESSION['time_worked'])) {
            $t = hesk_getHHMMSS($_SESSION['time_worked']);
            $onload .= "load_timer('time_worked', " . $t[0] . ", " . $t[1] . ", " . $t[2] . ");";
            unset($t);
        } else {
            $onload .= "load_timer('time_worked', 0, 0, 0);";
        }

        /* Autostart timer? */
        if (!empty($_SESSION['autostart'])) {
            $onload .= "ss();";
        }
    }

    // Use ReCaptcha API v2?
    if (defined('RECAPTCHA')) {
        echo '<script src="https://www.google.com/recaptcha/api.js?hl=' . $hesklang['RECAPTCHA'] . '" async defer></script>';
    }

    if (defined('VALIDATOR')) {
        ?>
        <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/validation-scripts.js"></script>
    <?php
    }
    ?>

</head>
<body onload="<?php echo $onload;
unset($onload); ?>">

<?php
include(HESK_PATH . 'header.txt');
$iconDisplay = 'style="display: none"';
if ($modsForHesk_settings['show_icons']) {
    $iconDisplay = '';
}
?>

<div class="enclosing">
    <nav class="navbar navbar-default navbar-static-top" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo $modsForHesk_settings['navbar_title_url']; ?>"><?php echo $hesk_settings['hesk_title'] ?></a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <?php
                $active = '';
                if (defined('PAGE_TITLE') && PAGE_TITLE == 'CUSTOMER_HOME') {
                    $active = 'class="active"';
                }
                ?>
                <li <?php echo $active; ?>><a href="<?php echo HESK_PATH; ?>"><i
                            class="fa fa-home" <?php echo $iconDisplay; ?>></i>&nbsp;<?php echo $hesklang['main_page']; ?>
                    </a></li>
                <?php
                $active = '';
                if (defined('PAGE_TITLE') && PAGE_TITLE == 'CUSTOMER_TICKET') {
                    $active = ' active';
                }
                ?>
                <li class="dropdown<?php echo $active; ?>">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i
                            class="fa fa-ticket" <?php echo $iconDisplay; ?>></i>&nbsp;<?php echo $hesklang['ticket'] ?>
                        <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="<?php echo HESK_PATH; ?>index.php?a=add"><i
                                    class="fa fa-plus-circle" <?php echo $iconDisplay; ?>></i>&nbsp;<?php echo $hesklang['sub_support'] ?>
                            </a></li>
                        <li><a href="<?php echo HESK_PATH; ?>ticket.php"><i
                                    class="fa fa-search" <?php echo $iconDisplay; ?>></i>&nbsp;<?php echo $hesklang['view_ticket_nav'] ?>
                            </a></li>
                    </ul>
                </li>
                <?php if ($hesk_settings['kb_enable']) {
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'CUSTOMER_KB') {
                        $active = 'class="active"';
                    }
                    ?>
                    <li <?php echo $active; ?>><a href="<?php echo HESK_PATH; ?>knowledgebase.php"><i
                                class="fa fa-book" <?php echo $iconDisplay; ?>></i>&nbsp;<?php echo $hesklang['kb_text'] ?>
                        </a></li> <?php } ?>
                <?php include('custom/header-custom.inc.php'); ?>
            </ul>
            <?php if ($hesk_settings['can_sel_lang']) { ?>
                <div class="navbar-form navbar-right" role="search" style="margin-right: 20px; min-width: 80px;">
                    <?php echo hesk_getLanguagesAsFormIfNecessary(); ?>
                </div>
            <?php } ?>

        </div>
        <!-- /.navbar-collapse -->
    </nav>

