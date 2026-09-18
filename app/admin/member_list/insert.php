<?php
require '../../_base.php';
// ----------------------------------------------------------------------------
auth("Super Admin");
$folder = "../../user_photo";

// auto generated id
$stm = $_db->query("SELECT id FROM user ORDER BY id DESC LIMIT 1");
$lastID = $stm->fetchColumn();

if ($lastID) {
    $num = (int) substr($lastID, 1);
    $num ++;
}else{
    $num = 1;
}
$id = 'M' . str_pad($num, 4, '0', STR_PAD_LEFT);

if (is_post()) {
    // Input
    $name = req('name');
    $phone = req('phone');
    $email = req('email');
    $gender = req('gender');
    $role = req("role");
    $f = get_file('photo');
    
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = '*Must be image';
        }
        else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = '*Maximum 1MB';
        }
    }

    if ($name == '') {
        $_err['name'] = 'Required';
    }
    else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum length 100';
    }

    if ($phone == ''){
        $_err['phone'] = 'Required';
    }
    else if (!is_phone($phone)) {
        $_err['phone'] = 'Invalid phone format e.g.012-34567890';
    }

    // Validate gender
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email format';
    }


    if ($gender == '') {
        $_err['gender'] = 'Required';
    }
    else if (!array_key_exists($gender, $_genders)) {
        $_err['gender'] = 'Invalid value';
    }

    if ($role == '') {
        $_err['role'] = 'Required';
    }
    else if (!array_key_exists($role, $_roles)) {
        $_err['role'] = 'Invalid value';
    }

    // insert to database
    if (!$_err) {
        // default photo and password
        $photo = 'default_user.jpg';
        $hash= sha1('123456');

        if ($f) {
            $photo = save_photo($f, $folder);
        }
        $stm = $_db->prepare('INSERT INTO user (name, phone, email, gender, role, photo, id, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stm->execute([$name,$phone,$email,$gender,$role, $photo,$id, $hash]);

        temp('info','Record inserted successfully');
        redirect('member_list.php');
    }
}

// ----------------------------------------------------------------------------
$_title = ' User | Insert';
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

<div class = "form-container">
    <h2>Create User</h2>

    <form method="post" class="form-grid" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="id-row">
            <span >ID</span>
            <span class="id-badge"><?= $id ?></span>
        </div>

        <div class="full">
            <label for="name">Name</label>
            <?= html_text('name', 'maxlength="100"') ?>
            <?= err('name') ?>
        </div>

        <div>
            <label for="phone">Phone</label>
            <?= html_text('phone', 'maxlength="12" placeholder="012-3456789"') ?>
            <?= err('phone') ?>
        </div>
        
        <div>
            <label for="email">Email</label>
            <?= html_email ('email', "novalidate") ?>
            <?= err('email') ?>
        </div>
        
        <div class="full">
            <label for="gender">Gender</label>
            <?= html_select('gender', $_genders) ?>
            <?= err('gender') ?>
        </div>

        <div class="full">
                <label>Role</label>
                <?=  html_select('role', $_roles) ?>
                <?= err('role') ?>
            </div>

            <div class="upload-container full">
                <label>Photo</label>
                <label class= "upload-label" tabindex="0">
                    <img src="/user_photo/<?= 'default_user.jpg' ?>" alt="Upload Photo" style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;">
                    <?=  html_file('photo', 'image/*','hidden') ?>
                </label>
                <?= err('photo') ?>
            </div>

        <div class="actions">
            <button type="submit">Submit</button>
            <button type="reset">Reset</button>
        </div>
    </form>
</div>

<?php
include '../../_foot.php';