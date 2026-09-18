<?php
include '../../_base.php';


auth('Admin','Super Admin');

// sorting
$fields = ['id' => 'ID', 'name' => 'Name','category' => 'Category', 'price' => 'Price','stock'=>'Stock', 'brand'=>'Brand' ];

$sort = req('sort');
key_exists($sort,$fields) || $sort = 'id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page',1);
require_once '../../lib/SimplePager.php';


//search bar + filter
$name = req('name');
$category = req('category');
$sql="SELECT p.*, c.name AS category_name, (SELECT filename FROM product_photo WHERE product_id = p.id ORDER BY order_num ASC LIMIT 1) AS filename FROM product p JOIN category c ON p.category_id = c.id WHERE 1";
$para=[];

if ($name != ''){
    $sql.=" AND p.name LIKE ? ";
    $para[] = "%$name%";
}

if ($category != ''){
    $sql.=" AND p.category_id = ? ";
    $para[] = $category;
}

$sql .= " ORDER BY p.$sort $dir";
$s = new SimplePager($sql,$para, 10, $page);
$arr = $s->result;



$_title = 'Product | Admin';
include '../../_head.php';
?>


<!-- search bar & filter-->
<div class="toolbar">
    <form class="toolbar">
        <?= html_search('name','style="width: 300px;" placeholder="Search name" ')?>
        <?= html_select('category', $_categories, 'All','style="width:120px;"') ?>
        <button class="insert-btn">Search</button>
    </form>
</div>

<p style="text-align: center;"><?= count($arr) ?> record(s)</p>

<div class ='add'>    
    <p style="text-align: right;">
    <button data-get="insert.php" class="insert-btn">ADD</button>
    </p>
</div>
    <table class= "table">
        <tr>
            <?= table_headers($fields, $sort, $dir, "page=$page") ?>
            <th>Photo</th>
            <th>Action</th>
            
        </tr>
        <?php foreach ($arr as $p): ?>
        <tr>
            <td style="width: 100px; text-align:center;"><?= $p->id ?></td>
            <td style="width: 250px"><?= $p->name ?></td>
            <td style="width: 150px"><?= $p->category_name?></td>
            <td style="width: 50px"><?= sprintf('%.2f',$p->price) ?></td>
            <td style="width: 50px"><?= $p->stock ?></td>
            <td style="width: 100px"><?= $p->brand ?></td>
            <td style="width: 100px"><img src="/product_photos/<?= $p->filename ?? 'default.png' ?>" style="width: 100px; height: 100px;"></td>
            <td style="width: 100px">
                <button data-get="detail.php?id=<?= $p->id ?>" class="icon">&#xf06e;</button>
                <button data-get="update.php?id=<?= $p->id ?>" class="icon">&#xf304;</button>
                <button data-post="delete.php?id=<?= $p->id ?>" class="icon" data-confirm="Delete this item?">&#xf2ed;</button>
            </td>
        </tr>
        <?php endforeach ?>
    </table>
    <br>
    <?= $s->html("sort=$sort&dir=$dir") ?>
</div>


<?php
include '../../_foot.php';