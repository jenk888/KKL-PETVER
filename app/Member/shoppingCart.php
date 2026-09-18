<?php
include '../_base.php';

if(is_post()){
    $btn = req('btn');

    // clear the items in cart
    if($btn == 'clear'){
        if ($_user?->role =='Member'){
            $stm = $_db->prepare("DELETE FROM cart WHERE user_id = ?");
            $stm->execute([$_user->id]);
        }else{
            set_cart();    
        }
        // redirect back to cart page after clearing the cart
        redirect('?');
    }

    $id    = req('id');
    $action = req('action');

    if ($_user?->role == 'Member'){
        $stm = $_db->prepare("SELECT qty FROM cart WHERE user_id=? AND product_id=?");
        $stm->execute([$_user->id, $id]);
        $qty = $stm->fetchColumn() ?? 0;
    }else{
        $cart = get_cart();
        $qty = $cart[$id] ?? 0;  
    }
    
    if ($action == 'increase'){
            $check = $_db->prepare("SELECT stock FROM product WHERE id  = ?");
            $check->execute([$id]);
            $current_stock = $check->fetchColumn();

            // if stock is sufficient, add to cart
            if ($current_stock != false && $current_stock > $qty){
                if($_user?->role == 'Member'){
                        $stm = $_db->prepare("UPDATE cart SET qty = qty + 1 WHERE user_id = ? AND product_id = ?");
                        $stm->execute([$_user->id, $id]); 
                }else{
                    update_cart($id,$qty+1);
                }
            }
        }
    elseif ($action == 'decrease') {
        if ($qty > 1){
            if($_user?->role == 'Member'){
                    $stm = $_db->prepare("UPDATE cart SET qty = qty - 1 WHERE user_id = ? AND product_id = ?");
                    $stm->execute([$_user->id, $id]); 
            }else{
                update_cart($id,$qty-1);
            }
        }elseif ($qty == 1){
            if($_user?->role == 'Member'){
                $stm = $_db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stm->execute([$_user->id, $id]);
            }else{
                update_cart($id,0);
            }
        }
    }
    // handle remove action when user click the remove button
    elseif ($action == 'remove'){
        if($_user?->role == 'Member'){
            $stm = $_db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stm->execute([$_user->id, $id]);
        }
        else{
            update_cart($id,0);
        } 
    }

    //preserve query parameters for redirect after page resubmission
    $query = http_build_query([
        'use_points' => $_GET['use_points'] ?? '',
        'promo_code' => $_GET['promo_code'] ?? '',
    ]);

    redirect('?'.$query);
}

// get cart items for display
$items = [];

//if user is member, get cart items form database, otherwise get cart items from session
if ($_user?->role == "Member"){
    $stm = $_db->prepare("
        SELECT p.*, c.qty, (SELECT filename FROM product_photo WHERE product_id = p.id ORDER BY order_num ASC LIMIT 1) AS filename
        FROM cart c JOIN product p ON c.product_id = p.id 
        WHERE c.user_id =? 
        ");
    $stm->execute([$_user->id]);
    $items = $stm->fetchAll();
}else{
    $cart = get_cart();
    if ($cart) {
        $stm = $_db->prepare("
            SELECT p.*, (SELECT filename FROM product_photo WHERE product_id = p.id ORDER BY order_num ASC LIMIT 1) AS filename
            FROM product p 
            WHERE p.id =? 
            ");
        //loop throught cart items and get product details from database
        foreach ($cart as $id => $qty) {
            $stm->execute([$id]);
            $p = $stm->fetch();
            if ($p) {
                $p->qty = $qty;
                $items[] = $p;
            }
        }
    }    
}


// calculate the total 
$subtotal = 0;
$total_points = 0;
$user_points = 0;

foreach ($items as $p) {
    $subtotal += $p->price * $p->qty;
    $total_points += $p->price * $p->qty * 0.1; 
} 

$tax = $subtotal * 0.06;
$deduction = 0;

// handle point deduction
if ($_user?->role == 'Member' && isset($_GET['use_points'])) {
    $stm = $_db->prepare("SELECT point FROM user WHERE id=?");
    $stm->execute([$_user->id]);
    $user_points = $stm->fetchColumn();
    $deduction = min ($user_points, $subtotal + $tax);
}

// handle promo code
$promoCode = trim($_GET['promo_code'] ?? '');
$promo_discount = 0;
$voucher = null;

//if user enter a promo code, validate the promo code and calculate the discount
if ($promoCode) {
    $stm = $_db->prepare("SELECT * FROM voucher WHERE code=? AND status= 'active' AND expiry_date >= CURDATE()");
    $stm->execute([$promoCode]);
    $voucher = $stm->fetch();

    // if promo code valid, calculate the discount based on discount type and discount value
    if ($voucher) {

        if ($voucher->discount_type == 'percent'){
            $promo_discount = $subtotal * ($voucher->discount_value / 100);

            if($voucher->max_discount) {
                $promo_discount = min($promo_discount, $voucher->max_discount);
            }
        }else {
            $promo_discount = $voucher->discount_value;
        }
        $promo_discount = min($promo_discount, $subtotal + $tax);
    }
}


$total = $subtotal + $tax - $deduction - $promo_discount;

include '../_head.php';
?>

<div class="split-container">
        <section class="cart-left">
            <div class="selection-header">
                <h1 class="page-title">SHOPPING CART</h1>
                <p class="subtitle">Handpicked essentials for a mindful pet lifestyle. Review your curated items before we prepare them for delivery.</p>
            </div>

            <!-- if there are items in cart, display the cart items, otherwise display empty cart message and link to product page -->
            <?php if ($items): ?>
            <div class="product-table">
                <div class="table-header">
                    <span class="col-product">PRODUCT</span>
                    <span class="col-quantity">QUANTITY</span>
                    <span class="col-subtotal">SUBTOTAL</span>
                    <span class="col-actions"></span>
                </div>

                <?php foreach ($items as $p): ?>
                <div class="product-item">
                    <div class="product-details col-product">
                        <img src="../product_photos/<?= $p->filename ?>" class="product-image">
                        <div class="product-text">
                            <h2 class="product-name"><?= $p->name ?></h2>
                            <p class="product-brand"><?= $p->brand ?></p>
                            <span class="product-price"><?= sprintf("%.2f",$p->price) ?></span>
                        </div>
                    </div>

                    <div class="quantity-controls col-quantity">
                        <form method="post" style="display: inline">
                            <input type="hidden" name="id" value="<?= $p->id ?>">
                            <button name="action" value="decrease" <?= $p->qty <=1 ? 'disabled' : '' ?>>-</button>
                        </form>
                        <span class="qty-value"><?= $p->qty ?></span>
                        <form method="post" style="display: inline">
                            <input type="hidden" name="id" value="<?= $p->id ?>">
                            <button name="action" value="increase" <?= $p->qty >= $p->stock ? 'disabled' : '' ?>>+</button>
                        </form>
                    </div>

                    <div class="subtotal-amount col-subtotal">RM<?= sprintf('%.2f',$p->price * $p->qty) ?></div>
                    <div class="product-actions col-actions">
                        <form method="post" style="display: inline">
                            <input type="hidden" name="id" value="<?= $p->id ?>">
                            <button class="remove-btn" name="action" value="remove">🗑️</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>

            <!-- display member benefits banner -->
            <div class="benefit-banners">
                <div class="banner member-benefits">
                    <span class="banner-icon">✨</span>
                    <div class="banner-text">
                        <h3>Member Benefits</h3>
                        <p>You’re earning <?= sprintf('%.2f',$total_points) ?> Curator Points with this purchase. Redeem them for future organic treats.</p>
                    </div>
                </div>   
            </div>
            <?php else: ?>
                <p>Your cart is empty.
                    <a href="product.php">Shop now</a>
                </p>
            <?php endif; ?>
        </section>

        <!-- Order summary and checkout section -->
        <aside class="cart-right">
            <div class="order-summary">
                <h2>Order Summary</h2>
                
                <div class="summary-line">
                    <span>Subtotal</span>
                    <span class="price">RM <?= sprintf('%.2f', $subtotal)  ?></span>
                </div>
                <div class="summary-line green-text">
                    <span>Eco-Shipping</span>
                    <span class="price">FREE</span>
                </div>
                <div class="summary-line">
                    <span>Estimated Tax(6%)</span>
                    <span class="price">RM <?= sprintf('%.2f',$tax) ?></span>
                </div>

                <?php if ($deduction > 0): ?>
                <div class="summary-line">
                    <span>Point Deduction</span>
                    <span class="price" >RM <?= sprintf('%.2f', $deduction) ?></span>
                </div>
                <?php endif ?>

                <?php if ($promo_discount > 0): ?>
                <div class="summary-line">
                    <span>Promo <?=  $voucher->code ?? $promoCode ?></span>
                    <span class="price" >RM <?= sprintf('%.2f', $promo_discount) ?></span>
                </div>
                <?php endif ?>
                
                <!-- if is member, show the option to use points and enter promo code -->
                <?php if ($_user?->role == 'Member'): ?>
                    <form method="get">
                        <label>
                            <input type="checkbox" name="use_points" value="1"
                                <?= isset($_GET['use_points']) ? 'checked' : '' ?>>
                                Use points RM <?= sprintf('%.2f', $user_points ?? 0) ?>
                        </label>
                        <br>
                        <div class="promoCode">
                            <?=  html_text('promo_code', 'placeholder="Promo Code"') ?>
                            <button type="submit">Apply</button>
                        </div>
                    </form>
                    <?php endif ?>

                <div class="total-line summary-line">
                    <span>Total Amount</span>
                    <span class="total-price">RM <?= sprintf('%.2f',$total) ?></span>
                </div>
                
                <?php if($items): ?>
                    <?php if($_user?->role == 'Member'): ?>
                        <form method="post" action="checkout.php">
                            <input type="hidden" name="use_points" value="<?= isset($_GET['use_points']) ? 1 : 0 ?>">
                            <input type="hidden" name="promo_code" value="<?= htmlspecialchars($promoCode) ?>">
                            <button type="submit" class="secure-checkout">CHECKOUT</button>
                        </form>
                    <?php else: ?>
                        <a href="/login.php">Login to Checkout</a>
                    <?php endif; ?>
                    <form method="post">
                        <button name="btn" value="clear" style="background:#54502d; cursor:pointer; border-radius: 5px; border:none;color:white;">
                            Clear Cart
                        </button>
                    </form>
                <?php endif; ?>

                <div class="promise-box">
                    <div class="promise-header">
                        <span class="check-icon">✓</span>
                        <span>Organic Curator Promise</span>
                    </div>
                    <p>30-day "Happy Paw" return policy. If they don’t love it, we'll make it right.</p>
                </div>
            </div>
        </aside>
    </div>

<?php
include '../_foot.php';


