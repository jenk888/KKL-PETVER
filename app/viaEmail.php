<?php
include '_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $email   = req('email');
    $html    = req('html');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (strlen($email) > 100) {
        $_err['email'] = 'Maximum 100 characters';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }

    
    // Send email
    if (!$_err) {
        //send email
        $m= get_mail();
        $m->addAddress($email);
        $m->isHTML($html);
        $m->send();

        temp('info', 'Email sent');
        redirect();
    }
}

// ----------------------------------------------------------------------------

$_title = 'Demo';
include '_head.php';
?>

<style>
    #body {
        width: 500px;
        height: 200px;
        resize: none;
    }
</style>

<form class="form" method="post">
    <label for="email">Email</label>
    <?= html_text('email', 'maxlength="100"') ?>
    <?= err('email') ?>

    <label></label>
    <?= html_checkbox('html', 'HTML Content') ?>
    <br>

    <section>
        <button>Send</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
include '_foot.php';