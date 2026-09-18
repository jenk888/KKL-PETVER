<?php
include '../_base.php';

deny('Admin', 'Super Admin');

$user_id = $_user?->id;

// handle add to cart action
if (is_post()){
    $id    = req('id');
    $action = req('action');

    // if is member,get current quantity in cart
    if ($_user?->role == 'Member'){
        $stm = $_db->prepare("SELECT qty FROM cart WHERE user_id=? AND product_id=?");
        $stm->execute([$user_id, $id]);
        $qty = $stm->fetchColumn() ?? 0;
    // if is guest, get cart from session
    }else{
        $cart = get_cart();
        $qty = $cart[$id] ?? 0;  
    }
    
    if ($action == 'increase'){
            $check = $_db->prepare("SELECT stock FROM product WHERE id  = ?");
            $check->execute([$id]);
            $current_stock = $check->fetchColumn();

            // if stock is sufficient, add to cart
            if ($current_stock !== false && $current_stock > $qty){
                if($_user?->role == 'Member'){
                        $stm = $_db->prepare("INSERT INTO cart(user_id, product_id, qty) VALUES (?,?,1) ON DUPLICATE KEY UPDATE qty = qty+1");
                        $stm->execute([$user_id, $id]); 
                }else{
                    update_cart($id,$qty+1);
                }
            // if stock is not sufficient, prompt out of stock message
            }else{
                temp('info', 'Out of stock!');
            }
                
        }    

    elseif ($action == 'decrease') {
        if ($qty > 0){
            // if quantity is greater than 0, decrease the quantity in cart
            if($_user?->role == 'Member'){
                if ($qty == 1){
                    $stm = $_db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                    $stm->execute([$user_id,$id]);
                }else{
                    $stm = $_db->prepare("UPDATE cart SET qty= qty -1 WHERE user_id = ? AND product_id =?");
                    $stm->execute([$user_id,$id]);
                }
            }else{
                update_cart($id, $qty-1);
            }
            
        }
    }
    redirect();
}


$cart = get_cart();

// get member's cart from DB
if ($_user?->role == 'Member'){
    $stm= $_db->prepare('SELECT product_id, qty FROM cart WHERE user_id=? AND qty > 0');
    $stm->execute([$user_id]);
    $cart = $stm->fetchAll(PDO::FETCH_KEY_PAIR);
}

//search bar + filter
$name = req('name');
$category = req('category');
$sql="SELECT p.*, c.name AS category_name, 
    (SELECT filename FROM product_photo 
    WHERE product_id = p.id 
    ORDER BY order_num ASC LIMIT 1) AS filename 
    FROM product p 
    JOIN category c ON p.category_id = c.id 
    WHERE 1";
$para=[];

if ($name != ''){
    $sql.=" AND p.name LIKE ? ";
    $para[] = "%$name%";
}

if ($category != ''){
    $sql.=" AND p.category_id = ? ";
    $para[] = $category;
}

$stm= $_db->prepare($sql);
$stm->execute($para);
$arr = $stm->fetchAll();

include '../_head.php';
?>

<style>
    #product-list {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        justify-content: center;
        padding: 20px;
    }
    
    .product-card {
        border: 1px solid #ddd;
        width: 200px;
        position: relative;
        padding: 10px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: transform 0.2s, background 0.2s;
    }
    
    .product-card:hover {
        background-color: #f5f0e8;
        transform: translateY(-2px);
    }
    
    .product-card img {
        object-fit: cover;
        width: 100%;
        height: 160px;
        border-radius: 6px;
        cursor: pointer;
    }
    
    .product-card .product-info {
        padding: 8px 4px 4px;
        text-align: left;
        font-size: 13px;
        flex-grow: 1;
        width: 100%;
    }
    
    .product-card .product-info .name {
        font-weight: 600;
        margin-bottom: 2px;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-card .product-info .price {
        color: #54502d;
        font-weight: 700;
        font-size: 14px;
    }
    
    .product-card .qty-form {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 6px 0 4px;
        width: 100%;
    }
    
    .product-card .qty-form button {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1px solid #aaa;
        background: #f0f0f0;
        font-size: 16px;
        cursor: pointer;
        line-height: 1;
        padding: 0;
    }
    
    .product-card .qty-form button:disabled {
        opacity: 0.4;
        cursor: default;
    }
    
    .product-card .stock {
        font-size: 11px;
        color: #e74c3c;
        text-align: center;
        padding-bottom: 4px;
    }
    
    /* ===================== LIST VIEW ===================== */
    #product-list.list-view {
        flex-direction: column;
        align-items: stretch;
        padding: 20px 40px;
        gap: 10px;
    }
    
    #product-list.list-view .product-card {
        width: 100%;
        flex-direction: row;
        align-items: center;
        gap: 20px;
        padding: 12px 20px;
        height: auto;
    }
    
    #product-list.list-view .product-card img {
        width: 90px;
        height: 90px;
        flex-shrink: 0;
        border-radius: 8px;
    }
    
    #product-list.list-view .product-card .product-info {
        flex: 1;
        padding: 0;
    }
    
    #product-list.list-view .product-card .name {
        font-size: 16px;
    }
    
    #product-list.list-view .product-card .brand {
        font-size: 12px;
        color: #888;
        margin-top: 2px;
    }
    
    #product-list.list-view .product-card .qty-form {
        width: auto;
        padding: 0;
        flex-shrink: 0;
    }
    
    
    #product-list:not(.list-view) .brand { 
        display: none; 
    }

    .view-btn {
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 5px 10px;
        cursor: pointer;
        font-size: 18px;
    }

    .view-btn.active {
        background: #54502d;
        color: #fff;
        border-color: #54502d;
    }
</style>

<!-- search bar + filter + view toggle button -->
<div class="toolbar">
    <form method="get">
        <?=  html_search('name','style="width: 300px;" placeholder="Search name" ')?>
        <?= html_select('category', $_categories, 'All','style="width:120px;"') ?>
        <button class="insert-btn">Search</button>
    </form>


    <div class="view-toggle">
        <button type="button" class="view-btn" id="btn-grid" title="Grid View" onclick="setView('grid')">⊞</button>
        <button type="button" class="view-btn" id="btn-list" title="List View" onclick="setView('list')">&#9776;</button>
    </div>
</div>
<p style="text-align: center; color: #666; margin: 10px 0 0;"> <?= count($arr) ?> product(s) found</p>
<div id="product-list">

    <!-- loop through products and display them -->
    <?php foreach ($arr as $p): ?>
        <?php
        $id = $p->id;
        $qty = $cart[$id] ?? 0;
        $out = $p->stock <=0;
        ?>

        <div class="product-card">
            <img src = "/product_photos/<?= $p->filename ?>" 
                data-get = "detail.php?id=<?=  $p->id ?>" >

            <div class="product-info">
                <div class="name"><?= $p->name ?> </div>
                <div class="brand"><?= $p->brand ?></div>
                <div class="price">RM <?= sprintf("%.2f", $p->price) ?></div>
            </div>

            <?php if($out): ?>
                <div class="stock">Out of Stock</div>

            <?php else: ?>
                <form method = "post" class="qty-form">
                    <input type= "hidden" name="id" value="<?= $p->id ?>">
                    <input type= "hidden" name="qty" value="<?= $qty ?>">

                    <button type="submit" name="action" value="decrease" <?=  $qty <= 0 ? 'disabled' : '' ?>>-</button>

                    <span><?= $qty ?></span>
                    <button type="submit" name="action" value="increase" >+</button>
                </form>
            <?php endif ?>  
        </div>
    <?php endforeach; ?>
</div>

<script>
//toggle product view between grid and list
$('select').on('change', e=> e.target.form.submit());

// Save user's preferred view in localStorage and apply it on page load
function setView(view) {
    const list = document.getElementById('product-list');
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');

    if(view === 'list'){
        list.classList.add('list-view');
        btnList.classList.add('active');
        btnGrid.classList.remove('active');
    }
    else{
        list.classList.remove('list-view');
        btnGrid.classList.add('active');
        btnList.classList.remove('active');
    }
    localStorage.setItem('productView',view);
}

// On page load, check if user has a saved view preference and apply it
(function(){
    const saved = localStorage.getItem('productView');
    if (saved === 'list') setView('list');
})();
</script>

<?php
include '../_foot.php';