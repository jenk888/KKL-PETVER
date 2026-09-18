<?php
include '../_base.php';

auth('Member');

if(is_post()){
    $stm =$_db->prepare("SELECT product_id, qty FROM cart WHERE user_id=?");
    $stm->execute([$_user->id]);
    $cart = $stm->fetchAll(PDO::FETCH_KEY_PAIR);

    $user_points = req('use_points');
    $promo_code = req('promo_code');

    try{
        $_db->beginTransaction();

        // generate new order ID
        $stm = $_db->query("SELECT id FROM `order` ORDER BY id DESC LIMIT 1");
        $lastID = $stm->fetchColumn();

        if ($lastID) {
            $num = (int) substr($lastID, 5);
            $num ++;
        }else{
            $num = 1;
        }
        $id = '26ORD' . str_pad($num, 4, '0', STR_PAD_LEFT);

        $stm = $_db->prepare('INSERT INTO `order` (id,datetime,user_id, order_status, total_amount) VALUES (?,NOW(),?,"Pending", 0)');
        $stm->execute([$id,$_user->id]);

        $stm_detail= $_db->prepare('INSERT INTO order_details (order_id,product_id,quantity, price) 
                VALUES (?,?,?,(SELECT price FROM product WHERE id =?))');
        
        $stm_stock = $_db->prepare('UPDATE product SET stock = stock - ? WHERE id =? AND stock>=?');

        foreach ($cart as $product_id => $qty){
            $stm_detail->execute([$id, $product_id, $qty, $product_id]);
            $stm_stock->execute([$qty, $product_id,$qty]);
        }

        // calculate total, tax, points deduction, promo discount and final total
        $stm =  $_db->prepare('SELECT SUM(quantity * price) FROM order_details WHERE order_id = ?');
        $stm->execute([$id]);
        $base_total = (float)$stm->fetchColumn();
        $tax = $base_total * 0.06;
        $deduction = 0;
        $promo_discount = 0;

        if ($user_points) {
            $stm = $_db->prepare('SELECT point FROM user WHERE id=?');
            $stm->execute([$_user->id]);
            $user_points = (float)$stm->fetchColumn();
            $deduction = min($user_points, $base_total + $tax);

            $_db->prepare('UPDATE user SET point = point - ? WHERE id = ?')
                ->execute([$deduction, $_user->id]);
        }

        if ($promo_code){
            $stm = $_db->prepare("SELECT * FROM voucher WHERE code= ? AND status = 'active' AND expiry_date >=CURDATE()");
            $stm->execute([$promo_code]);
            $voucher = $stm->fetch();

            if($voucher){
                if ($voucher->discount_type == 'percent'){
                    $promo_discount = $base_total * ($voucher->discount_value / 100);
                    if ($voucher->max_discount){
                        $promo_discount = min($promo_discount, $voucher->max_discount);
                    }
                }else{
                    $promo_discount = $voucher->discount_value;
                }
                $promo_discount = min($promo_discount, $base_total + $tax);
            }
        }

        // prevent final total being nagative and update order total
        $final_total = max(0, $base_total + $tax - $deduction - $promo_discount);
        $_db->prepare('UPDATE `order` SET total_amount = ? WHERE id = ?')
            ->execute([$final_total, $id]);
        
            
        $total_count = array_sum($cart);
        $stm = $_db->prepare('
        INSERT INTO history (id, user_id, datetime, count, total, status)
        VALUES (?, ?, NOW(), ?, ?, "Pending")
        ');
        $stm->execute([
            $id,
            $_user->id,
            $total_count,
            $final_total
        ]);
        

        $points_earned = round($base_total * 0.1 ,2);
        $_db->prepare('UPDATE user SET point = point + ? WHERE id = ?')
            ->execute([$points_earned, $_user->id]);

        $stm = $_db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stm->execute([$_user->id]);

        $_db->commit();

        set_cart();
        temp('info', "order: $id placed successfully");
        redirect("slip.php?id=$id");
    }
    // if any error occurs, rollback the transaction and show error message
    catch(Exception $e){
        $_db->rollBack();
        temp('info', "Checkout failed:". $e->getMessage());
        redirect('shoppingCart.php');
    }
} else{
    redirect('shoppingCart.php');
}

