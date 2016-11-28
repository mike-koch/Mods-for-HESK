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

define('IN_SCRIPT',1);
define('HESK_PATH','../');
define('MFH_PAGE_LAYOUT', 'TOP_ONLY');
define('PAGE_TITLE', 'ADMIN_CUSTOM_FIELDS');

define('LOAD_TABS',1);
define('CALENDAR',1);

// Get all the req files and functions
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(HESK_PATH . 'inc/admin_functions.inc.php');
require(HESK_PATH . 'inc/setup_functions.inc.php');
require(HESK_PATH . 'inc/mail_functions.inc.php');
hesk_load_database_functions();

hesk_session_start();
hesk_dbConnect();
hesk_isLoggedIn();

// Check permissions for this feature
hesk_checkPermission('can_man_settings');

// Load custom fields
require_once(HESK_PATH . 'inc/custom_fields.inc.php');

// List of categories
$hesk_settings['categories'] = array();
$res = hesk_dbQuery("SELECT `id`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` ORDER BY `cat_order` ASC");
while ($row=hesk_dbFetchAssoc($res))
{
	$hesk_settings['categories'][$row['id']] = $row['name'];
}

// What should we do?
if ( $action = hesk_REQUEST('a') )
{
	if ($action == 'edit_cf') {edit_cf();}
	elseif ( defined('HESK_DEMO') ) {hesk_process_messages($hesklang['ddemo'], 'custom_fields.php', 'NOTICE');}
	elseif ($action == 'new_cf') {new_cf();}
	elseif ($action == 'save_cf') {save_cf();}
	elseif ($action == 'order_cf') {order_cf();}
	elseif ($action == 'remove_cf') {remove_cf();}
}

// Print header
require_once(HESK_PATH . 'inc/headerAdmin.inc.php');

// Print main manage users page
require_once(HESK_PATH . 'inc/show_admin_nav.inc.php');
?>
<div class="content-wrapper">
	<section class="content">
	<?php
	/* This will handle error, success and notice messages */
	hesk_handle_messages();
	?>
	<div class="box">
		<div class="box-body">
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs" role="tablist">
					<?php
					// Show a link to banned_emails.php if user has permission to do so
					if (hesk_checkPermission('can_ban_emails', 0)) {
						echo '
            <li role="presentation">
                <a title="' . $hesklang['banemail'] . '" href="banned_emails.php">' . $hesklang['banemail'] . '</a>
            </li>';
					}

					// Show a link to banned_ips.php if user has permission to do so
					if (hesk_checkPermission('can_ban_ips', 0)) {
						echo '
						<li role="presentation">
							<a title="' . $hesklang['banip'] . '" href="banned_ips.php">' . $hesklang['banip'] . '</a>
						</li>';
					}
					// Show a link to status_message.php if user has permission to do so
					if (hesk_checkPermission('can_service_msg', 0)) {
						echo '
						<li role="presentation">
							<a title="' . $hesklang['sm_title'] . '" href="service_messages.php">' . $hesklang['sm_title'] . '</a>
						</li>';
					}

					// Show a link to email tpl management if user has permission to do so
					if (hesk_checkPermission('can_man_email_tpl', 0)) {
						echo '
						<li role="presentation">
							<a title="' . $hesklang['email_templates'] . '" href="manage_email_templates.php">' . $hesklang['email_templates'] . '</a>
						</li>
						';
					}
					if (hesk_checkPermission('can_man_ticket_statuses', 0)) {
						echo '
						<li role="presentation">
							<a title="' . $hesklang['statuses'] . '" href="manage_statuses.php">' . $hesklang['statuses'] . '</a>
						</li>
						';
					}
					?>
					<li role="presentation" class="active">
						<a title="<?php echo $hesklang['tab_4']; ?>" href="custom_fields.php">
							<?php echo $hesklang['tab_4']; ?>
							<i class="fa fa-question-circle settingsquestionmark"
							   onclick="alert('<?php echo hesk_makeJsString($hesklang['cf_intro']); ?>')"></i>
						</a>
					</li>
				</ul>
				<div class="tab-content summaryList tabPadding">
					<?php
					// Did we reach the custom fields limit?
					if ($hesk_settings['num_custom_fields'] >= 50 && $action != 'edit_cf')
					{
						hesk_show_info($hesklang['cf_limit']);
					}
					// Less than 50 custom fields
					else
					{
					?>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h4><a name="new_cf"></a><?php echo hesk_SESSION('edit_cf') ? $hesklang['edit_cf'] : $hesklang['new_cf']; ?></h4>
							</div>
							<div class="panel-body">
								<form action="custom_fields.php" method="post" name="form1" class="form-horizontal">
									<div class="form-group">
										<label for="name[]" class="col-sm-3 control-label">
											<?php echo $hesklang['custom_n']; ?>
										</label>
										<?php
										$names = hesk_SESSION(array('new_cf','names'));

										if ($hesk_settings['can_sel_lang'] && count($hesk_settings['languages']) > 1)
										{
											?>
											<table border="0">
												<?php foreach ($hesk_settings['languages'] as $lang => $info): ?>
												<tr>
												<td><?php echo $lang; ?></td>
												<td><input type="text" class="form-control" name="name[<?php echo $lang; ?>]" size="30" value="<?php echo (isset($names[$lang]) ? $names[$lang] : ''); ?>"></td>
												</tr>
												<?php endforeach; ?>
											</table>
											<?php

										}
										else
										{
											?>
											<div class="col-sm-9">
												<input type="text" class="form-control" name="name[<?php echo $hesk_settings['language']; ?>]" size="30" value="<?php echo isset($names[$hesk_settings['language']]) ? $names[$hesk_settings['language']] : ''; ?>" />
											</div>
											<?php
										}
										?>
									</div>
									<div class="form-group">
										<label for="name[]" class="col-sm-3 control-label">
											<?php echo $hesklang['s_type']; ?>
										</label>
										<div class="col-sm-9">
											<select name="type" class="form-control" onchange="hesk_setType(this.value);">
												<?php $type = hesk_SESSION(array('new_cf','type'), 'text'); ?>
												<option value="text"     <?php if ($type == 'text') {echo 'selected';} ?> ><?php echo $hesklang['stf']; ?></option>
												<option value="textarea" <?php if ($type == 'textarea') {echo 'selected';} ?> ><?php echo $hesklang['stb']; ?></option>
												<option value="radio"    <?php if ($type == 'radio') {echo 'selected';} ?> ><?php echo $hesklang['srb']; ?></option>
												<option value="select"   <?php if ($type == 'select') {echo 'selected';} ?> ><?php echo $hesklang['ssb']; ?></option>
												<option value="checkbox" <?php if ($type == 'checkbox') {echo 'selected';} ?> ><?php echo $hesklang['scb']; ?></option>
												<option value="date"     <?php if ($type == 'date') {echo 'selected';} ?> ><?php echo $hesklang['date']; ?></option>
												<option value="email"    <?php if ($type == 'email') {echo 'selected';} ?> ><?php echo $hesklang['email']; ?></option>
												<option value="readonly"    <?php if ($type == 'readonly') {echo 'selected';} ?> ><?php echo $hesklang['readonly_custom_field']; ?></option>
												<option value="hidden"   <?php if ($type == 'hidden') {echo 'selected';} ?> ><?php echo $hesklang['sch']; ?></option>
											</select><br>
											<?php
											$value = hesk_SESSION(array('new_cf','value'));

											if (is_string($value))
											{
												$value = json_decode($value, true);
											}
											?>

											<div id="text" style="display:<?php echo ($type == 'text') ? 'block' : 'none' ?>">
												<div class="form-group">
													<label class="col-sm-3 control-label" for="max_length">
														<?php echo $hesklang['custom_l']; ?>
													</label>
													<div class="col-sm-3">
														<input type="text" class="form-control" name="max_length"
															   value="<?php echo isset($value['max_length']) ? intval($value['max_length']) : '255'; ?>" size="5">
													</div>
												</div>
												<div class="form-group">
													<label class="col-sm-3 control-label" for="default_value">
														<?php echo $hesklang['defw']; ?>
													</label>
													<div class="col-sm-3">
														<input type="text" class="form-control" name="default_value"
															   value="<?php echo isset($value['default_value']) ? $value['default_value'] : ''; ?>" size="30">
													</div>
												</div>
											</div>


											<div id="readonly" style="display:<?php echo ($type == 'readonly') ? 'block' : 'none' ?>">
												<div class="form-group">
													<label class="col-sm-3 control-label" for="max_length">
														<?php echo $hesklang['custom_l']; ?>
													</label>
													<div class="col-sm-3">
														<input type="text" class="form-control" name="max_length"
															   value="<?php echo isset($value['max_length']) ? intval($value['max_length']) : '255'; ?>" size="5">
													</div>
												</div>
												<div class="form-group">
													<label class="col-sm-3 control-label" for="value">
														<?php echo $hesklang['defw']; ?>
													</label>
													<div class="col-sm-3">
														<input type="text" class="form-control" name="default_value"
															   value="<?php echo isset($value['default_value']) ? $value['default_value'] : ''; ?>" size="30">
													</div>
												</div>
											</div>

											<div id="textarea" style="display:<?php echo ($type == 'textarea') ? 'block' : 'none' ?>">
												<div class="form-group">
													<label class="col-sm-3 control-label" for="rows">
														<?php echo $hesklang['rows']; ?>
													</label>
													<div class="col-sm-3">
														<input type="text" class="form-control" name="rows"
															   value="<?php echo isset($value['rows']) ? intval($value['rows']) : '12'; ?>" size="5">
													</div>
												</div>
												<div class="form-group">
													<label class="col-sm-3 control-label" for="cols">
														<?php echo $hesklang['cols']; ?>
													</label>
													<div class="col-sm-3">
														<input type="text" class="form-control" name="cols"
															   value="<?php echo isset($value['cols']) ? intval($value['cols']) : '60'; ?>" size="5">
													</div>
												</div>
											</div>

											<div id="radio" style="display:<?php echo ($type == 'radio') ? 'block' : 'none' ?>">
												<p><?php echo $hesklang['opt2']; ?></p>
												<p><label><input type="checkbox" name="no_default" id="no_default" value="1" <?php if ( ! empty($value['no_default'])) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['rcheck']; ?> </label></p>
												<textarea class="form-control" name="radio_options" rows="6" cols="40"><?php echo (isset($value['radio_options']) && is_array($value['radio_options'])) ? implode("\n", $value['radio_options']) : ''; ?></textarea>
											</div>

											<div id="select" style="display:<?php echo ($type == 'select') ? 'block' : 'none' ?>">
												<p><?php echo $hesklang['opt3']; ?></p>
												<p><label><input type="checkbox" name="show_select" id="show_select" value="1" <?php if ( ! empty($value['show_select'])) {echo 'checked="checked"';} ?> /> <?php echo $hesklang['show_select']; ?> </label></p>
												<textarea class="form-control" name="select_options" rows="6" cols="40"><?php echo isset($value['select_options']) && is_array($value['select_options']) ? implode("\n", $value['select_options']) : ''; ?></textarea>
											</div>

											<div id="checkbox" style="display:<?php echo ($type == 'checkbox') ? 'block' : 'none' ?>">
												<p><?php echo $hesklang['opt4']; ?></p>
												<textarea class="form-control" name="checkbox_options" rows="6" cols="40"><?php echo isset($value['checkbox_options']) && is_array($value['checkbox_options']) ? implode("\n", $value['checkbox_options']) : ''; ?></textarea>
											</div>

											<div id="date" style="display:<?php echo ($type == 'date') ? 'block' : 'none' ?>">
												<div class="form-group">
													<label class="col-sm-3 control-label">
														<?php echo $hesklang['dmin']; ?>
													</label>
													<div class="col-sm-9">
														<?php
														$dmin = isset($value['dmin']) ? $value['dmin'] : '';

														// Defaults
														$dmin_pm = '+';
														$dmin_num = 1;
														$dmin_type = 'day';

														// Minimum date is in "+1 day" format
														if (preg_match("/^([+-]{1})(\d+) (day|week|month|year)$/", $dmin, $matches))
														{
															$dmin = '';
															$dmin_rf = 2;
															$dmin_pm = $matches[1];
															$dmin_num = $matches[2];
															$dmin_type = $matches[3];
														}
														// Minimum date is in "MM/DD/YYYY" format
														elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dmin))
														{
															$dmin_rf = 1;
														}
														else
														{
															$dmin = '';
															$dmin_rf = 0;
														}
														?>

														<div class="form-inline">
															<div class="radio">
																<label>
																	<input type="radio" name="dmin_rf" id="dmin_rf0" value="0"
																		<?php if ($dmin_rf == 0) {echo 'checked';} ?>> <?php echo $hesklang['d_any']; ?>
																</label>
															</div>
														</div>
														<div class="form-inline">
															<div class="radio">
																<label>
																	<input type="radio" name="dmin_rf" id="dmin_rf1" value="1"
																		<?php if ($dmin_rf == 1) {echo 'checked';} ?>> <?php echo $hesklang['d_fixed']; ?>
																</label>
															</div>
															<input type="text" name="dmin" value="<?php echo $dmin; ?>" id="dmin" class="form-control datepicker" size="10" onfocus="document.getElementById('dmin_rf1').checked = true">
														</div>
														<div class="form-inline">
															<div class="radio">
																<label>
																	<input type="radio" name="dmin_rf" id="dmin_rf2" value="2"
																		<?php if ($dmin_rf == 2) {echo 'checked';} ?>> <?php echo $hesklang['d_relative']; ?>
																</label>
															</div>
															<select name="dmin_pm" class="form-control" onclick="document.getElementById('dmin_rf2').checked = true" onchange="document.getElementById('dmin_rf2').checked = true">
																<option <?php if ($dmin_pm == '+') {echo 'selected';} ?>>+</option>
																<option <?php if ($dmin_pm == '-') {echo 'selected';} ?>>-</option>
															</select>
															<input type="text" class="form-control" name="dmin_num" value="<?php echo $dmin_num; ?>" size="5" onclick="document.getElementById('dmin_rf2').checked = true" onchange="document.getElementById('dmin_rf2').checked = true">
															<select class="form-control" name="dmin_type" onclick="document.getElementById('dmin_rf2').checked = true" onchange="document.getElementById('dmin_rf2').checked = true">
																<option value="day"   <?php if ($dmin_type == 'day') {echo 'selected';} ?>><?php echo $hesklang['d_day']; ?></option>
																<option value="week"  <?php if ($dmin_type == 'week') {echo 'selected';} ?>><?php echo $hesklang['d_week']; ?></option>
																<option value="month" <?php if ($dmin_type == 'month') {echo 'selected';} ?>><?php echo $hesklang['d_month']; ?></option>
																<option value="year"  <?php if ($dmin_type == 'year') {echo 'selected';} ?>><?php echo $hesklang['d_year']; ?></option>
															</select>
														</div>
													</div>
												</div>
												<div class="form-group">
													<label class="col-sm-3 control-label">
														<?php echo $hesklang['dmax']; ?>
													</label>
													<div class="col-sm-9">
														<?php
														$dmax = isset($value['dmax']) ? $value['dmax'] : '';

														// Defaults
														$dmax_pm = '+';
														$dmax_num = 1;
														$dmax_type = 'day';

														// Minimum date is in "+1 day" format
														if (preg_match("/^([+-]{1})(\d+) (day|week|month|year)$/", $dmax, $matches))
														{
															$dmax = '';
															$dmax_rf = 2;
															$dmax_pm = $matches[1];
															$dmax_num = $matches[2];
															$dmax_type = $matches[3];
														}
														// Minimum date is in "MM/DD/YYYY" format
														elseif (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dmax))
														{
															$dmax_rf = 1;
														}
														else
														{
															$dmax = '';
															$dmax_rf = 0;
														}
														?>

														<div class="form-inline">
															<div class="radio">
																<label>
																	<input type="radio" name="dmax_rf" id="dmax_rf0" value="0"
																		<?php if ($dmax_rf == 0) {echo 'checked';} ?>> <?php echo $hesklang['d_any']; ?>
																</label>
															</div>
														</div>
														<div class="form-inline">
															<div class="radio">
																<label>
																	<input type="radio" name="dmax_rf" id="dmax_rf1" value="1"
																		<?php if ($dmax_rf == 1) {echo 'checked';} ?>> <?php echo $hesklang['d_fixed']; ?>
																</label>
															</div>
															<input type="text" class="form-control datepicker" name="dmax" value="<?php echo $dmax; ?>" id="dmax" size="10" onfocus="document.getElementById('dmax_rf1').checked = true">
														</div>
														<div class="form-inline">
															<div class="radio">
																<label>
																	<input type="radio" name="dmax_rf" id="dmax_rf2" value="2"
																		<?php if ($dmax_rf == 2) {echo 'checked';} ?>> <?php echo $hesklang['d_relative']; ?>
																</label>
															</div>
															<select name="dmax_pm" class="form-control" onclick="document.getElementById('dmax_rf2').checked = true" onchange="document.getElementById('dmax_rf2').checked = true">
																<option <?php if ($dmax_pm == '+') {echo 'selected="selected"';} ?>>+</option>
																<option <?php if ($dmax_pm == '-') {echo 'selected="selected"';} ?>>-</option>
															</select>
															<input type="text" name="dmax_num" class="form-control" value="<?php echo $dmax_num; ?>" size="5" onclick="document.getElementById('dmax_rf2').checked = true" onchange="document.getElementById('dmax_rf2').checked = true">
															<select name="dmax_type" class="form-control" onclick="document.getElementById('dmax_rf2').checked = true" onchange="document.getElementById('dmax_rf2').checked = true">
																<option value="day"   <?php if ($dmax_type == 'day') {echo 'selected';} ?>><?php echo $hesklang['d_day']; ?></option>
																<option value="week"  <?php if ($dmax_type == 'week') {echo 'selected';} ?>><?php echo $hesklang['d_week']; ?></option>
																<option value="month" <?php if ($dmax_type == 'month') {echo 'selected';} ?>><?php echo $hesklang['d_month']; ?></option>
																<option value="year"  <?php if ($dmax_type == 'year') {echo 'selected';} ?>><?php echo $hesklang['d_year']; ?></option>
															</select>
														</div>
													</div>
												</div>
												<div class="form-group">
													<label class="col-sm-3 control-label">
														<?php echo $hesklang['d_format']; ?>
													</label>
													<div class="col-sm-9">
														<?php
														$date_format = isset($value['date_format']) ? $value['date_format'] : 'F j, Y';

														$default_formats = array(
															'm/d/Y',
															'd/m/Y',
															'm-d-Y',
															'd-m-Y',
															'd.m.Y',
															'M j Y',
															'j M Y',
															'j M y',
															'F j, Y',
														);

														$time = mktime(0, 0, 0, 12, 30, date('Y'));

														foreach ($default_formats as $format)
														{
															echo '<div class="form-inline"><div class="radio"><label><input type="radio" name="date_format" value="'.$format.'" '.($date_format == $format ? 'checked="checked"' : '').' /> '.date($format, $time).'</label></div></div>';
														}

														?>
														<div class="form-inline">
															<div class="radio">
																<label>
																	<input type="radio" name="date_format" value="custom" id="d_custom"
																		<?php if (!in_array($date_format, $default_formats)) {echo 'checked';} ?>>
																	<?php echo $hesklang['d_custom']; ?>
																</label>
															</div>
															<input type="text" class="form-control" name="date_format_custom"
																   value="<?php echo $date_format; ?>" size="10" onclick="document.getElementById('d_custom').checked = true"
																   onchange="document.getElementById('d_custom').checked = true">
															<a href="javascript:;" onclick="alert('<?php echo hesk_makeJsString($hesklang['d_ci']); ?>')">
																<i class="fa fa-question-circle settingsquestionmark"></i>
															</a>
														</div>
													</div>
												</div>
											</div>

											<div id="email" style="display:<?php echo ($type == 'email') ? 'block' : 'none' ?>">
												<div class="form-group">
													<label for="email_multi" class="col-sm-4 control-label">
														<?php echo $hesklang['meml3']; ?>
													</label>
													<div class="col-sm-8">
														<?php $email_multi = empty($value['multiple']) ? 0 : 1; ?>
														<div class="radio">
															<label>
																<input type="radio" name="email_multi" id="email_multi0" value="0"
																	<?php if ($email_multi == 0) {echo 'checked';} ?>>
																<?php echo $hesklang['no']; ?>
															</label>
														</div>
														<div class="radio">
															<label>
																<input type="radio" name="email_multi" id="email_multi1" value="1"
																	<?php if ($email_multi == 1) {echo 'checked';} ?>>
																<?php echo $hesklang['yes']; ?>
															</label>
														</div>
													</div>
												</div>
												<div class="form-group">
													<label for="email_type" class="col-sm-4 control-label">
														<?php echo $hesklang['email_custom_field_label']; ?>
													</label>
													<div class="col-sm-8">
														<?php $address_type = empty($value['email_type']) ? 'none' : $value['email_type']; ?>
														<div class="radio">
															<label>
																<input type="radio" name="email_type" value="none"
																	<?php if ($address_type == 'none') {echo 'checked';} ?>>
																<?php echo $hesklang['none']; ?>
															</label>
														</div>
														<div class="radio">
															<label>
																<input type="radio" name="email_type" value="cc"
																	<?php if ($address_type == 'cc') {echo 'checked';} ?>>
																<?php echo $hesklang['cc']; ?>
															</label>
														</div>
														<div class="radio">
															<label>
																<input type="radio" name="email_type" value="bcc"
																	<?php if ($address_type == 'bcc') {echo 'checked';} ?>>
																<?php echo $hesklang['bcc']; ?>
															</label>
														</div>
													</div>
												</div>
											</div>

											<div id="hidden" style="display:<?php echo ($type == 'hidden') ? 'block' : 'none' ?>">
												<p><?php echo $hesklang['hidf']; ?></p>
												<div class="form-group">
													<label class="col-sm-3 control-label" for="hidden_max_length">
														<?php echo $hesklang['custom_l']; ?>
													</label>
													<div class="col-sm-3">
														<input type="text" class="form-control" name="hidden_max_length"
															   value="<?php echo isset($value['max_length']) ? intval($value['max_length']) : '255'; ?>" size="5">
													</div>
												</div>
												<div class="form-group">
													<label class="col-sm-3 control-label" for="hidden_default_value">
														<?php echo $hesklang['defw']; ?>
													</label>
													<div class="col-sm-3">
														<input type="text" class="form-control" name="hidden_default_value"
															   value="<?php echo isset($value['default_value']) ? $value['default_value'] : ''; ?>" size="30">
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label" for="use">
											<?php echo $hesklang['visibility']; ?>
										</label>
										<div class="col-sm-9">
											<?php $use = hesk_SESSION(array('new_cf','use'), 1); ?>
											<div class="radio">
												<label>
													<input type="radio" name="use" id="use1" value="1"
														   onchange="hesk_setRadioOptions();" <?php if ($use == 1) {echo 'checked';} ?>>
													<?php echo $hesklang['cf_public']; ?>
												</label>
											</div>
											<div class="radio">
												<label>
													<input type="radio" name="use" id="use2" value="2"
														   onchange="hesk_setRadioOptions();" <?php if ($use == 2) {echo 'checked';} ?>>
													<?php echo $hesklang['cf_private']; ?>
												</label>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label for="req" class="col-sm-3 control-label">
											<?php echo $hesklang['custom_r']; ?>
										</label>
										<div class="col-sm-9">
											<?php $req = hesk_SESSION(array('new_cf','req'), 0); ?>
											<div class="radio">
												<label>
													<input type="radio" name="req" id="req0" value="0"
														<?php if ($req == 0) {echo 'checked';} ?>>
													<?php echo $hesklang['no']; ?>
												</label>
											</div>
											<div class="radio">
												<label>
													<input type="radio" name="req" id="req2" value="2"
														<?php if ($req == 2) {echo 'checked';} ?>>
													<?php echo $hesklang['yes']; ?>
												</label>
											</div>
											<div class="radio">
												<label id="req_customers" style="display:<?php echo ($use == 2) ? 'none' : 'inline'; ?>">
													<input type="radio" name="req" id="req1" value="1"
														<?php if ($req == 1) {echo 'checked';} ?>>
													<?php echo $hesklang['cf_cust']; ?>
												</label>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label for="place" class="col-sm-3 control-label">
											<?php echo $hesklang['custom_place']; ?>
										</label>
										<div class="col-sm-9">
											<?php $place = hesk_SESSION(array('new_cf','place')) ? 1 : 0; ?>
											<div class="radio">
												<label>
													<input type="radio" name="place" value="0"
														<?php if ($place == 0) {echo 'checked';} ?>>
													<?php echo $hesklang['place_before']; ?>
												</label>
											</div>
											<div class="radio">
												<label>
													<input type="radio" name="place" value="1"
														<?php if ($place == 1) {echo 'checked';} ?>>
													<?php echo $hesklang['place_after']; ?>
												</label>
											</div>
										</div>
									</div>
									<div class="form-group">
										<label for="category" class="col-sm-3 control-label">
											<?php echo $hesklang['category']; ?>
										</label>
										<div class="col-sm-9">
											<?php $category = hesk_SESSION(array('new_cf','category')) ? 1 : 0; ?>
											<div class="radio">
												<label>
													<input type="radio" name="category" id="category0" value="0"
														   onchange="hesk_setRadioOptions();" <?php if ($category == 0) {echo 'checked';} ?>>
													<?php echo $hesklang['cf_all']; ?>
												</label>
											</div>
											<div class="radio">
												<label>
													<input type="radio" name="category" id="category1" value="1"
														   onchange="hesk_setRadioOptions();" <?php if ($category == 1) {echo 'checked';} ?>>
													<?php echo $hesklang['cf_cat']; ?>
												</label>
											</div>
											<div id="selcat" style="display:<?php echo $category ? 'block' : 'none'; ?>">
												<select class="form-control" name="categories[]" multiple="multiple" size="10">
													<?php
													$categories = hesk_SESSION(array('new_cf','categories'));
													$categories = is_array($categories) ? $categories : array();

													foreach ($hesk_settings['categories'] as $cat_id => $cat_name)
													{
														echo '<option value="'.$cat_id.'"'.(in_array($cat_id, $categories) ? ' selected' : '').'>'.$cat_name.'</option>';
													}
													?>
												</select>
												<?php echo $hesklang['cf_ctrl']; ?>
											</div>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-9 col-sm-offset-3">
											<?php echo isset($_SESSION['edit_cf']) ? '<input type="hidden" name="a" value="save_cf" /><input type="hidden" name="id" value="'.intval($_SESSION['new_cf']['id']).'" />' : '<input type="hidden" name="a" value="new_cf" />'; ?>
											<input type="hidden" name="token" value="<?php hesk_token_echo(); ?>" />
											<input type="submit" value="<?php echo $hesklang['cf_save']; ?>" class="btn btn-default">
										</div>
									</div>
								</form>
							</div>
						</div>
					<?php } ?>
					<script language="javascript" type="text/javascript"><!--
						function hesk_toggleLayer(nr,setto) {
							if (document.all)
								document.all[nr].style.display = setto;
							else if (document.getElementById)
								document.getElementById(nr).style.display = setto;
						}

						function hesk_setType(myType) {
							var divs = ["text", "textarea", "radio", "select", "checkbox", "date", "email", "hidden", "readonly"];
							var index;
							var setTo;

							for (index = 0; index < divs.length; ++index) {
								setTo = (myType == divs[index] + "") ? 'block' : 'none';
								hesk_toggleLayer(divs[index], setTo);
							}
						}

						function hesk_setRadioOptions() {
							if(document.getElementById('use1').checked) {
								hesk_toggleLayer('req_customers', 'inline');
							} else {
								hesk_toggleLayer('req_customers', 'none');
								if(document.getElementById('req1').checked) {
									document.getElementById('req0').checked = true;
								}
							}

							if(document.getElementById('category1').checked) {
								hesk_toggleLayer('selcat', 'block');
							} else {
								hesk_toggleLayer('selcat', 'none');
							}
						}
						//-->
					</script>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4><?php echo $hesklang['ex_cf']; ?></h4>
						</div>
						<div class="panel-body">
							<?php
							// List existing custom fields
							if ($hesk_settings['num_custom_fields'] < 1) {
								echo $hesklang['no_cf'];
							} else {
							?>
								<table border="0" cellspacing="1" cellpadding="3" class="table table-striped" width="100%">
									<thead>
									<tr>
										<th><?php echo $hesklang['id']; ?></th>
										<th><?php echo $hesklang['custom_n']; ?></th>
										<th><?php echo $hesklang['s_type']; ?></th>
										<th><?php echo $hesklang['visibility']; ?></th>
										<th><?php echo $hesklang['custom_r']; ?></th>
										<th><?php echo $hesklang['category']; ?></th>
										<th><?php echo $hesklang['opt']; ?></th>
									</tr>
									</thead>
									<tbody>
									<?php
									$before = true;
									$after = true;
									$hide_up = false;
									$num_before = 0;
									$num_after = 0;

									foreach ($hesk_settings['custom_fields'] as $id => $cf) {
										if ($cf['place']) {
											$num_after++;
										} else {
											$num_before++;
										}
									}

									$k = 1;
									foreach ($hesk_settings['custom_fields'] as $id => $cf) {
										$id = intval(str_replace('custom', '', $id));

										if ($hide_up) {
											$hide_up = false;
										}

										if ($before && $cf['place'] == 0) {
											?>
											<tr>
												<td colspan="7"><b><i><?php echo $hesklang['place_before']; ?></i></b></td>
											</tr>
											<?php
											$before = false;
										} elseif ($after && $cf['place'] == 1) {
											?>
											<tr>
												<td colspan="7"><b><i><?php echo $hesklang['place_after']; ?></i></b></td>
											</tr>
											<?php
											$after = false;
											$hide_up = true;
										}

										$cf['type'] = hesk_custom_field_type($cf['type']);
										$cf['use'] = ($cf['use'] == 1) ? $hesklang['cf_public'] : $hesklang['cf_private'];
										$cf['req'] = ($cf['req'] == 0) ? $hesklang['no'] : ($cf['req'] == 2 ? $hesklang['yes'] : $hesklang['cf_cust']);
										$cf['category'] = count($cf['category']) ? $hesklang['cf_cat'] : $hesklang['cf_all'];
										?>
										<tr>
											<td><?php echo $id; ?></td>
											<td><?php echo $cf['name']; ?></td>
											<td><?php echo $cf['type']; ?></td>
											<td><?php echo $cf['use']; ?></td>
											<td><?php echo $cf['req']; ?></td>
											<td><?php echo $cf['category']; ?></td>
											<td>
												<?php
												if ($hesk_settings['num_custom_fields'] == 2 && $num_before == 1) {
													// Special case, don't print anything
												} elseif ($hesk_settings['num_custom_fields'] > 1) {
													if (($num_before == 1 && $cf['place'] == 0) || ($num_after == 1 && $cf['place'] == 1)) {
														// Only 1 custom fields in this place, don't print anything
														?>
														<i class="fa fa-fw icon-link">&nbsp;</i>
														<i class="fa fa-fw icon-link">&nbsp;</i>
														<?php
													} elseif ($k == 1 || $hide_up) {
														?>
														<i class="fa fa-fw icon-link">&nbsp;</i>
														<a href="custom_fields.php?a=order_cf&amp;id=<?php echo $id; ?>&amp;move=15&amp;token=<?php hesk_token_echo(); ?>">
															<i class="fa fa-arrow-down fa-fw icon-link green" data-toggle="tooltip" title="<?php echo $hesklang['move_dn']; ?>"></i>
														</a>
														<?php
													} elseif ($k == $hesk_settings['num_custom_fields'] || $k == $num_before) {
														?>
														<a href="custom_fields.php?a=order_cf&amp;id=<?php echo $id; ?>&amp;move=-15&amp;token=<?php hesk_token_echo(); ?>">
															<i class="fa fa-arrow-up fa-fw icon-link green" data-toggle="tooltip" title="<?php echo $hesklang['move_up']; ?>"></i>
														</a>
														<i class="fa fa-fw icon-link">&nbsp;</i>
														<?php
													} else {
														?>
														<a href="custom_fields.php?a=order_cf&amp;id=<?php echo $id; ?>&amp;move=-15&amp;token=<?php hesk_token_echo(); ?>">
															<i class="fa fa-arrow-up fa-fw icon-link green" data-toggle="tooltip" title="<?php echo $hesklang['move_up']; ?>"></i>
														</a>
														<a href="custom_fields.php?a=order_cf&amp;id=<?php echo $id; ?>&amp;move=15&amp;token=<?php hesk_token_echo(); ?>">
															<i class="fa fa-arrow-down fa-fw icon-link green" data-toggle="tooltip" title="<?php echo $hesklang['move_dn']; ?>"></i>
														</a>
														<?php
													}
												}
												?>
												<a href="custom_fields.php?a=edit_cf&amp;id=<?php echo $id; ?>">
													<i class="fa fa-pencil fa-fw icon-link orange" data-toggle="tooltip" title="<?php echo $hesklang['edit']; ?>"></i>
												</a>
												<a href="custom_fields.php?a=remove_cf&amp;id=<?php echo $id; ?>&amp;token=<?php hesk_token_echo(); ?>"
												   onclick="return hesk_confirmExecute('<?php echo hesk_makeJsString($hesklang['del_cf']); ?>');">
													<i class="fa fa-times fa-fw icon-link red" data-toggle="tooltip" title="<?php echo $hesklang['delete']; ?>"></i>
												</a>
											</td>
										</tr>
										<?php
										$k++;
									} // End while
									?>
									</tbody>
								</table>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
</div>
<?php

hesk_cleanSessionVars( array('new_cf', 'edit_cf') );

require_once(HESK_PATH . 'inc/footer.inc.php');
exit();


/*** START FUNCTIONS ***/


function save_cf()
{
	global $hesk_settings, $hesklang;
	global $hesk_error_buffer;

	// A security check
	# hesk_token_check('POST');

	// Get custom field ID
	$id = intval( hesk_POST('id') ) or hesk_error($hesklang['cf_e_id']);

	// Validate inputs
	if (($cf = cf_validate()) == false)
	{
		$_SESSION['edit_cf'] = true;
		$_SESSION['new_cf']['id'] = $id;

		$tmp = '';
		foreach ($hesk_error_buffer as $error)
		{
			$tmp .= "<li>$error</li>\n";
		}
		$hesk_error_buffer = $tmp;

		$hesk_error_buffer = $hesklang['rfm'].'<br /><br /><ul>'.$hesk_error_buffer.'</ul>';
		hesk_process_messages($hesk_error_buffer,'custom_fields.php');
	}

	// Add custom field data into database
	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` SET
	`use`      = '{$cf['use']}',
	`place`    = '{$cf['place']}',
	`type`     = '{$cf['type']}',
	`req`      = '{$cf['req']}',
	`category` = ".(count($cf['categories']) ? "'".json_encode($cf['categories'])."'" : 'NULL').",
	`name`     = '".hesk_dbEscape($cf['names'])."',
	`value`    = ".(strlen($cf['value']) ? "'".hesk_dbEscape($cf['value'])."'" : 'NULL')."
	WHERE `id`={$id}");

	// Clear cache
	hesk_purge_cache('cf');

	// Show success
	$_SESSION['cford'] = $id;
	hesk_process_messages($hesklang['cf_mdf'],'custom_fields.php','SUCCESS');

} // End save_cf()


function edit_cf()
{
	global $hesk_settings, $hesklang;

	// Get custom field ID
	$id = intval( hesk_GET('id') ) or hesk_error($hesklang['cf_e_id']);

	// Get details from the database
	$res = hesk_dbQuery("SELECT * FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` WHERE `id`={$id} LIMIT 1");
	if ( hesk_dbNumRows($res) != 1 )
	{
		hesk_error($hesklang['cf_not_found']);
	}
	$cf = hesk_dbFetchAssoc($res);

	$cf['names'] = json_decode($cf['name'], true);
	unset($cf['name']);

	if (strlen($cf['category']))
	{
		$cf['categories'] = json_decode($cf['category'], true);
		$cf['category'] = 1;
	}
	else
	{
		$cf['categories'] = array();
		$cf['category'] = 0;
	}

	$_SESSION['new_cf'] = $cf;
	$_SESSION['edit_cf'] = true;

} // End edit_cf()


function order_cf()
{
	global $hesk_settings, $hesklang;

	// A security check
	hesk_token_check();

	// Get ID and move parameters
	$id    = intval( hesk_GET('id') ) or hesk_error($hesklang['cf_e_id']);
	$move  = intval( hesk_GET('move') );
	$_SESSION['cford'] = $id;

	// Update article details
	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` SET `order`=`order`+".intval($move)." WHERE `id`={$id}");

	// Update order of all custom fields
	update_cf_order();

	// Clear cache
	hesk_purge_cache('cf');

	// Finish
	header('Location: custom_fields.php');
	exit();

} // End order_cf()


function update_cf_order()
{
	global $hesk_settings, $hesklang;

	// Get list of current custom fields
	$res = hesk_dbQuery("SELECT `id` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` WHERE `use` IN ('1','2') ORDER BY `place` ASC, `order` ASC");

	// Update database
	$i = 10;
	while ( $cf = hesk_dbFetchAssoc($res) )
	{
		hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` SET `order`=".intval($i)." WHERE `id`='".intval($cf['id'])."'");
		$i += 10;
	}

	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` SET `order`=1000 WHERE `use`='0'");

	return true;

} // END update_cf_order()


function remove_cf()
{
	global $hesk_settings, $hesklang;

	// A security check
	hesk_token_check();

	// Get ID
	$id = intval( hesk_GET('id') ) or hesk_error($hesklang['cf_e_id']);

	// Reset the custom field
	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` SET `use`='0', `place`='0', `type`='text', `req`='0', `category`=NULL, `name`='', `value`=NULL, `order`=1000 WHERE `id`={$id}");

	// Were we successful?
	if ( hesk_dbAffectedRows() == 1 )
	{
		// Update order
		update_cf_order();

		// Clear cache
		hesk_purge_cache('cf');

		// Delete custom field data from tickets
		hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."tickets` SET `custom{$id}`=''");

		// Show success message
		hesk_process_messages($hesklang['cf_deleted'],'./custom_fields.php','SUCCESS');
	}
	else
	{
		hesk_process_messages($hesklang['cf_not_found'],'./custom_fields.php');
	}

} // End remove_cf()


function cf_validate()
{
	global $hesk_settings, $hesklang;
	global $hesk_error_buffer;

	$hesk_error_buffer = array();

	// Get names
	$cf['names'] = hesk_POST_array('name');

	// Make sure only valid names pass
	foreach ($cf['names'] as $key => $name)
	{
		if ( ! isset($hesk_settings['languages'][$key]))
		{
			unset($cf['names'][$key]);
		}
		else
		{
			$name = is_array($name) ? '' : hesk_input($name, 0, 0, HESK_SLASH);

			if (strlen($name) < 1)
			{
				unset($cf['names'][$key]);
			}
			else
			{
				$cf['names'][$key] = stripslashes($name);
			}
		}
	}

	// No name entered?
	if ( ! count($cf['names']))
	{
		$hesk_error_buffer[] = $hesklang['err_custname'];
	}

	// Get type and values
	$cf['type'] = hesk_POST('type');
	switch ($cf['type'])
	{
		case 'textarea':
			$cf['rows'] = hesk_checkMinMax(intval(hesk_POST('rows')), 1, 100, 12);
			$cf['cols'] = hesk_checkMinMax(intval(hesk_POST('cols')), 1, 500, 60);
			$cf['value'] = array('rows' => $cf['rows'], 'cols' => $cf['cols']);
			break;

		case 'radio':
			$cf['radio_options'] = stripslashes(hesk_input(hesk_POST('radio_options'), 0, 0, HESK_SLASH));

			$options = preg_split("/\\r\\n|\\r|\\n/", $cf['radio_options']);

			$no_default = hesk_POST('no_default') ? 1 : 0;

			$cf['value'] = array('radio_options' => $options, 'no_default' => $no_default);

			if (count($options) < 2)
			{
				$hesk_error_buffer[] = $hesklang['atl2'];
			}

			break;

		case 'select':
			$cf['select_options'] = stripslashes(hesk_input(hesk_POST('select_options'), 0, 0, HESK_SLASH));

			$options = preg_split("/\\r\\n|\\r|\\n/", $cf['select_options']);

			$show_select = hesk_POST('show_select') ? 1 : 0;

			$cf['value'] = array('show_select' => $show_select, 'select_options' => $options);

			if (count($options) < 2)
			{
				$hesk_error_buffer[] = $hesklang['atl2'];
			}

			break;

		case 'checkbox':
			$cf['checkbox_options'] = stripslashes(hesk_input(hesk_POST('checkbox_options'), 0, 0, HESK_SLASH));

			$options = preg_split("/\\r\\n|\\r|\\n/", $cf['checkbox_options']);

			$cf['value'] = array('checkbox_options' => $options);

			if ( ! isset($options[0]) || strlen($options[0]) < 1)
			{
				$hesk_error_buffer[] = $hesklang['atl1'];
			}

			break;

		case 'date':
        	$cf['dmin'] = '';
            $cf['dmax'] = '';

            // Minimum date
            $dmin_rf = hesk_POST('dmin_rf');

            if ($dmin_rf == 1)
            {
            	$dmin = hesk_POST('dmin');

            	if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dmin))
                {
                	$cf['dmin'] = $dmin;
                }
            }
            elseif ($dmin_rf == 2)
            {
				$dmin_pm = hesk_POST('dmin_pm') == '+' ? '+' : '-';
				$dmin_num = intval(hesk_POST('dmin_num', 0));
				$dmin_type = hesk_POST('dmin_type');
                if ( ! in_array($dmin_type, array('day', 'week', 'month', 'year')))
                {
                	$dmin_type = 'day';
                }

                $cf['dmin'] = $dmin_pm . $dmin_num . ' ' . $dmin_type;
            }

			// Maximum date
            $dmax_rf = hesk_POST('dmax_rf');

            if ($dmax_rf == 1)
            {
            	$dmax = hesk_POST('dmax');

            	if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $dmax))
                {
                	$cf['dmax'] = $dmax;
                }
            }
            elseif ($dmax_rf == 2)
            {
				$dmax_pm = hesk_POST('dmax_pm') == '+' ? '+' : '-';
				$dmax_num = intval(hesk_POST('dmax_num', 0));
				$dmax_type = hesk_POST('dmax_type');
                if ( ! in_array($dmax_type, array('day', 'week', 'month', 'year')))
                {
                	$dmax_type = 'day';
                }

                $cf['dmax'] = $dmax_pm . $dmax_num . ' ' . $dmax_type;
            }

            // Minimum date should not be higher than maximum date
            if (strlen($cf['dmin']) && strlen($cf['dmax']))
            {
				if (strtotime($cf['dmin']) > strtotime($cf['dmax']))
				{
					$hesk_error_buffer[] = $hesklang['d_mm'];
				}
            }

            // Date format
            $date_format = hesk_POST('date_format');
            if ($date_format == 'custom')
            {
            	$date_format = hesk_POST('date_format_custom');
            }

            $cf['date_format'] = preg_replace('/[^a-zA-Z0-9 \/\.\_+\-,;:#(){}\[\]\'@*]/', '', $date_format);

            $cf['value'] = array('dmin' => $cf['dmin'], 'dmax' => $cf['dmax'], 'date_format' => $cf['date_format']);

			break;

		case 'email':
			$cf['email_multi'] = hesk_POST('email_multi') ? 1 : 0;
			$cf['email_type'] = hesk_POST('email_type', 'none');
			$cf['value'] = array('multiple' => $cf['email_multi'], 'email_type' => $cf['email_type']);
			break;

		case 'hidden':
			$cf['hidden_max_length'] = hesk_checkMinMax(intval(hesk_POST('hidden_max_length')), 1, 10000, 255);
			$cf['hidden_default_value'] = stripslashes(hesk_input(hesk_POST('hidden_default_value'), 0, 0, HESK_SLASH));
			$cf['value'] = array('max_length' => $cf['hidden_max_length'], 'default_value' => $cf['hidden_default_value']);
			break;

		case 'readonly':
			$max_length = hesk_POST('max_length');
			$value = hesk_POST('default_value');
			$cf['value'] = array('default_value' => $value, 'max_length' => $max_length);
			break;

		default:
			$cf['type'] = 'text';
			$cf['max_length'] = hesk_checkMinMax(intval(hesk_POST('max_length')), 1, 10000, 255);
			$cf['default_value'] = stripslashes(hesk_input(hesk_POST('default_value'), 0, 0, HESK_SLASH));
			$cf['value'] = array('max_length' => $cf['max_length'], 'default_value' => $cf['default_value']);

	}

	// Enable
	$cf['use'] = hesk_POST('use') == 2 ? 2 : 1;

	// req
	$cf['req'] = hesk_POST('req');
	$cf['req'] = $cf['req'] == 2 ? 2 : ($cf['req'] == 1 ? 1 : 0);

	// Private fields cannot be req for customers
	if ($cf['use'] == 2 && $cf['req'] == 1)
	{
		$cf['req'] = 0;
	}

	// Located above or below "Message"?
	$cf['place'] = hesk_POST('place') ? 1 : 0;

	// Get allowed categories
	if (hesk_POST('category'))
	{
		$cf['category'] = 1;
		$cf['categories'] = hesk_POST_array('categories');

		foreach ($cf['categories'] as $key => $cat_id)
		{
			if ( ! isset($hesk_settings['categories'][$cat_id]) )
			{
				unset($cf['categories'][$key]);
			}
		}

		if ( ! count($cf['categories']))
		{
			$hesk_error_buffer[] = $hesklang['cf_nocat'];
		}
	}
	else
	{
		$cf['category'] = 0;
		$cf['categories'] = array();
	}

	// Any errors?
	if (count($hesk_error_buffer))
	{
		$_SESSION['new_cf'] = $cf;
		return false;
	}

	$cf['names'] = addslashes(json_encode($cf['names']));
	$cf['value'] = $cf['type'] == 'date' ? json_encode($cf['value']) : addslashes(json_encode($cf['value']));

	return $cf;
} // END cf_validate()


function new_cf()
{
	global $hesk_settings, $hesklang;
	global $hesk_error_buffer;

	// A security check
	# hesk_token_check('POST');

	// Validate inputs
	if (($cf = cf_validate()) == false)
	{
		$tmp = '';
		foreach ($hesk_error_buffer as $error)
		{
			$tmp .= "<li>$error</li>\n";
		}
		$hesk_error_buffer = $tmp;

		$hesk_error_buffer = $hesklang['rfm'].'<br /><br /><ul>'.$hesk_error_buffer.'</ul>';
		hesk_process_messages($hesk_error_buffer,'custom_fields.php');
	}

	// Get the lowest available custom field ID
	$res = hesk_dbQuery("SELECT `id` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` WHERE `use`='0' ORDER BY `id` ASC LIMIT 1");
	$row = hesk_dbFetchRow($res);
	$_SESSION['cford'] = intval($row[0]);

	// Insert custom field into database
	hesk_dbQuery("UPDATE `".hesk_dbEscape($hesk_settings['db_pfix'])."custom_fields` SET
	`use`      = '{$cf['use']}',
	`place`    = '{$cf['place']}',
	`type`     = '{$cf['type']}',
	`req`      = '{$cf['req']}',
	`category` = ".(count($cf['categories']) ? "'".json_encode($cf['categories'])."'" : 'NULL').",
	`name`     = '".hesk_dbEscape($cf['names'])."',
	`value`    = ".(strlen($cf['value']) ? "'".hesk_dbEscape($cf['value'])."'" : 'NULL').",
	`order`    = 990
	WHERE `id`={$_SESSION['cford']}");

	// Update order
	update_cf_order();

	// Clear cache
	hesk_purge_cache('cf');

	// Show success
	hesk_process_messages($hesklang['cf_added'],'custom_fields.php','SUCCESS');

} // End new_cf()
