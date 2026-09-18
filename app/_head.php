<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=  $_title ?? 'KKL PETVER' ?></title>
        <link rel="stylesheet" href="/css/style.css">
        <link rel="shortcut icon" href="/paw.png">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script src= "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="/js/app.js"></script>
    </head>
    <body>
        <!-- flash message-->
        <div id="info"><?= temp('info') ?></div>
        <header>
            <h1>KKL Petver</h1>
        </header>

        <!-- sidebar and topbar-->
        <div id="sidebar" class="sidebar">
            <a href="javascript:void(0)" class= "closebtn" onclick="closeNav()">&times;</a> 
            <?php if($_user): ?>
                <img src="/user_photo/<?= !empty($_user->photo) ? $_user->photo : 'default.png' ?>" class="avatar">
            <?php endif ?>
            <a href="/">Home</a>
            <!-- Admin & Manager Links -->
            <?php if ($_user?->role == "Admin" || $_user?->role == "Super Admin"): ?>
                <a href="/admin/product/product.php">Products</a>
                <a href="/admin/member_list/member_list.php">Members</a>
                <a href="/order/order_listing.php">Order_listing</a>
                <a href="/user/profile.php">&#9784; Settings</a>
                
            <?php elseif ($_user?->role == 'Member'):?>
                <a href="/Member/product.php">Shop</a>
                <a href="/Member/shoppingCart.php">Shopping cart</a>
                <a href="/order/history.php">Order History</a>
                <a href="/user/profile.php">&#9784; Settings</a>
                
            
            
            <!-- Guest Links -->
            <?php else: ?>
                <a href="/Member/product.php">Shop</a>
                <a href="/Member/shoppingCart.php">Shopping cart</a>
                <a href="/login.php">Login</a>
                
            <?php endif ?>
        </div>
        <nav>
            <div id="topbar" class="topbar">
                <span style="font-size:30px;color:azure;cursor:pointer;" onclick="openNav()">&#9776;</span>
                
                    <div class="right-side">
                        <a href="/Member/product.php">Shop</a>

                        <?php if($_SERVER['PHP_SELF'] == '/user/profile.php'): ?>
                            <a href="/user/password.php">Password</a>
                        <?php endif ?>

                        <?php if ($_user): ?>
                            <a href="/logout.php">Logout</a>
                        
                        <?php else: ?>
                            <a href="/user/register.php">Register</a>
                            <a href="/login.php">Login</a>
                        <?php endif ?> 
                </div>
            </div>
        </nav>


        
    <main>
           
       
    