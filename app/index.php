<?php
require '_base.php';

$products = $_db->query('SELECT p.* , (SELECT filename FROM product_photo WHERE product_id = p.id ORDER BY order_num ASC LIMIT 1) AS filename FROM product p LIMIT 8')->fetchAll();
$vouchers = $_db->query("SELECT * FROM voucher WHERE status ='active'")->fetchAll();

include '_head.php';
?>
<!-- Home Page -->
<section class="hp-top">
  <div class = "hp-content">
    <h1>Welcome to KKL Petver</h1>

    <div class = 'hp-img'>
      <img src="pet.jpg">
      <a href="/Member/product.php" class="btn" style="background: #fffdfd; color: #333">SHOP</a>  
    </div>

  </div>
</section>

<!-- Voucher Banner and Product List -->
<?php if ($vouchers) : ?>
  <h2 class="title">Promotions</h2>

  <div class = "voucher-banner">
    <?php foreach($vouchers as $v): ?>
      <img src="voucher/<?= $v->image ?>">
    <?php endforeach ?>
  </div>
<?php endif ?>

<h2 class ="title">Products</h2>
<div class= "product-list">
  <?php foreach($products as $p): ?>
    <a href="/Member/product.php?id=<?= $p->id ?>" class="card">
      <img src = "product_photos/<?= $p->filename ?>">
      <h3><?= $p->name ?></h3>
      <p>RM <?= $p->price ?></p>
    </a>
  <?php endforeach ?>
</div>


<?php
include '_foot.php';

