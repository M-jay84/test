<?php

namespace Bootstrap\Models;

use Bootstrap\Libs\DB;
use Bootstrap\Libs\Thumbnail;

/*
* Attachments Logic
*/
class AttachmentsLogic
{
    // Download Attachment
    public static function downloadAttachment($id, $hash)
    {
        $file = ATTACHMENT_PATH . "/$hash.data";
        $attachments_query = "SELECT filename FROM attachments WHERE id = ?";
        $attachment =  DB::run($attachments_query, [$id])->fetchColumn();
        $extension = substr($attachment['filename'], -3);
        $array = [
            'id' => $id,
            'extension' => $extension,
            'file' => $file,
        ];
        return $array;
    }

    // Delete Attachment
    public static function deleteAll($id, $post = false)
    {
        if ($post) {
            $attachments_query = "SELECT filename,file_hash,id FROM attachments WHERE content_id = ?";
            $sql = DB::run($attachments_query, $id);
        } else {
            $attachments_query = "SELECT filename,file_hash,id FROM attachments WHERE topicid = ?";
            $sql = DB::run($attachments_query, $id);
        }

        if ($sql->rowCount() != 0) {
            foreach ($sql as $row7) {
                
                $daimage = ATTACHMENT_PATH . "/$row7[file_hash].data";
                if (file_exists($daimage)) {
                    if (unlink($daimage)) {
                        $attachments_query = "DELETE FROM attachments WHERE content_id = ?";
                        DB::run($attachments_query, [$row7['id']]);
                        
                    }
                }
                $extension = substr($row7['filename'], -3);
                if ($extension != 'zip') {
                    $dathumb = UPLOAD_PATH."/thumbnail/$row7[file_hash].jpg";
                    if (!unlink($dathumb)) {
                        message(("post/$id"), "Could not remove thumbnail = $row7[file_hash].jpg");
                    }
                }
            }
        }
    }

    // Get Attachment //todo in view
    public static function get_attachment($id)
    {
        $attachments_query = "SELECT * FROM `attachments` WHERE content_id =?";
        $sql = DB::run($attachments_query, [$id]);
        if ($sql->rowCount() != 0) {
            foreach ($sql as $row7) {
                print("<br><br>&nbsp;<b>$row7[filename]</b><br>");
                $extension = substr($row7['filename'], -3);
                if ($extension == 'zip' || $extension == 'rar' || $extension == 'php') {
                    $daimage = ATTACHMENT_PATH . "/$row7[file_hash].data";
                    if (file_exists($daimage)) {
                        print(" <a class='btn btn-sm ttbtn' href=\"" . URLROOT . "/attachment/$row7[id]/" . rawurlencode($row7["file_hash"]) . "\"><i class='fa fa-file-archive-o tticon' ></i>" . LANG['DOWNLOAD'] . "</a><br>");
                    } else {
                        print("no zip<br>");
                    }
                } else {
                    $daimage = UPLOAD_PATH."/thumbnail/$row7[file_hash].jpg";
                    if (file_exists($daimage)) {
                        $switchimage = ATTACHMENT_PATH . "/$row7[file_hash].data"; ?>
                        <img alt="test image" src="<?php echo data_uri($switchimage, $row7['filename']); ?>" height="100px" width="100px">
                        <?php
                    } else {
                        print("<a href=\"" . URLROOT . "/attachment/$row7[id]/hash=" . rawurlencode($row7["file_hash"]) . "\"><img src='" . URLROOT . "/thumb/$row7[file_hash].jpg' height='110px' width='110px' border='0' alt='' /></a><br>");
                    }
                }
            }
        }
    }

    // Set Attachment
    public static function set_attachment($data = [])
    {
        if (!empty($_FILES['file']['name'][0])) {
            $array = array($_FILES['file']['name']);
            $result = array_filter($array);
            foreach ($result as $k => $ar) :
                // Source, Size & Name
                $sourcePath = $_FILES['file']['tmp_name'][$k];
                $fileSize = $_FILES['file']['size'][$k];
                $fileName = $_FILES['file']['name'][$k];
                // Extension & Hash
                $extension = substr($fileName, -3);
                $hash = bin2hex(random_bytes(25));

                $allowedfileExtensions = array('jpg', 'gif', 'png', 'zip', 'rar', 'php', 'jpeg');
                if (!in_array($extension, $allowedfileExtensions)) {
                    message('forum', "Sorry, only zip, rar, php, JPG, JPEG, PNG, GIF files are allowed.");
                }

                $targetPath = ATTACHMENT_PATH . "/" . $hash . ".data"; // Target path where file is to be stored
                $thumbPath = UPLOAD_PATH . "/thumbnail/" . $hash . ".jpg"; // Target path where attachment as jpg is to be stored

                if ($extension == 'zip' || $extension == 'rar' || $extension == 'php') {
                    move_uploaded_file($sourcePath, $targetPath); // Moving Uploaded file
                    $attachments_query = "INSERT INTO `attachments` (content_id,user_id,upload_date,filename,file_size,file_hash,topicid) VALUES (?,?,?,?,?,?,?)";
                    DB::run($attachments_query,
                    [$data['postid'], $_SESSION['id'], CONFIG['TT_TIME'], $fileName, $fileSize, $hash, $data['topicid']]);
                } else {
                    if (move_uploaded_file($sourcePath, $targetPath)) { // Moving Uploaded file
                        Thumbnail::create()->image($targetPath)->thumbnail(100)->to($thumbPath);
                        $attachments_query = "INSERT INTO attachments (content_id, user_id, upload_date, filename,file_size,
                        file_hash,topicid) VALUES (?, ?, ?, ?,?,?,?)";
                        DB::run($attachments_query, [$data['postid'], $_SESSION['id'], CONFIG['TT_TIME'], $fileName,
                            $fileSize, $hash, $data['topicid']]);
                    }
                }
            endforeach;
        }
    }
}