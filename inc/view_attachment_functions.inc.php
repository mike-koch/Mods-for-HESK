<?php
function mfh_listAttachments($attachments = '', $reply = 0, $is_staff)
{
    global $hesk_settings, $hesklang, $trackingID, $can_edit, $can_delete;

    $email = '';
    if (!$is_staff) {
        $email = $hesk_settings['e_query'];
    }

    /* Attachments disabled or not available */
    if (!$hesk_settings['attachments']['use'] || !strlen($attachments)) {
        return false;
    }

    /* List attachments */
    $att = explode(',', substr($attachments, 0, -1));
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped attachment-table">';
    echo '<thead>
        <tr>
        <th>&nbsp;</th>
        <th>' . $hesklang['file_name'] . '</th>';
    if ($is_staff) {
        echo '<th>' . $hesklang['download_count'] . '</th>';
    }
    echo '<th>' . $hesklang['action'] . '</th>
        </tr>
        </thead>';
    echo '<tbody>';
    foreach ($att as $myatt) {

        list($att_id, $att_name) = explode('#', $myatt);
        $fileparts = pathinfo($att_name);
        $fontAwesomeIcon = mfh_getFontAwesomeIconForFileExtension($fileparts['extension']);
        echo '
        <tr>
            <td>';
        //-- File is an image
        if ($fontAwesomeIcon == 'fa fa-file-image-o') {

            //-- Get the actual image location and display a thumbnail. It will be linked to a modal to view a larger size.
            $path = mfh_getSavedNameUrlForAttachment($att_id, $is_staff);
            if ($path == '') {
                echo '<i class="fa fa-ban fa-4x" data-toggle="tooltip" title="' . $hesklang['attachment_removed'] . '"></i>';
            } else {
                echo '<span data-toggle="tooltip" title="' . $hesklang['click_to_preview'] . '">
                                  <img src="' . $path . '" alt="' . $hesklang['image'] . '" data-toggle="modal" data-target="#modal-attachment-' . $att_id . '">
                              </span>';
                $download_path = '';
                if ($is_staff) {
                    $download_path = '../';
                }
                echo '<div class="modal fade" id="modal-attachment-' . $att_id . '" tabindex="-1" role="dialog" aria-hidden="true">
                                  <div class="modal-dialog">
                                      <div class="modal-content">
                                          <div class="modal-header">
                                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                              <h4 class="modal-title" id="myModalLabel">' . $att_name . '</h4>
                                          </div>
                                          <div class="modal-body">
                                              <img class="img-responsive" src="' . $path . '" alt="' . $hesklang['image'] . '">
                                          </div>
                                          <div class="modal-footer">
                                              <button type="button" class="btn btn-default" data-dismiss="modal">' . $hesklang['close_modal'] . '</button>
                                              <a href="' . $download_path . 'download_attachment.php?att_id=' . $att_id . '&amp;track=' . $trackingID . $email . '" class="btn btn-success">' . $hesklang['dnl'] . '</a>
                                          </div>
                                      </div>
                                  </div>
                              </div>';
            }
        } else {
            //-- Display the FontAwesome icon in the panel's body
            echo '<i class="' . $fontAwesomeIcon . ' fa-4x"></i>';
        }
        echo '
            </td>
            <td>
                <p>' . $att_name . '</p>
            </td>';
        if ($is_staff) {
            echo '<td>' . mfh_getNumberOfDownloadsForAttachment($att_id) . '</td>';
        }
        echo '<td>
                <div class="btn-group">';
        /* Can edit and delete tickets? */
        $download_path = '';
        if ($is_staff) {
            $download_path = '../';
            if ($can_edit && $can_delete) {
                echo '<a class="btn btn-danger" href="admin_ticket.php?delatt=' . $att_id . '&amp;reply=' . $reply . '&amp;track=' . $trackingID . '&amp;Refresh=' . mt_rand(10000, 99999) . '&amp;token=' . hesk_token_echo(0) . '" onclick="return hesk_confirmExecute(\'' . hesk_makeJsString($hesklang['pda']) . '\');" data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['delete'] . '"><i class="fa fa-times"></i></a> ';
            }
        }
        echo '<a class="btn btn-success" href="' . $download_path . 'download_attachment.php?att_id=' . $att_id . '&amp;track=' . $trackingID . $email . '"
                        data-toggle="tooltip" data-placement="top" data-original-title="' . $hesklang['dnl'] . '">
                            <i class="fa fa-arrow-down"></i>
                      </a>';
        echo '</div>
            </td>
        </tr>
        ';
    }
    echo '</tbody></table></div>';

    return true;
} // End hesk_listAttachments()

function mfh_getSavedNameUrlForAttachment($att_id, $is_staff)
{
    global $hesk_settings;

    //-- Call the DB for the attachment
    $nameRS = hesk_dbQuery("SELECT `saved_name` FROM `" . hesk_dbEscape($hesk_settings['db_pfix']) . "attachments` WHERE `att_id` = " . hesk_dbEscape($att_id));
    $name = hesk_dbFetchAssoc($nameRS);
    if ($is_staff) {
        $realpath = '../' . $hesk_settings['attach_dir'] . '/' . $name['saved_name'];
    } else {
        $realpath = $hesk_settings['attach_dir'] . '/' . $name['saved_name'];
    }

    return !file_exists($realpath) ? '' : $realpath;
}

function mfh_getFontAwesomeIconForFileExtension($fileExtension)
{
    $imageExtensions = array('jpg', 'jpeg', 'png', 'bmp', 'gif');

    //-- Word, Excel, and PPT file extensions: http://en.wikipedia.org/wiki/List_of_Microsoft_Office_filename_extensions
    $wordFileExtensions = array('doc', 'docx', 'dotm', 'dot', 'docm', 'docb');
    $excelFileExtensions = array('xls', 'xlt', 'xlm', 'xlsx', 'xlsm', 'xltx', 'xltm');
    $pptFileExtensions = array('ppt', 'pot', 'pps', 'pptx', 'pptm', 'potx', 'potm', 'ppsx', 'ppsm', 'sldx', 'sldm');

    //-- File archive extensions: http://en.wikipedia.org/wiki/List_of_archive_formats
    $archiveFileExtensions = array('tar', 'gz', 'zip', 'rar', '7z', 'bz2', 'lz', 'lzma', 'tgz', 'tbz2', 'zipx');

    //-- Audio file extensions: http://en.wikipedia.org/wiki/Audio_file_format#List_of_formats
    $audioFileExtensions = array('3gp', 'act', 'aiff', 'aac', 'amr', 'au', 'awb', 'dct', 'dss', 'dvf', 'flac', 'gsm', 'iklax', 'ivs', 'm4a', 'm4p', 'mmf', 'mp3', 'mpc', 'msv', 'ogg', 'oga', 'opus', 'ra', 'rm', 'raw', 'tta', 'vox', 'wav', 'wma', 'wv');

    //-- Video file extensions: http://en.wikipedia.org/wiki/Video_file_format#List_of_video_file_formats
    $videoFileExtensions = array('webm', 'mkv', 'flv', 'drc', 'mng', 'avi', 'mov', 'qt', 'wmv', 'yuv', 'rm', 'rmvb', 'asf', 'mp4', 'm4p', 'm4v', 'mpg', 'mp2', 'mpeg', 'mpe', 'mpv', 'm2v', 'svi', '3gp', '3g2', 'mxf', 'roq', 'nsv');

    //-- The only one I know of :D
    $pdfFileExtensions = array('pdf');

    $textFileExtensions = array('txt');

    $icon = 'fa fa-file-';
    $fileExtension = strtolower($fileExtension);
    if (in_array($fileExtension, $imageExtensions)) {
        $icon .= 'image-o';
    } elseif (in_array($fileExtension, $wordFileExtensions)) {
        $icon .= 'word-o';
    } elseif (in_array($fileExtension, $excelFileExtensions)) {
        $icon .= 'excel-o';
    } elseif (in_array($fileExtension, $pptFileExtensions)) {
        $icon .= 'powerpoint-o';
    } elseif (in_array($fileExtension, $archiveFileExtensions)) {
        $icon .= 'archive-o';
    } elseif (in_array($fileExtension, $audioFileExtensions)) {
        $icon .= 'audio-o';
    } elseif (in_array($fileExtension, $videoFileExtensions)) {
        $icon .= 'video-o';
    } elseif (in_array($fileExtension, $pdfFileExtensions)) {
        $icon .= 'pdf-o';
    } elseif (in_array($fileExtension, $textFileExtensions)) {
        $icon .= 'text-o';
    } else {
        $icon .= 'o';
    }
    return $icon;
}

function output_dropzone_window() {
    echo '
    <div class="table table-striped" class="files" id="previews" style="display:none">
        <div id="template" class="file-row">
            <!-- This is used as the file preview template -->
            <div class="row">
                <div class="col-md-4">
                    <span class="preview"><img class="img-responsive" data-dz-thumbnail></span>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <p class="name" data-dz-name></p>
                        <i class="fa fa-trash fa-2x" style="color: gray; cursor: pointer" title="Remove file" data-dz-remove></i>
                        <span class="size" data-dz-size></span>
                    </div>
                    <div class="row">
                        <div class="progress progress-striped active" role="progressbar" id="total-progress">
                            <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress>
                                <span id="percentage"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <strong class="error text-danger" data-dz-errormessage></strong>
                </div>
            </div>
        </div>
    </div>';
}

function output_attachment_id_holder_container($id) {
    echo '<div id="attachment-holder-' . $id . '" class="hide"></div>';
}

function build_dropzone_markup($admin = false, $id = 'filedrop') {
    global $hesklang, $hesk_settings;

    $directory_separator = $admin ? '../' : '';
    echo '<div class="dropzone" id="' . $id . '">
        <div class="fallback">
            <input type="hidden" name="use-legacy-attachments" value="1">';
            for ($i = 1; $i <= $hesk_settings['attachments']['max_number']; $i++) {
                $cls = ($i == 1 && isset($_SESSION['iserror']) && in_array('attachments', $_SESSION['iserror'])) ? ' class="isError" ' : '';
                echo '<input type="file" name="attachment[' . $i . ']" size="50" ' . $cls . ' /><br />';
            }
        echo '</div>
    </div>
    <div class="btn btn-default btn-xs fileinput-button filedropbutton-' . $id . '">' . $hesklang['add_files'] . '</div><br>
    <a href="' . $directory_separator . 'file_limits.php" target="_blank"
       onclick="Javascript:hesk_window(\'' . $directory_separator . 'file_limits.php\',250,500);return false;">'. $hesklang['ful'] . '</a>';
}

function display_dropzone_field($url, $id = 'filedrop') {
    global $hesk_settings, $hesklang;

    output_dropzone_window();
    output_attachment_id_holder_container($id);

    $acceptedFiles = implode(',', $hesk_settings['attachments']['allowed_types']);
    $size = mfh_bytesToUnits($hesk_settings['attachments']['max_size']);
    $max_files = $hesk_settings['attachments']['max_number'];

    echo "
    <script type=\"text/javascript\">
    Dropzone.options.".$id." = {
        init: function() {
            this.on('success', function(file, response) {
                // The response will only be the ID of the attachment in the database
                outputAttachmentIdHolder(response, '".$id."');

                // Add the database id to the file
                file['databaseId'] = response;
            });
            this.on('addedfile', function() {
                var numberOfFiles = $('#" . $id . " .file-row').length;

                var disabled = false;
                if (numberOfFiles >= " . $max_files . ") {
                    disabled = true;
                }

                $('." . $id . "button-" . $id . "').attr('disabled', disabled);
            });
            this.on('removedfile', function(file) {
                // Remove the attachment from the database and the filesystem.
                removeAttachment(file['databaseId']);

                var numberOfFiles = $('#" . $id . " .file-row').length;

                var disabled = false;
                if (numberOfFiles >= " . $max_files . ") {
                    disabled = true;
                }
                $('." . $id . "button-" . $id . "').attr('disabled', disabled);
            });
            this.on('complete', function(file) {
                // Stop animating if complete.
                $(file.previewTemplate).find('#total-progress').removeClass('active');
            });
            this.on('queuecomplete', function() {
                $('input[type=\"submit\"]').attr('disabled', false);
            });
            this.on('processing', function() {
                $('input[type=\"submit\"]').attr('disabled', true);
            });
            this.on('uploadprogress', function(file, percentage) {
                $(file.previewTemplate).find('#percentage').text(percentage + '%');
            });
            this.on('error', function(file, errorMessage, xhr) {
                $(file.previewTemplate).addClass('alert-danger');
            });
        },
        paramName: 'attachment',
        url: '" . $url . "',
        parallelUploads: ".$max_files.",
        uploadMultiple: true,
        maxFiles: ".$max_files.",
        acceptedFiles: ".json_encode($acceptedFiles).",
        maxFilesize: ".$size.", // MB
        dictDefaultMessage: ".json_encode($hesklang['attachment_viewer_message']).",
        dictFallbackMessage: '',
        dictInvalidFileType: ".json_encode($hesklang['attachment_invalid_type_message']).",
        dictResponseError: ".json_encode($hesklang['attachment_upload_error']).",
        dictFileTooBig: ".json_encode($hesklang['attachment_too_large']).",
        dictCancelUpload: ".json_encode($hesklang['attachment_cancel']).",
        dictCancelUploadConfirmation: ".json_encode($hesklang['attachment_confirm_cancel']).",
        dictRemoveFile: ".json_encode($hesklang['attachment_remove']).",
        dictMaxFilesExceeded: ".json_encode($hesklang['attachment_max_exceeded']).",
        previewTemplate: $('#previews').html(),
        clickable: '.filedropbutton-".$id."',
        uploadMultiple: false
    };
    </script>
    ";

}