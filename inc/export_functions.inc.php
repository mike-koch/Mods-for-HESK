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

/*** FUNCTIONS ***/

function hesk_export_to_XML($sql, $export_selected = false)
{
    global $hesk_settings, $hesklang, $ticket, $my_cat;

    // We'll need HH:MM:SS format for hesk_date() here
    $hesk_settings['timeformat'] = 'H:i:s';

    // Get staff names
    $admins = array();
    $result = hesk_dbQuery("SELECT `id`,`name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "users` ORDER BY `name` ASC");
    while ($row = hesk_dbFetchAssoc($result)) {
        $admins[$row['id']] = $row['name'];
    }

    // Get category names
    if ( ! isset($my_cat))
    {
        $my_cat = array();
        $res2 = hesk_dbQuery("SELECT `id`, `name` FROM `".hesk_dbEscape($hesk_settings['db_pfix'])."categories` WHERE " . hesk_myCategories('id') . " ORDER BY `cat_order` ASC");
        while ($row=hesk_dbFetchAssoc($res2))
        {
            $my_cat[$row['id']] = hesk_msgToPlain($row['name'], 1);
        }
    }

    // This will be the export directory
    $export_dir = HESK_PATH.$hesk_settings['cache_dir'].'/';

    // This will be the name of the export and the XML file
    $export_name = 'hesk_export_' . date('Y-m-d_H-i-s') . '_' . mt_rand(10000, 99999);
    $save_to = $export_dir . $export_name . '.xml';

    // Do we have the export directory?
    if (is_dir($export_dir) || (@mkdir($export_dir, 0777) && is_writable($export_dir))) {
        // Is there an index.htm file?
        if (!file_exists($export_dir.'index.htm')) {
            @file_put_contents($export_dir.'index.htm', '');
        }

        // Cleanup old files
        hesk_purge_cache('export', 86400);
    } else {
        hesk_error($hesklang['ede']);
    }

    // Make sure the file can be saved and written to
    @file_put_contents($save_to, '');
    if (!file_exists($save_to)) {
        hesk_error($hesklang['eef']);
    }

    // Start generating the report message and generating the export
    $success_msg = '';
    $flush_me = '<br /><br />';
    $flush_me .= hesk_date() . " | {$hesklang['inite']} ";

    // Is this export of a date or date range?
    if ($export_selected === false)
    {
        global $date_from, $date_to;

        if ($date_from == $date_to)
        {
            $flush_me .= "(" . hesk_dateToString($date_from,0) . ")";
        }
        else
        {
            $flush_me .= "(" . hesk_dateToString($date_from,0) . " - " . hesk_dateToString($date_to,0) . ")";
        }
    }

    // Start generating file contents
    $tmp = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
  <AllowPNG/>
 </OfficeDocumentSettings>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>8250</WindowHeight>
  <WindowWidth>16275</WindowWidth>
  <WindowTopX>360</WindowTopX>
  <WindowTopY>90</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:CharSet="238" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s62">
   <NumberFormat ss:Format="General Date"/>
  </Style>
  <Style ss:ID="s63">
   <NumberFormat ss:Format="Short Date"/>
  </Style>
  <Style ss:ID="s65">
   <NumberFormat ss:Format="[h]:mm:ss"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Sheet1">
  <Table>
';

    // Define column width
    $tmp .= '
	<Column ss:AutoFitWidth="0" ss:Width="50"/>
	<Column ss:AutoFitWidth="0" ss:Width="84" ss:Span="1"/>
	<Column ss:AutoFitWidth="0" ss:Width="110"/>
	<Column ss:AutoFitWidth="0" ss:Width="110"/>
	<Column ss:AutoFitWidth="0" ss:Width="90"/>
	<Column ss:AutoFitWidth="0" ss:Width="90"/>
	<Column ss:AutoFitWidth="0" ss:Width="87"/>
	<Column ss:AutoFitWidth="0" ss:Width="57.75"/>
	<Column ss:AutoFitWidth="0" ss:Width="57.75"/>
	<Column ss:AutoFitWidth="0" ss:Width="100"/>
	<Column ss:AutoFitWidth="0" ss:Width="100"/>
	<Column ss:AutoFitWidth="0" ss:Width="80"/>
	<Column ss:AutoFitWidth="0" ss:Width="80"/>
	';

    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        if ($v['use']) {
            $tmp .= '<Column ss:AutoFitWidth="0" ss:Width="80"/>' . "\n";
        }
    }

    // Define first row (header)
    $tmp .= '
	<Row>
	<Cell><Data ss:Type="String">#</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['trackID'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['date'] . '</Data></Cell>
    <Cell><Data ss:Type="String">' . $hesklang['last_update'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['name'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['email'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['category'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['priority'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['status'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['subject'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['message'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['owner'] . '</Data></Cell>
	<Cell><Data ss:Type="String">' . $hesklang['ts'] . '</Data></Cell>
	';

    foreach ($hesk_settings['custom_fields'] as $k => $v) {
        if ($v['use']) {
            $tmp .= '<Cell><Data ss:Type="String">' . $v['name'] . '</Data></Cell>' . "\n";
        }
    }

    $tmp .= "</Row>\n";

    // Write what we have by now into the XML file
    file_put_contents($save_to, $tmp, FILE_APPEND);
    $flush_me .= hesk_date() . " | {$hesklang['gXML']}<br />\n";

    // OK, now start dumping data and writing it into the file
    $tickets_exported = 0;
    $save_after = 100;
    $this_round = 0;
    $tmp = '';

    $result = hesk_dbQuery($sql);
    while ($ticket = hesk_dbFetchAssoc($result)) {
        $ticket['status'] = mfh_getDisplayTextForStatusId($ticket['status']);

        switch ($ticket['priority']) {
            case 0:
                $ticket['priority'] = $hesklang['critical'];
                break;
            case 1:
                $ticket['priority'] = $hesklang['high'];
                break;
            case 2:
                $ticket['priority'] = $hesklang['medium'];
                break;
            default:
                $ticket['priority'] = $hesklang['low'];
        }

        $ticket['archive'] = !($ticket['archive']) ? $hesklang['no'] : $hesklang['yes'];
        $ticket['message'] = hesk_msgToPlain($ticket['message'], 1, 0);
        $ticket['subject'] = hesk_msgToPlain($ticket['subject'], 1, 0);
        $ticket['owner'] = isset($admins[$ticket['owner']]) ? $admins[$ticket['owner']] : '';
        $ticket['category'] = isset($my_cat[$ticket['category']]) ? $my_cat[$ticket['category']] : '';

        // Format for export dates
        $hesk_settings['timeformat'] = "Y-m-d\TH:i:s\.000";

        // Create row for the XML file
        $tmp .= '
<Row>
<Cell><Data ss:Type="Number">' . $ticket['id'] . '</Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . $ticket['trackid'] . ']]></Data></Cell>
<Cell ss:StyleID="s62"><Data ss:Type="DateTime">' . hesk_date($ticket['dt'], true) . '</Data></Cell>
<Cell ss:StyleID="s62"><Data ss:Type="DateTime">' . hesk_date($ticket['lastchange'], true) . '</Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . hesk_msgToPlain($ticket['name'], 1) . ']]></Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . $ticket['email'] . ']]></Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . $ticket['category'] . ']]></Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . $ticket['priority'] . ']]></Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . $ticket['status'] . ']]></Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . $ticket['subject'] . ']]></Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . $ticket['message'] . ']]></Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . $ticket['owner'] . ']]></Data></Cell>
<Cell><Data ss:Type="String"><![CDATA[' . $ticket['time_worked'] . ']]></Data></Cell>
';

        // Add custom fields
        foreach ($hesk_settings['custom_fields'] as $k=>$v) {
            if ($v['use']) {
                switch ($v['type']) {
                    case 'date':
                        $tmp_dt = hesk_custom_date_display_format($ticket[$k], 'Y-m-d\T00:00:00.000');
                        $tmp .= strlen($tmp_dt) ? '<Cell ss:StyleID="s63"><Data ss:Type="DateTime">'.$tmp_dt : '<Cell><Data ss:Type="String">';
                        $tmp .= "</Data></Cell> \n";
                        break;
                    default:
                        $tmp .= '<Cell><Data ss:Type="String"><![CDATA['.hesk_msgToPlain($ticket[$k], 1, 0).']]></Data></Cell>  ' . "\n";
                }
            }
        }

        $tmp .= "</Row>\n";

        // Write every 100 rows into the file
        if ($this_round >= $save_after) {
            file_put_contents($save_to, $tmp, FILE_APPEND);
            $this_round = 0;
            $tmp = '';
            usleep(1);
        }

        $tickets_exported++;
        $this_round++;
    } // End of while loop

    // Go back to the HH:MM:SS format for hesk_date()
    $hesk_settings['timeformat'] = 'H:i:s';

    // Append any remaining rows into the file
    if ($this_round > 0) {
        file_put_contents($save_to, $tmp, FILE_APPEND);
    }

    // If any tickets were exported, continue, otherwise cleanup
    if ($tickets_exported > 0) {
        // Finish the XML file
        $tmp = '
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <Selected/>
   <Panes>
    <Pane>
     <Number>3</Number>
     <ActiveRow>4</ActiveRow>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Sheet2">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
 <Worksheet ss:Name="Sheet3">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>
</Workbook>
';
        file_put_contents($save_to, $tmp, FILE_APPEND);

        // Log how many rows we exported
        $flush_me .= hesk_date() . " | " . sprintf($hesklang['nrow'], $tickets_exported) . "<br />\n";

        // We will convert XML to Zip to save a lot of space
        $save_to_zip = $export_dir . $export_name . '.zip';

        // Log start of Zip creation
        $flush_me .= hesk_date() . " | {$hesklang['cZIP']}<br />\n";

        // Preferrably use the zip extension
        if (extension_loaded('zip')) {
            $save_to_zip = $export_dir . $export_name . '.zip';

            $zip = new ZipArchive;
            $res = $zip->open($save_to_zip, ZipArchive::CREATE);
            if ($res === TRUE) {
                $zip->addFile($save_to, "{$export_name}.xml");
                $zip->close();
            } else {
                die("{$hesklang['eZIP']} <$save_to_zip>\n");
            }

        } // Some servers have ZipArchive class enabled anyway - can we use it?
        elseif (class_exists('ZipArchive')) {
            require(HESK_PATH . 'inc/zip/Zip.php');
            $zip = new Zip();
            $zip->addLargeFile($save_to, "{$export_name}.xml");
            $zip->finalize();
            $zip->setZipFile($save_to_zip);
        } // If not available, use a 3rd party Zip class included with HESK
        else {
            require(HESK_PATH . 'inc/zip/pclzip.lib.php');
            $zip = new PclZip($save_to_zip);
            $zip->add($save_to, PCLZIP_OPT_REMOVE_ALL_PATH);
        }

        // Delete XML, just leave the Zip archive
        hesk_unlink($save_to);

        // Echo memory peak usage
        $flush_me .= hesk_date() . " | " . sprintf($hesklang['pmem'], (@memory_get_peak_usage(true) / 1048576)) . "<br />\r\n";

        // We're done!
        $flush_me .= hesk_date() . " | {$hesklang['fZIP']}<br /><br />";

        // Success message
        $success_msg .= $hesk_settings['debug_mode'] ? $flush_me : '<br /><br />';
        $success_msg .= $hesklang['step1'] . ': <a href="' . $save_to_zip . '">' . $hesklang['ch2d'] . '</a><br /><br />' . $hesklang['step2'] . ': <a href="export.php?delete='.urlencode($export_name).'">' . $hesklang['dffs'] . '</a>';
    } // No tickets exported, cleanup
    else {
        hesk_unlink($save_to);
    }

    return array($success_msg, $tickets_exported);

} // END hesk_export_to_XML()