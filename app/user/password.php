<?php
include '../_base.php';

// ----------------------------------------------------------------------------

// Authenticated users

if (is_post()) {
    $password     = req('password');
    $new_password = req('new_password');
    $confirm      = req('confirm');

    // Validate: password
    if ($password == '') {
        $_err['password'] = '*Required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }
    else {
        // TODO
        $stm = $_db->prepare('
            SELECT COUNT(*) FROM user
            WHERE password = SHA1(?) AND id = ?
        ');
        $stm->execute([$password, $_user->id]);
        
        if ($stm->fetchColumn() == 0) {
            $_err['password'] = '*Not matched';
        }
    }

   // Validate: new_password (Strong Password)
    if ($new_password == '') {
        $_err['new_password'] = '*Required';
    }
    else if (strlen($new_password) < 8 || strlen($new_password) > 100) {
        $_err['new_password'] = 'Must be between 8-100 characters';
    }
    else if (!preg_match('/[A-Z]/', $new_password)) {
        $_err['new_password'] = 'Must contain at least 1 uppercase letter';
    }
    else if (!preg_match('/[a-z]/', $new_password)) {
        $_err['new_password'] = 'Must contain at least 1 lowercase letter';
    }
    else if (!preg_match('/[0-9]/', $new_password)) {
        $_err['new_password'] = 'Must contain at least 1 number';
    }
    else if (!preg_match('/[\W]/', $new_password)) {
        $_err['new_password'] = 'Must contain at least 1 special character';
    }
    else if ($new_password == $password) {
        $_err['new_password'] = 'Cannot be same as current password';
    }

    // Validate: confirm
    if (!$confirm) {
        $_err['confirm'] = '*Required';
    }
    else if (strlen($confirm) < 5 || strlen($confirm) > 100) {
        $_err['confirm'] = 'Between 5-100 characters';
    }
    else if ($confirm != $new_password) {
        $_err['confirm'] = '*Not matched';
    }

    // DB operation
    if (!$_err) {

        // Update user (password)
        // TODO
        $stm = $_db->prepare('
            UPDATE user
            SET password = SHA1(?)
            WHERE id = ?
        ');
        $stm->execute([$new_password, $_user->id]);

        temp('info', 'Password has been updated');
        redirect();
    }
}

// ----------------------------------------------------------------------------

$_title = 'Change Password';
include '../_head.php';
?>

<style>
    body{
        background-image: url(pet.jpg);
        margin: 0;
        height: 100vh;
        display: grid;
        grid: auto auto 1fr auto / auto;
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: 100% 100%;
    }

    #password-rules {
        list-style: none;
        padding: 0;
        font-size: 14px;
    }

    #password-rules li {
        color: red;
    }

    #password-rules li.valid {
        color: green;
    }

</style>

<div class="profile-container">

    <form method="post" class="profile-form">

    <label for="password">Password
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?>
    </label>

    <label for="new_password">New Password
    <?= html_password('new_password', 'maxlength="100"') ?>
    <?= err('new_password') ?>

    <ul id="password-rules">
        <li id="rule-length">❌ At least 8 characters</li>
        <li id="rule-upper">❌ At least 1 uppercase letter</li>
        <li id="rule-lower">❌ At least 1 lowercase letter</li>
        <li id="rule-number">❌ At least 1 number</li>
        <li id="rule-symbol">❌ At least 1 special character</li>
    </ul>

    </label>

    <label for="confirm">Confirm
    <?= html_password('confirm', 'maxlength="100"') ?>
    <?= err('confirm') ?>
    </label>

    <section>
        <button>Save</button>
    </section>
</form>

<script>
const passwordInput = document.querySelector('input[name="new_password"]');

const rules = {
    length: document.getElementById('rule-length'),
    upper: document.getElementById('rule-upper'),
    lower: document.getElementById('rule-lower'),
    number: document.getElementById('rule-number'),
    symbol: document.getElementById('rule-symbol')
};

passwordInput.addEventListener('input', function() {
    const value = passwordInput.value;

    toggleRule(rules.length, value.length >= 8);
    toggleRule(rules.upper, /[A-Z]/.test(value));
    toggleRule(rules.lower, /[a-z]/.test(value));
    toggleRule(rules.number, /[0-9]/.test(value));
    toggleRule(rules.symbol, /[\W]/.test(value));
});

function toggleRule(element, valid) {
    if (valid) {
        element.classList.add('valid');
        element.textContent = '✔ ' + element.textContent.slice(2);
    } else {
        element.classList.remove('valid');
        element.textContent = '❌ ' + element.textContent.slice(2);
    }
}

</script>

<?php
include '../_foot.php';