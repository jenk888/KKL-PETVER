<?php
include '../_base.php';
auth('Admin', 'Super Admin');

$status = $_GET['status'] ?? '';

if ($status != '') {
    $stm = $_db->prepare("SELECT * FROM `order` WHERE order_status = ?");
    $stm->execute([$status]);
} else {
    $stm = $_db->prepare("SELECT * FROM `order`");
    $stm->execute();
}

$order = $stm->fetchAll(PDO::FETCH_ASSOC);


$_title = 'Order Listing';
include '../_head.php';
?>
<link rel="stylesheet" href="/css/listing.css">

<div class="container">
    <h1>Order Listing</h1>
    <form method ="get">
        <select name ="status">
            <option value="">All Status</option>
            <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Completed" <?= $status == 'Completed' ? 'selected' : '' ?>>Completed</option>
        </select>
        <button type="submit">Filter</button>
    </form>

    <br>
    <table class="order-table">
        <tr>
            <th>Order ID</th>
            <th>User ID</th>
            <th>Date</th>
            <th>Status</th>
            <th>Total Amount</th>
            <th>Action</th>
        </tr>

        <?php if ($order): ?>
            <?php foreach ($order as $o): ?>
            <tr>
                <td><?= htmlspecialchars($o['id']) ?></td>
                <td><?= htmlspecialchars($o['user_id']) ?></td>
                <td><?= htmlspecialchars($o['datetime']) ?></td>
                <td><?= htmlspecialchars($o['order_status']) ?></td>
                <td>RM <?= number_format($o['total_amount'], 2) ?></td>
                <td>
                    <a href="order_detail.php?id=<?= $o['id'] ?>">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No order records found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <?php if ($status): ?>
        <div class="status">
            <?= htmlspecialchars($status) ?>
        </div>
    <?php endif; ?>

</div>
