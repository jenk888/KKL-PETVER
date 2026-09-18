<?php
include '../_base.php';

auth('Member');

$id = req('id');
$stm = $_db->prepare('SELECT * FROM `order` WHERE id = ? AND user_id=?');
$stm->execute([$id, $_user->id]);
$o = $stm->fetch();
if (!$o) redirect('shoppingCart.php');

$stm = $_db->prepare('SELECT od.*, p.name, pp.filename, (od.quantity * od.price) AS subtotal FROM order_details AS od, product AS p, product_photo AS pp WHERE od.product_id=p.id AND p.id = pp.product_id AND pp.order_num = 1 AND od.order_id =?');
$stm->execute([$id]);
$arr = $stm->fetchAll();

include '../_head.php';
?>

<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #receipt,
        #receipt * {
            visibility: visible;
        }

        #receipt {
            width: 90%;
            padding: 10px;
        }

        .print-btn {
            display: none;
        }

        .slip-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
    
        .slip {
            width: 100%;
            border-collapse: collapse;
        }

        .slip th,
        .slip td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
    }

</style>
<!-- order receiot with print button and order history button-->
<div id = "receipt">
    <h5 style="font-size:  20px; padding:5px;">🧾 Order Receipt</h5>
    <div class="slip-info">
        <div>
            <label>Order Id: </label>
            <b><?= $o->id ?></b>
            <br><br>

            <label>Name </label> <?= $_user->name ?>
            <br><br>
            
            <label>Phone No. </label><?= $_user->phone ?>
            <br><br>

            <label>Email</label><?= $_user->email ?>
            <br><br>
        </div>
        <div>
            <label>Datetime </label><?= $o->datetime ?>
            <br><br>
        </div>
    </div>

    <table class = "slip">
        <tr>
            <th colspan="2">Product</th>
            <th>UNIT PRICE</th>
            <th>QUANTITY</th>
            <th>SUBTOTAL</th>
        </tr>

        <?php foreach($arr as $p): ?>
        <tr>
            <td><img src="/product_photos/<?= $p->filename ?>" style="height:100px; width:100px"></td>
            <td><?= $p->name ?></td>
            <td><?= $p->price ?></td>
            <td><?= $p->quantity ?></td>
            <td><?= $p->subtotal ?></td>
        </tr>
        <?php endforeach ?>

        <!-- calculatation -->
        <?php 
        $slip_subtotal = array_sum(array_column((array)$arr, 'subtotal'));
        $slip_tax = $slip_subtotal * 0.06;
        $slip_discount = ($slip_subtotal + $slip_tax) - $o->total_amount;
         ?>
        <tr>
            <td colspan="4">Subtotal</td>
            <td>RM <?= sprintf("%.2f",$slip_subtotal) ?></td>
        </tr>

        <tr>
            <td colspan="4">Tax (6%)</td>
            <td>RM <?= sprintf("%.2f",$slip_tax)?></td>
        </tr>

        <?php if($slip_discount > 0): ?>
            <tr>
                <td colspan="4">Discount</td>
                <td>RM <?= sprintf("%.2f",$slip_discount) ?></td>
            </tr>
        <?php endif ?>

        <tr>
            <td colspan="4">Total (after discounts)</td>
            <td>RM <?= sprintf("%.2f",$o->total_amount)?></td>
        </tr>
    </table>
</div>

<button class="print-btn">🧾Print Receipt</button>
<button class="btn-history" onclick="window.location.href='../order/history.php'" >View Order History</button>
<script>
    // print function
    $('.print-btn').on('click', function() {
        window.print();
    });
</script>

<?php
include '../_foot.php';