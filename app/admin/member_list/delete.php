<?php
include '../../_base.php';

auth('Admin', 'Super Admin');

if (is_post()){
    $id = req('id');

    $stm = $_db->prepare('SELECT photo FROM user WHERE id =?');
    $stm-> execute([$id]);
    $photo = $stm->fetchColumn();

    // delete user photo from server if exists
    if($photo) {
        $file = "../../user_photo/$photo";
        if (file_exists($file)){
            unlink($file);
        }
    }
    

    $stm = $_db->prepare('DELETE FROM user WHERE id = ?');
    $stm->execute([$id]);
    temp('info', "$id deleted successfully");
}

redirect('member_list.php');