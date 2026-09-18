<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

$id  = req('id');
$stm = $_db->prepare('SELECT p.*, c.name AS category_name FROM product p JOIN category c ON p.category_id = c.id WHERE p.id=?');
$stm->execute([$id]);
$p = $stm->fetch();

if (!$p) redirect('list.php');

$stm = $_db->prepare('SELECT p.*, pp.filename FROM product p JOIN product_photo pp ON p.id = pp.product_id WHERE p.id=?');
$stm->execute([$id]);
$photos = $stm->fetchAll();

// ----------------------------------------------------------------------------

$_title = 'Product | Detail';
include '../../_head.php';
?>

<style>
   .photo-container {
        position: relative;
        width: 250px; /* Reduced width */
        height: 250px; /* Fixed height for consistency */
        background: #fdfdfd;
        border: 1px solid #eee;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .photo-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    
    p{
        text-align: center;
    }
</style>

<div class="detail">
    <div class="photo-container">
        <?php foreach ($photos as $photo): ?>
            <img src="/product_photos/<?= $photo->filename ?>" >
        <?php endforeach ?>
        <?php if(count($photos) > 1): ?>
            <button class="prev">&lt;</button>
            <button class="next">&gt;</button>
        <?php endif; ?>
    </div>

    <div class="right-info">
        <label>Name</label>
        <div><?= $p->name ?></div>

        <label>Price</label>
        <div><?= sprintf("%.2f",$p->price) ?></div>

        <label>Category</label>
        <div><?= $p->category_name ?></div>

        <label>Brand</label>
        <div><?= $p->brand ?></div>

        <label>Stock</label>
        <div class="<?= $p->stock <= 0? 'stock-zero' : '' ?>"><?= $p->stock<=0 ? 'Out of stock' : $p->stock ?></div>

        <label>Description</label>
        <div><?= $p->description ?></div>

    </div>
</div>
   
<p>
    <button data-get="product.php">Back</button>
    <button data-get="update.php?id=<?= $p->id ?>">Update</button>
</p>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
// photo slider 
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
include '../../_foot.php';