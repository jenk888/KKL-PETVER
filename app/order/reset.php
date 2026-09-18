<?php
include '../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {

    $_db->query('
    DELETE FROM item;
    DELETE FROM `order`;
    ALTER TABLE `order` AUTO_INCREMENT = 1;
    ');

    temp('info', 'Order and item tables reset');
    
}

redirect('history.php');

// ----------------------------------------------------------------------------

?>