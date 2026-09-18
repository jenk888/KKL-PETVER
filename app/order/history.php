<?php
include '../_base.php';

auth('Member');

// ----------------------------------------------------------------------------
// Get member order history
$stm = $_db->prepare('
    SELECT * FROM history
    WHERE user_id = ?
    ORDER BY id DESC
');
$stm->execute([$_user->id]);
$arr = $stm->fetchAll();

$_title = 'Order History';
include '../_head.php';
?>

<link rel="stylesheet" href="/css/history.css">

<style>
    tr:hover .popup {
        display: grid !important;
        grid-template-columns: repeat(5, auto);
        gap: 5px;
        border: none;
    }

    .popup {
        display: none;
        margin-top: 10px;
    }

    .popup img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        outline: 1px solid #333;
        border-radius: 6px;
    }

    .btn-detail {
        display: inline-block;
        padding: 6px 12px;
        background: orange;
        color: white;
        text-decoration: none;
        border-radius: 6px;
    }

    .btn-detail:hover {
        background: darkorange;
    }
</style>

<h1>My Order History</h1>
<p><?= count($arr) ?> record(s)</p>

<table class="table">
    <tr>
        <th>ID</th>
        <th>Datetime</th>
        <th>Count</th>
        <th>Total (RM)</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php foreach ($arr as $order): ?>
        <tr>
            <td><?= $order->id ?></td>
            <td><?= $order->datetime ?></td>
            <td class="right"><?= $order->count ?></td>
            <td class="right"><?= number_format($order->total, 2) ?></td>
            <td><?= $order->status ?></td>
            <td>

                <a href="/order/history_detail.php?id=<?= $order->id ?>" class="btn-detail">
                    Detail
                </a>

                
                <div class="popup">
                    <?php
                    $stm2 = $_db->prepare('
                    SELECT 
                    (
                        SELECT filename
                        FROM product_photo
                        WHERE product_id = i.product_id
                        ORDER BY order_num ASC
                        LIMIT 1
                    ) AS photo
                    FROM order_details i
                    WHERE i.order_id = ?
                    ');
                
                    $stm2->execute([$order->id]);
                    $photos = $stm2->fetchAll(PDO::FETCH_COLUMN);

                    foreach ($photos as $photo):
                    ?>
                        <img src="/product_photos/<?= $photo ?>" alt="Product Photo">
                    <?php endforeach; ?>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
</table>