<?php
include '../_base.php';

auth('Member');

$id = req('id');

$stm = $_db->prepare('
    SELECT * FROM `order`
    WHERE id = ? AND user_id = ?
');
$stm->execute([$id, $_user->id]);
$order = $stm->fetch();
if (!$order){
    temp('info', 'Order not found');
    redirect('history.php');
}
$stm = $_db->prepare('
SELECT od.*, p.name,
(
    SELECT filename
    FROM product_photo
    WHERE product_id = p.id
    ORDER BY order_num ASC
    LIMIT 1
) AS photo
FROM order_details od
JOIN product p ON od.product_id = p.id
WHERE od.order_id = ?
');
$stm->execute([$id]);
$arr = $stm->fetchAll();


$_title = 'Order | Detail';
include '../_head.php';
?>
<link rel="stylesheet" href="/css/detail.css">
<div class="order-container">
    <h1>Order Detail</h1>

    <div class="order-info">
        <p><strong>Order ID:</strong> <?= $order->id ?></p>
        <p><strong>Date:</strong> <?= $order->datetime ?></p>
        <p><strong>Status:</strong> <?= $order->order_status ?></p>
        <p><strong>Total:</strong> RM <?= number_format($order->total_amount, 2) ?></p>
    </div>

    <table class="table">
        <tr>
            <th>Photo</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price (RM)</th>
            <th>Subtotal (RM)</th>
        </tr>

        <?php foreach ($arr as $item): ?>
            <tr>
                <td>
                    <img src="/product_photos/<?= $item->photo ?>" width="60">
                </td>
                <td><?= $item->name ?></td>
                <td><?= $item->quantity ?></td>
                <td><?= number_format($item->price, 2) ?></td>
                <td><?= number_format($item->quantity * $item->price, 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="/order/history.php" class="btn-detail">Back to History</a>
</div>


