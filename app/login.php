<?php
include '_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $email = req('email');
    $password = req('password');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }
    // Login user
    if (!$_err) {
        $stm = $_db->prepare('
            SELECT * FROM user
            WHERE email = ? 
        ');
        $stm->execute([$email]);
        $u = $stm->fetch();

        if (!$u) {
            $_err['login'] = 'This account has not been registered.'. ' Please register first.';
        }
        else if ($u->status == 'Blocked') {
            $_err['login'] = 'Your account has been blocked by admin';
        }
        else if ($u->password !=sha1($password)){
            $_err['password'] = 'Not matched';
        }
        else{
            temp('success', 'Welcome back,'. $u->name . '!');
            login($u);
            redirect('/index.php');
        }
    }
}


// ----------------------------------------------------------------------------

$_title = 'Login';

?>
<link rel="stylesheet" href="/css/login.css">

<form method="post" class="login-container">
    <h1 class="page-title">Login</h1>
    <label for="email">Email</label>

    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>


    <label for="password">Password</label>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?>

    <div class="forgot-link">
        <a href="/user/reset_password.php">Forgot Password?</a>
    </div>

    <?php if (!empty($_err['login'])): ?>
    <div class="notification">
        <?= $_err['login'] ?>
    </div>

    <?php if (str_contains($_err['login'], 'not been registered')): ?>
        <div class="register-link">
            Don't have account?
            <a href="register.php">Register now</a>
        </div>
    <?php endif; ?>
<?php endif; ?>

    <section>
        <button>Login</button>
        <button type="reset">Reset</button>
    </section>
</form>