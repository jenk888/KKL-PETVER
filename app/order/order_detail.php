<?php
include '../_base.php';
auth('Admin', 'Super Admin');

$id = req('id');

$stm = $_db->prepare("
SELECT o.*, u.name AS customer_name
FROM `order` o
JOIN user u ON o.user_id = u.id
WHERE o.id = ?
");
$stm->execute([$id]);
$order = $stm->fetch();

if (!$order) {
    die('Order not found');
}

$stm = $_db->prepare("
SELECT i.*, p.name, ph.filename
FROM order_details i
JOIN product p ON i.product_id = p.id
LEFT JOIN product_photo ph
    ON p.id = ph.product_id AND ph.order_num = 0
WHERE i.order_id = ?
");
$stm->execute([$id]);
$items = $stm->fetchAll();

$total = 0;
foreach ($items as $i) {
    $total += $i->quantity * $i->price;
}
$_title = 'Order Detail';
include '../_head.php';
?>
<link rel="stylesheet" href="/css/listing.css">
<div class="container">
    <h1>Order Detail</h1>

    <div class="order-info">
    <p><strong>Order ID:</strong> <?= htmlspecialchars($order->id) ?></p>
    <p><strong>User ID:</strong> <?= htmlspecialchars($order->user_id) ?></p>
    <p><strong>Customer:</strong> <?= htmlspecialchars($order->customer_name) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order->order_status) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($order->datetime) ?></p>
    </div>

    <table class="order-table">
        <tr>
            <th>Photo</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>

        <?php foreach ($items as $i): ?>
        <tr>
            <td><img src="/product_photos/<?= $i->filename ?>" width="80" height="80"></td>
            <td><?= $i->name ?></td>
            <td><?= $i->quantity ?></td>
            <td>RM <?= number_format($i->price, 2) ?></td>
            <td>RM <?= number_format($i->quantity * $i->price, 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

        <div class="order-total">
            <h3>Total: RM <?= number_format($total, 2) ?></h3>
        </div>

        <br>
        <div class="back-btn">
            <a href="order_listing.php" class="btn-back">← Back to Order Listing</a>
        </div>
</div>
