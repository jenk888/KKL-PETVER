<?php
include '_base.php';
logout();
temp('info', 'Logout successfully');
redirect('/');
?>