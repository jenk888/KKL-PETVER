<?php
include '../../_base.php';

const MAX_PHOTOS =4;
$id = req('id');
$stmPhotos = $_db->prepare('SELECT * FROM product_photo WHERE product_id = ?');
$stmPhotos->execute([$id]);
$photos = $stmPhotos->fetchAll();


if (is_get()) {

    $stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
    $stm->execute([$id]);
    $s = $stm->fetch();


    if (!$s){
        redirect('product.php');
    }

    extract((array)$s);
    $category = $category_id; 
    
}

if (is_post()) {

    $name  = req('name');
    $price = req('price');
    $category = req('category');
    $description =req('description');
    $stock = req('stock');
    $brand = req('brand');
    $f = $_FILES['photo'] ?? []; 
    
    // handle photo deletion
    if (isset($_POST['delete_photo'])){
        $photo_id = $_POST['delete_photo'];

        $stm = $_db->prepare('SELECT filename FROM product_photo WHERE id = ? AND product_id = ?');
        $stm->execute([$photo_id, $id]);
        $file_to_delete = $stm->fetchColumn();

        if ($file_to_delete){
            $path =__DIR__.'/../../product_photos/' . $file_to_delete;

            if (file_exists($path)){
                unlink($path);
            }

            $stm = $_db->prepare('DELETE FROM product_photo WHERE id= ? AND product_id=?');
            $stm->execute([$photo_id, $id]);

            temp('info', 'Photo deleted');
        }
        redirect("update.php?id=$id");
        exit;
    }

    // Validate: name
    if ($name == '') {
        $_err['name'] = 'Required';
    }
    else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum 100 characters';
    }

    if (empty($category)) {
        $_err['category'] = 'Required';
    }
   

    // Validate: price
    if ($price == '') {
        $_err['price'] = 'Required';
    }
    else if (!is_money($price)) {
        $_err['price'] = 'Must be money';
    }
    else if ($price < 0.01 || $price > 999.99) {
        $_err['price'] = 'Must between 0.01 - 999.99';
    }

    if ($stock == '') {
        $_err['stock'] = 'Required';
    }
    else if (!is_numeric($stock) || $stock < 0 || $stock > 1000) {
        $_err['stock'] = 'Must be number between 0 - 1000';
    }

    if ($brand == '') {
        $_err['brand'] = 'Required';
    }
    
    if ($description == '') {
        $_err['description'] = 'Required';
    }

    $valid_uploads = [];
    if (!empty($f['name'][0])) {
        if (count($photos) + count($f['name']) > MAX_PHOTOS){
            $_err['photo'] = 'Maximum ' . MAX_PHOTOS . ' photos allowed';
        } else{
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $allowed = ['image/jpeg' => 'jpg', 'image/png'=>'png','image/webp'=>'webp'];

            foreach ($f['name'] as $i => $fname){
                if ($f['error'][$i] !== UPLOAD_ERR_OK){
                    continue;
                }
                $mime = $finfo->file($f['tmp_name'][$i]);

                if (!isset($allowed[$mime])) {
                    $_err['photo'] = "$fname invalid image type";
                    break;
                }
                if ($f['size'][$i] > 1 * 1024 * 1024) {
                    $_err['photo'] = "$fname exceeds 1MB";
                    break;
                }
                $valid_uploads[$i] = $allowed[$mime];
            }
        }
        
    }

    // DB operation
    if (!$_err) {
        foreach($valid_uploads as $i =>$ext){
            $filename = uniqid() . '.' . $ext;
            $target = __DIR__.'/../../product_photos/'.$filename;

            if (move_uploaded_file($f['tmp_name'][$i], $target)){
                $stm = $_db->prepare("SELECT COALESCE(MAX(order_num),-1)+1 FROM product_photo WHERE product_id = ?");
                $stm->execute([$id]);
                $order_num= $stm->fetchColumn();

                $stmInsert = $_db->prepare('INSERT INTO product_photo (product_id,filename,order_num) VALUES (?,?,?)');
                $stmInsert->execute([$id,$filename, $order_num]);
            }
        }
        $stm = $_db->prepare('UPDATE product SET name = ?, description = ?, price = ?, stock = ?, brand = ?, category_id= ? WHERE id = ?');
        $stm->execute([$name, $description, $price, $stock, $brand, $category, $id]);

        temp('info', 'Record updated');
        redirect('product.php');
    }
}
    

  
// ----------------------------------------------------------------------------

$_title = 'Product | Update';
include '../../_head.php';
?>
<style>
:root {
    --primary: #54502d;
    --err-color: #e74c3c;
    --border: #ccc;
}

.form-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 2rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.2rem;
    max-width: 850px;
    margin: 2rem auto;
    padding: 2rem;
    font-family: sans-serif;
}

/* Logic for spanning full width */
.full, .id-row, .photo-section, .actions { grid-column: 1 / -1; }

.id-row { font-size: 1.1rem; margin-bottom: 1rem; }

label {
    display: block;
    font-weight: bold;
    font-size: 0.85rem;
    text-transform: uppercase;
    color: #555;
    margin-bottom: 5px;
}

select {
    width: 100%;
    padding: 10px;
    padding-right: 30px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: white;
    cursor: pointer;
    text-overflow: ellipsis; 
    white-space: nowrap;
    height: 40px;
    line-height: 20px;
}

.form-grid{
    min-width: 0;
}

input, textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--border);
    border-radius: 4px;
    box-sizing: border-box;
}

.photo-section {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 4px;
    margin-top: 1rem;
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.photo-item {
    position: relative;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.photo-item img {
    width: 100%;
    height: 110px;
    object-fit: cover;
    display: block;
}

/* Position delete button as an overlay */
.photo-item button {
    position: absolute;
    top: 5px; right: 5px;
    background: rgba(255,255,255,0.8);
    color: var(--err-color);
    border: 1px solid var(--err-color);
    border-radius: 3px;
    cursor: pointer;
    padding: 2px 6px;
    font-weight: bold;
}

.actions {
    text-align: right;
    padding-top: 1.5rem;
    border-top: 1px solid #eee;
}

.btn-save {
    background: var(--primary);
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
}

</style>
 

<div class="form-container">
    <form method="post" class="form-grid" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="id-row">
            <span >Product ID</span>
            <span class="id-badge"><?= $id ?></span>
        </div>

        <div class="full">
            <label for="name">Name</label>
            <?= html_text('name', 'maxlength="100"') ?>
            <?= err('name') ?>
        </div>

        <div>
            <label for="category">Category</label>
            <?= html_select('category', $_categories) ?>
            <?= err('category') ?>
        </div>

        <div>
            <label for="brand">Brand</label>
            <?= html_text('brand', 'maxlength="100"') ?>
            <?= err('brand') ?>
        </div>

        <div>
            <label for="price">Price</label>
            <?= html_number('price', 0.01, 999.99, 0.01) ?>
            <?= err('price') ?>
        </div>

        <div>
            <label for="stock">Stock: </label>
            <?= html_number('stock', 0, 1000, 1) ?>
            <?= err('stock') ?>
        </div>
 
        <div class="full">
            <label for="description">Description: </label>
            <?= html_textarea('description', 'style="width:300px; height :100px; resize:none;"') ?>
            <?= err('description') ?>
        </div>

        <div class="photo-section">
            <label >Photos (<?= count($photos) ?>/<?= MAX_PHOTOS ?>)</label>
            <input type="file" name="photo[]" accept="image/*" multiple>
            <?= err('photo') ?>
            <div class="photo-grid">
            <?php foreach ($photos as $photo): ?>
                <div class="photo-item">
                    <img src="/product_photos/<?= $photo->filename ?>">
                    <button type="submit" name="delete_photo" value="<?= $photo->id ?>" onclick="return confirm('Delete?')">✕</button>
                </div>
            <?php endforeach; ?>
        </div>
        </div>

        <div class="actions">
            <button class="btn-save">Submit</button>
            <button type="reset">Reset</button>
        </div>
    </form>
</div>


<?php
include '../../_foot.php';

