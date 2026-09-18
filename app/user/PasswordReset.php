<?php
include '../_base.php';


if (is_post()) {
    $email = req('email');

   
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }
    else if (!is_exists($email, 'user', 'email')) {
        $_err['email'] = 'Not exists';
    }

    
    if (!$_err) {
       
        $stm = $_db->prepare('SELECT * FROM user WHERE email = ?');
        $stm->execute([$email]);
        $u = $stm->fetch();

        
        $id = sha1(uniqid() . rand());
        $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        
        $stm = $_db->prepare('
            DELETE FROM token WHERE user_id = ?');
        $stm->execute([$u->id, $id, $u->id]);

        $stm = $_db->prepare('
            INSERT INTO token (id, expired, user_id) VALUES (?, ?, ?)');
        $stm->execute([$id, $expiry, $u->id]);
    
        $url = base("user/token.php?id=$id");

        // Send 
        $m = get_mail();
        $m->addAddress($u->email, $u->name);
        $m-> isHTML(true);
        $m->Subject = 'Reset Password';
        $m->Body = "
            <img src='cid:photo'
                 style='width: 200px; height: 200px;
                        border: 1px solid #333'>
            <p>Dear $u->name,<p>
            <h1 style='color: red'>Reset Password</h1>
            <p>
                Please click <a href='$url'>here</a>
                to reset your password.
            </p>
            <p>From, 🐾 Admin</p>
        ";
        $m->send();
        temp('info', 'Email sent');
        redirect('/');
    }
}

$_title = 'User | Reset Password';
include '../_head.php';
?>
<link rel="stylesheet" href="/css/PasswordReset.css">
<form method="post" class="form">
    <label for="email">Email</label>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>

    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
include '../_foot.php';