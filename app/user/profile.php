<?php
include '../_base.php';

// ----------------------------------------------------------------------------

// Authenticated users
auth();

if (is_get()) {
    
    $stm = $_db->prepare('SELECT * FROM user WHERE id = ?');
    $stm->execute([$_user->id]);
    $u = $stm->fetch();

    if (!$u) {
        redirect('/');
    }

    extract((array)$u);
    $phone = $u->phone ?? '';
    $gender = $u->gender ?? '';
    $_SESSION['photo'] = $u->photo;
}

if (is_post()) {
    $email = req('email');
    $name  = req('name');
    $phone = req('phone');
    $gender = req('gender');
    $photo = $_SESSION['photo'];
    $f = get_file('photo');

    // Validate: email
    if ($email == '') {
        $_err['email'] = '*Required';
    }
    else if (strlen($email) > 100) {
        $_err['email'] = '*Maximum 100 characters';
    }
    else if (!is_email($email)) {
        $_err['email'] = '*Invalid email*';
    }
    else {
        // TODO
        $stm = $_db->prepare('
            SELECT COUNT(*) FROM user
            WHERE email = ? AND id !=?
        ');
        $stm->execute([$email, $_user->id]);

        if ($stm->fetchColumn() > 0) {
            $_err['email'] = '* Please use another email address.';
        }
    }

    // Validate: name
    if ($name == '') {
        $_err['name'] = '*Required';
    }
    else if (strlen($name) > 100) {
        $_err['name'] = '*Maximum 100 characters';
    }

    // Validate: photo (file) --> optional
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = '*Must be image';
        }
        else if ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = '*Maximum 1MB';
        }
    }

    // Validate: phone
    if ($phone == '') {
        $_err['phone'] = '*Required';
    }
    else if (!is_phone($phone)) {
        $_err['phone'] = '*Invalid phone number format e.g.012-34567890';
    }
    else if (strlen($phone) > 12) {
        $_err['phone'] = '*Maximum 12 characters';
    }

    // Validate: gender
    if ($gender == '') {
        $_err['gender'] = '*Required';
    }
    else if (!in_array($gender, ['M', 'F', 'N'])) {
        $_err['gender'] = '*Invalid selection';
    }

    // DB operation

    if (!$_err) {

        // (1) Delete and save photo --> optional
        if ($f) {
            $old = "../user_photo/$photo";
            if (!empty($photo) && $photo != 'default_user.jpg' && file_exists($old)) {
                unlink($old);
            }
            $photo = save_photo($f, '../user_photo');
        }
        
        // (2) Update user (email, name, photo)
        // TODO
        $stm = $_db->prepare('
            UPDATE user
            SET email = ?, name = ?, photo = ?, phone = ?, gender = ?
            WHERE id = ?
        ');
        $stm->execute([$email, $name, $photo, $phone, $gender, $_user->id]);

        // (3) Update global user object
        $_user->email = $email;
        $_user->name = $name;
        $_user->photo = $photo;
        $_user->phone = $phone;
        $_user->gender = $gender;
        $_SESSION['user'] = $_user;

        temp('info', 'Record updated');
        
    }
}

// ----------------------------------------------------------------------------

$_title = 'Profile';
include '../_head.php';
?>

<style>

    body{
        background-image: url(../images/profile_back.jpg);
        margin: 0;
        height: 100vh;
        display: grid;
        grid: auto auto 1fr auto / auto;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: 100% 100%;
    }

    .upload img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 2px solid #333;
    object-fit: cover;
    display: block;
    margin: 10px auto;
    cursor: pointer;
}

    #editBtn {
        background: none;
        border: none;
        color: #2d29ee;
        font-size: 16px;
        cursor: pointer;
        text-decoration: underline;
        font-weight: bold;
    }
    
    body.profile-page {
        background: url(../images/profile_back.jpg) no-repeat center / cover;
    }

</style>

<div class="profile-container">

    <form method="post" class="profile-form" enctype="multipart/form-data">
    <h2><?php echo $_user->name . "'s Profile"; ?></h2>
        <button type="button" id="editBtn" onclick="enableEdit()">Edit Profile</button>

        <label for="photo" class="upload">
            <input type="file" name="photo" id="photo" accept="image/*" hidden disabled>
            <img src="/user_photo/<?= $photo ?>">
        </label>

        <label for="name">Name
        <input type="text" name="name" value="<?= $name ?>"disabled>
        <?= err('name') ?>
        </label>

        <label for="phone">Phone
        <input type="text" name="phone" value="<?= $phone ?>"disabled>
        <?= err('phone') ?>
        </label>

        <label for="email">Email
        <input type="email" name="email" value="<?= $email ?>"disabled>
        <?= err('email') ?>
        </label>

        <label>Gender
        <select name="gender" disabled>
            <option value="M" <?= $gender == 'M' ? 'selected' : '' ?>>Male</option>
            <option value="F" <?= $gender == 'F' ? 'selected' : '' ?>>Female</option>
            <option value="N" <?= $gender == 'N' ? 'selected' : '' ?>>None</option>
        </select>

        </label>

        <button type="submit" id="saveBtn" style="display:none;">Save</button>

    </form>
</div>

<script>

function enableEdit() {
    let inputs = document.querySelectorAll('.profile-form input, .profile-form select');

    inputs.forEach(input => {
        input.disabled = false;
    });

    document.getElementById('editBtn').style.display = 'none';
    document.getElementById('saveBtn').style.display = 'block';
}
</script>

<?php
include '../_foot.php';