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
    define('MAINTENANCE_MODE', true);
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
    $modsForHesk_settings['enable_calendar'] = 1;
} else {
    $modsForHesk_settings = mfh_getSettings();
}

?>
<!DOCTYPE html>
<html>
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
    <link href="<?php echo HESK_PATH; ?>css/bootstrap-iconpicker.min.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/octicons.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>" type="text/css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/dropzone.min.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/dropzone-basic.min.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/fullcalendar.min.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/bootstrap-clockpicker.min.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/jquery.jgrowl.min.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>">
    <link rel="stylesheet" href="<?php echo HESK_PATH; ?>css/bootstrap-colorpicker.min.css?v=<?php echo MODS_FOR_HESK_BUILD; ?>">
    <?php if (defined('USE_JQUERY_2')): ?>
        <script src="<?php echo HESK_PATH; ?>js/jquery-2.2.4.min.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <?php else: ?>
        <script src="<?php echo HESK_PATH; ?>js/jquery-1.10.2.min.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <?php endif; ?>
    <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>hesk_javascript.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script language="Javascript" type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap.min.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/dropzone.min.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script language="Javascript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>js/modsForHesk-javascript.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script language="JavaScript" type="text/javascript"
            src="<?php echo HESK_PATH; ?>js/bootstrap-datepicker.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap-clockpicker.min.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/iconset-fontawesome-4.3.0.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/iconset-octicon-2.1.2.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap-iconpicker.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/platform.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap-validator.min.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>internal-api/js/core.php?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/jquery.jgrowl.min.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
    <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/bootstrap-colorpicker.min.js?v=<?php echo MODS_FOR_HESK_BUILD; ?>"></script>
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

    if (defined('MFH_CUSTOMER_CALENDAR')) {
        ?>
        <script src="<?php echo HESK_PATH; ?>js/calendar/moment.js"></script>
        <script src="<?php echo HESK_PATH; ?>js/calendar/fullcalendar.min.js"></script>
        <script src="<?php echo HESK_PATH; ?>js/calendar/locale/<?php echo $hesk_settings['languages'][$hesk_settings['language']]['folder'] ?>.js"></script>
        <script type="text/javascript" src="<?php echo HESK_PATH; ?>js/calendar/mods-for-hesk-calendar-readonly.js"></script>
    <?php
    }

    // Include custom head code
    include(HESK_PATH . 'head.txt');
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
                if ($hesk_settings['kb_enable'] !== 2 && !defined('MAINTENANCE_MODE')) {
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
                <?php
                }
                if ($hesk_settings['kb_enable'] && !defined('MAINTENANCE_MODE')) {
                    $active = '';
                    if (defined('PAGE_TITLE') && PAGE_TITLE == 'CUSTOMER_KB') {
                        $active = 'class="active"';
                    }
                    ?>
                    <li <?php echo $active; ?>><a href="<?php echo HESK_PATH; ?>knowledgebase.php"><i
                                class="fa fa-book" <?php echo $iconDisplay; ?>></i>&nbsp;<?php echo $hesklang['kb_text'] ?>
                        </a></li> <?php }
                $active = '';
                if (defined('PAGE_TITLE') && PAGE_TITLE == 'CUSTOMER_CALENDAR') {
                    $active = ' active';
                }
                if ($modsForHesk_settings['enable_calendar'] == 1 && !defined('MAINTENANCE_MODE')):
                ?>
                <li class="<?php echo $active; ?>">
                    <a href="<?php echo HESK_PATH; ?>calendar.php"><i class="fa fa-calendar" <?php echo $iconDisplay; ?>></i>&nbsp;<?php echo $hesklang['calendar_title_case']; ?></a>
                </li>
                <?php endif; ?>
                <?php include('custom/header-custom.inc.php'); ?>
            </ul>
            <?php if ($hesk_settings['can_sel_lang']) { ?>
                <div class="navbar-form navbar-right" role="search" style="margin-right: 20px; min-width: 80px;">
                    <?php
                    if (!defined('MAINTENANCE_MODE')) {
                        if (defined('PAGE_TITLE') && PAGE_TITLE == 'CUSTOMER_TICKET') {
                            hesk_getLanguagesAsFormIfNecessary($trackingID);
                        } else {
                            hesk_getLanguagesAsFormIfNecessary();
                        }
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    </nav>