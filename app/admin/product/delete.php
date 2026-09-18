<?php
include '../../_base.php';

auth('Admin', 'Super Admin');

if (is_post()){
    $id = req('id');

    $stm = $_db->prepare('SELECT filename FROM product_photo WHERE product_id =?');
    $stm-> execute([$id]);
    $photo = $stm->fetchAll();

    //delete product photo from server if exists
    foreach ($photo as $p){
        $file=__DIR__ . "/../../product_photos/" . $p->filename;
        if(file_exists($file)){
            unlink($file);
        }


    }

    $stm = $_db->prepare('DELETE FROM cart WHERE product_id=?');
    $stm->execute([$id]);
    
    $stm = $_db->prepare('DELETE FROM order_details WHERE product_id=?');
    $stm->execute([$id]);

    $stm = $_db->prepare('DELETE FROM product_photo WHERE product_id =?');
    $stm->execute([$id]);

    $stm = $_db->prepare('DELETE FROM product WHERE id = ?');
    $stm->execute([$id]);
    
    temp('info', "$id deleted successfully");
}

redirect("product.php");