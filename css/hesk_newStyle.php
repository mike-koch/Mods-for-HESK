<?php
    header("Content-type: text/css; charset: UTF-8");
    require_once('../nuMods_settings.inc.php');

    $navbarBackgroundColor = $nuMods_settings['navbarBackgroundColor'];
    $navbarBrandColor = $nuMods_settings['navbarBrandColor'];
    $navbarBrandHoverColor = $nuMods_settings['navbarBrandHoverColor'];

    $navbarItemTextColor = $nuMods_settings['navbarItemTextColor'];
    $navbarItemTextHoverColor = $nuMods_settings['navbarItemTextHoverColor'];
    $navbarItemTextSelectedColor = $nuMods_settings['navbarItemTextSelectedColor'];
    $navbarItemSelectedBackgroundColor = $nuMods_settings['navbarItemSelectedBackgroundColor'];

    $dropdownItemTextColor = $nuMods_settings['dropdownItemTextColor'];
    $dropdownItemTextHoverColor = $nuMods_settings['dropdownItemTextHoverColor'];
    $dropdownItemTextHoverBackgroundColor = $nuMods_settings['dropdownItemTextHoverBackgroundColor'];

    $questionMarkColor = $nuMods_settings['questionMarkColor'];
?>

.nu-rtlFloatLeft {
    /* Don't do anything; the pager looks good already */
}

.nu-floatRight {
    float: right;
}

.tabPadding {
    padding: 10px;
}

@media (max-width:991px) {
    .close-ticket {
        text-align: left;
    }
}

@media (min-width:992px) {
    .close-ticket {
        text-align: right;
        padding-bottom: 5px;
    }
}


@media (max-width:991px) {
    .ticket-cell {
        border-bottom: solid 1px #ddd;
        border-right: 0;
        padding-top: 5px;
    }
}

@media (max-width:991px) {
    .ticket-cell-admin {
        border-bottom: solid 1px #ddd;
        border-right: 0;
        padding-top: 5px;
        height: 100px;
    }
}

@media (min-width:992px) {
    .ticket-cell {
        border-bottom: 0;
        border-right: solid 1px #ddd;
        margin-top: 1px;
        min-height: 70px;
        padding-top: 10px;
    }
}

@media (min-width:992px) {
    .ticket-cell-admin {
        border-bottom: 0;
        border-right: solid 1px #ddd;
        margin-top: 1px;
        height: 100px;
        padding-top: 10px;
    }    
}

.row {
    margin-left: 0px;
    margin-right: 0px;
}

.navbar {
    margin-bottom: 0;
}
.navbar-default {
    background-color: <?php echo $navbarBackgroundColor; ?>;
    background-image: none;
    filter: none;
}
.navbar-default .navbar-brand {
    color: <?php echo $navbarBrandColor; ?>;
}
.navbar-default .navbar-brand:focus, .navbar-default .navbar-brand:hover {
    color: <?php echo $navbarBrandHoverColor; ?>;
    background-color: transparent;
}
.navbar-default .navbar-nav>li>a {
    color: <?php echo $navbarItemTextColor; ?>;
}
.navbar-default .navbar-nav>li>a:focus, .navbar-default .navbar-nav>li>a:hover {
    color: <?php echo $navbarItemTextHoverColor; ?>;
    background-color: transparent;
}
.dropdown-menu>li>a {
    color: <?php echo $dropdownItemTextColor; ?>;
}
.dropdown-menu>li>a:focus, .dropdown-menu>li>a:hover {
    color: <?php echo $dropdownItemTextHoverColor; ?>;
    text-decoration: none;
    background-color: <?php echo $dropdownItemTextHoverBackgroundColor; ?>;
}
.navbar-default .navbar-nav>.open>a, .navbar-default .navbar-nav>.open>a:focus, .navbar-default .navbar-nav>.open>a:hover {
    color: <?php echo $navbarItemTextSelectedColor; ?>;
    background-color: <?php echo $navbarItemSelectedBackgroundColor; ?>;
}
.settingsquestionmark {
    color: <?php echo $questionMarkColor; ?>;
    font-size: 14px;
}
.h3questionmark {
    color: <?php echo $questionMarkColor; ?>;
    font-size: 14px;
}
.h3questionmark:hover {
    text-decoration: underline;
}
.form-signin {
    max-width: 330px;
    padding: 15px;
    margin: 0 auto;
}
.loginError {
    width: 40%;
    padding: 20px;
    margin-left: auto;
    margin-right: auto;
}
.kbContent {
    padding-top: 10px;
    text-align: left;
}
.withBorder {
    border-bottom: 1px solid #ddd;
}
.ticketMessageContainer {
    background-color: #ededef;
    border: 1px solid #ddd;
    margin-bottom: 20px;
}
.ticketName {
    font-size: 20px;
    font-weight: 300;
    color: #000;
    margin-top: 5px;
}
.ticketEmail {
    font-size: 14px;
    color: #888;
}
.ticketMessageTop {
    padding-top: 10px;
    padding-left: 10px;
    padding-right: 10px;
    margin-right: -15px;
    color: #888;
    background-color: #fff;
}

.pushMargin {
    margin-top: -10px;
    margin-bottom: -10px;
}

.pushMarginLeft {
    margin-left: -15px;
    margin-right: -15px;
    padding-right: 0;
}

.ticketMessageBottom {
    padding-left: 10px;
    padding-right: 10px;
    margin-right: -15px;
    word-wrap: break-word;
    font-size: 15px;
    background-color: #fff;
}
.ticketMessageBottom > p.message {
    margin-bottom: 0px;

}

.ticketMessage {
    margin-left: 238px;
    background: #fff;
    height: 100%;
    position: relative;
}
.ticketPropertyTitle {
    color: rgba(255, 255, 255, .75);
    font-size: 11px;
    text-transform: uppercase;
}

@media (min-width: 992px) {
    .ticketPropertyText {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ticketPropertyText:hover {
        white-space: normal;
        overflow: none;
    }
}

.ticketPropertyText {
    font-size: 16px;
    line-height: 1em;
    color: #fff;
    padding-bottom: 2px;
}

.criticalPriority {
    background-color: red;
}
.highPriority {
    background-color: #ff6a00;
}
.medLowPriority {
    background-color: #8BB467;
}
div.blankSpace {
    padding-top: 20px;
}
div.footerWithBorder {
    border-top: 1px solid #cfd4d6;
}

.blockRow > a:hover {
    text-decoration: none;
}

.block {
    height: 114px;
    width: 114px;
    display: inline-block;
    border: 1px solid #c9cfd7;
    background-color: #fff;
    border-radius: 4px;
    font-size: .83em;
    margin-right: 5px;
    vertical-align: top;
}

.block > .upper {
    height:57px;
    padding-top:10px;
}

.block > .upper > img {
    padding-top:5px;
    padding-bottom:5px;
    padding-left:41px;
}

.block > .lower {
    height:57px;
    text-align:center;
}

.block > .lower > p:hover {
    text-decoration: underline;
}

div.block:hover {
    background-color: #e9ecef;
}
div.rightSideDash {
    padding-left: 18px;
    padding-right: 18px;
}
div.enclosing {
    background-color: #fff;
    color: #4a5571;
    font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
    font-size: 12px;
    width: 100%}
div.headersm {
    width: 100%;
    color: #fff;
    font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
    font-size: 12px;
    text-align: left;
    background-color: #424b5c;
    background-repeat: repeat-x;
    padding: 12px 20px 8px;
    margin: 0;
    font-weight: 700;
    padding-left: 20px;
}
div.installWarning {
    width: 70%;
    height: 52px;
    margin-top: 10px;
    margin-left: auto;
    margin-right: auto;
}
div.setupContainer {
    margin: 50px;
    text-align: center;
}
div.setupLogo {
    vertical-align: middle;
    border: 0;
    margin-top: -2px;
}
div.setupButtons {
    text-decoration: none;
    border: 4px solid #eee;
    background: #fff;
    border-radius: 5px;
    color: #61718c;
    -webkit-box-shadow: rgba(0, 0, 0, .1)0 0 3px;
    -moz-box-shadow: rgba(0, 0, 0, .1)0 0 3px;
    text-align: center;
    margin: 20px 0;
    padding: 10px 0;
}
.agreementBox {
    position: relative;
    background-color: #fff;
    overflow: auto;
    padding: 20px;
    display: block;
    height: 206px;
    box-shadow: inset 0 0 4px #bbb, inset 0 0 20px #eee;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
}
.summaryList {
    border-style: solid;
    border-width: 1px;
    border-color: #ddd;
    border-top-color: transparent;
}
.installRequirements {
    margin-left: auto;
    margin-right: auto;
    width: 90%;
}
