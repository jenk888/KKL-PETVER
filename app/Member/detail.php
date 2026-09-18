<?php
include '../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $id = req('id');
    $action = req("action");

    $cart = get_cart();
    $qty = $cart[$id] ?? 0;

    // retrieve stock from database
    $stm = $_db->prepare("SELECT stock FROM product WHERE id =?");
    $stm->execute([$id]);
    $stock = $stm->fetchColumn();

    // check if stock whether is null or not, and based on user action, increase or decrease the quantity in cart
    if (!$stock){
        $stock = 0;
    }

    if ($action === 'increase'){
        if($qty < $stock){
            $qty++;
        }
    }else if($action === 'decrease') {
        $qty = max(0, $qty - 1);
    }

    update_cart($id, $qty);
    redirect();
}

// ----------------------------------------------------------------------------
$id  = req('id');
$stm = $_db->prepare('SELECT p.* ,c.name AS category_name FROM product p JOIN category c ON p.category_id = c.id WHERE p.id =?');
$stm->execute([$id]);
$p = $stm->fetch();

if (!$p) redirect('product.php');

$stm = $_db->prepare('SELECT p.*, pp.filename FROM product p JOIN product_photo pp ON p.id = pp.product_id WHERE p.id=?');
$stm->execute([$id]);
$photos = $stm->fetchAll();

// ----------------------------------------------------------------------------


include '../_head.php';
?>

<style>
    #photo {
        display: block;
        border: 1px solid #333;
        width: 200px;
        height: 200px;
    }

    
    p{
        text-align: center;
    }
</style>

<div class="detail">
    <div class="photo-container">
        <?php foreach ($photos as $photo): ?>
            <img src="/product_photos/<?= $photo->filename ?>" id="photo">
        <?php endforeach ?>
        <?php if(count($photos) > 1): ?>
            <button class="prev">&lt;</button>
            <button class="next">&gt;</button>
        <?php endif; ?>
    </div>
    <section class="right-info">
        <label>Name</label>
        <div><?= $p->name ?></div>

        <label>Brand</label>
        <div><?= $p->brand ?></div>

        <label>Price</label>
        <div><?= sprintf("%.2f",$p->price) ?></div>

        <label>Category</label>
        <div><?= $p->category_name ?></div>

        <label>Description</label>
        <div><?= $p->description ?></div>

        <!-- Change the quantity in cart when user click the button -->
        <label>Quantity</label>
        <div>
            <?php 
            $cart = get_cart();
            $id = $p->id;
            $qty = $cart[$p->id] ?? 0;
            ?>
            <form method = "post">
                <input type= "hidden" name="id" value="<?= $p->id ?>">
                

                <button type="submit" name="action" value="decrease" <?=  $qty <= 0 ? 'disabled' : '' ?>>-</button>

                <span><?=  $qty ?></span>
                <button type="submit" name="action" value="increase" >+</button>
            </form>

        </div>

    </section>
</div>


<p>
    <button data-get="product.php">Back</button>
</p>

<script>
    // Javascript to handle photo slideshow
    let currentIndex = 0;
    const photos = $('.photo-container img');
    photos.hide().eq(0).show(); 

    $('.next').click(() => {
        photos.eq(currentIndex).hide();
        currentIndex = (currentIndex + 1) % photos.length;
        photos.eq(currentIndex).show();
    });
    $('.prev').click(() => {
        photos.eq(currentIndex).hide();
        currentIndex = (currentIndex - 1 + photos.length) % photos.length;
        photos.eq(currentIndex).show();
    });
</script>

<?php
include '../_foot.php';