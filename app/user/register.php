<?php
include '../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $email    = req('email');
    $password = req('password');
    $confirm  = req('confirm');
    $phone   = req('phone');
    $gender   =req('gender');
    $name     = req('name');
    $f = get_file('photo');
    

    
    if (!$email) {
        $_err['email'] = 'Required';
    }
    else if (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }
    else if (!is_unique($email, 'user', 'email')) {
        $_err['email'] = 'Duplicated';
    }


    if (!$phone) {
        $_err['phone'] = 'Required';
    }
    else if (!preg_match('/^[0-9]{8,11}$/', $phone)) {
        $_err['phone'] = 'Invalid phone number';
    }


    if (!$password) {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }


    if (!$confirm) {
        $_err['confirm'] = 'Required';
    }
    else if (strlen($confirm) < 5 || strlen($confirm) > 100) {
        $_err['confirm'] = 'Between 5-100 characters';
    }
    else if ($confirm != $password) {
        $_err['confirm'] = 'Not matched';
    }

    if (!$name) {
        $_err['name'] = 'Required';
    }
    else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum 100 characters';
    }
    else if (!preg_match('/^[a-zA-Z ]+$/', $name)) {
        $_err['name'] = 'Only letters and spaces allowed';
    }

    if (!$gender) {
        $_err['gender'] = 'Required';
    }

   $photo = 'default_user.jpg';
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Must be image';
        }
        else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Maximum 1MB';
        }
        else {
            $photo = save_photo($f, '../user_photo');
        }
    }

    if (!$_err) {

        // Generate next user ID manually
        $stm = $_db->query("SELECT MAX(CAST(id AS UNSIGNED)) FROM user");
        $maxId = $stm->fetchColumn();

        $newId = $maxId ? $maxId + 1 : 1000;

        // Insert new member
        $stm = $_db->prepare('
            INSERT INTO user (id, email, password, name, photo, phone, gender, role, point)
            VALUES (?, ?, SHA1(?), ?, ?, ?, ?, "Member", 0)
        ');

        $stm->execute([
            $newId,
            $email,
            $password,
            $name,
            $photo,
            $phone,
            $gender
        ]);

        temp('success', 'Welcome to KKL PETVER, ' . $name . '!');
        redirect('/index.php');
    }
    
}


$_title = 'Register Member';
?>

<link rel="stylesheet" href="/css/register.css">

<form method="post" class="form" enctype="multipart/form-data">
    <h1 class="page-title">Register</h1>

    <label>Email</label>
    <div>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>
    </div>

    <label>Phone</label>
    <div>
        <div class="phone-box">
            <span class="prefix">+60</span>
            <?= html_text('phone', 'id="phone" maxlength="10" placeholder="123456789"') ?>
        </div>
        <?= err('phone') ?>
    </div>

    <label>Password</label>
    <div>
        <?= html_password('password', 'maxlength="100"') ?>
        <?= err('password') ?>
    </div>


    <label>Confirm</label>
    <div>
        <?= html_password('confirm', 'maxlength="100"') ?>
        <?= err('confirm') ?>
    </div>

    <label>Name</label>
    <div>
        <?= html_text('name', 'maxlength="100"') ?>
        <?= err('name') ?>
    </div>

    <label>Gender</label>
    <div>
        <div class="gender">
            <label class="radio">
                <input type="radio" name="gender" value="M">
                Male
            </label>
            <label class="radio">
                <input type="radio" name="gender" value="F">
                Female
            </label>
        </div>
        <?=  err('gender') ?>
    </div>

    <label>Photo</label>
        <div>
            <div class="photo-box">
        <label class="upload">
            <?= html_file('photo', 'image/*', 'hidden id="photoInput"') ?>
            <img src="/user_photo/default_user.jpg" id="preview">
        </label>
        <?= err('photo') ?>
</div>
    </div>


    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

