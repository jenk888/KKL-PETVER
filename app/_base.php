 <?php

 date_default_timezone_set('Asia/Kuala_Lumpur');
 session_start();

$_user = $_SESSION['user'] ?? null;



function is_get(){
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

function is_post(){
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function get($key, $value = null){
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function post($key, $value = null){
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function req($key, $value = null){
    $value = $_REQUEST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

function redirect($url = null){
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

function temp($key, $value = null){
    if($value !== null){
        $_SESSION["temp_$key"] = $value;
    }
    else{
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
 
}

function get_file($key){
    $f = $_FILES[$key] ?? null;

    if($f){
        if (!is_array($f['error']) && $f['error'] == 0){
            return (object)$f;
        }
        if(is_array($f['error']) && $f['error'][0] == 0){
            return (object)$f;
        }
    }
    return null;
}

function save_photo($f, $folder, $width = 200, $height = 200){
    $photo = uniqid(). '.jpg';

    require_once 'lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');
    return $photo;
}

function is_money($value){
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

function is_phone($value){
    return preg_match('/^01\d-\d{7,8}$/',$value);
}

function is_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Return base url (host + port)
function base($path = '') {
    return "http://$_SERVER[SERVER_NAME]:$_SERVER[SERVER_PORT]/$path";
}
// HTML helper
//Placeholder

function encode($value){
    return htmlentities($value);
}

function html_text($key, $attr = ''){
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

function html_comment($key, $attr = ''){
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr>$value</textarea>";
}

function html_number($key, $min = '', $max = '', $step = '', $attr = ''){
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value' min='$min' max='$max' step='$step' $attr>";
}

function html_search($key, $attr = ''){
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}


function html_radios($key, $items, $br = false){
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {   
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

function html_select($key, $items, $default = '-Select One-', $attr = ''){
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null){
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text){
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';

}

function html_email($key, $attr=''){
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='email' id='$key' name='$key' value='$value' $attr>";
}
function html_checkbox($name, $label = '') {
    $checked = req($name) ? 'checked' : '';
    return "<label><input type='checkbox' name='$name' value='1' $checked> $label</label>";
}


function html_file($key, $accept = '', $attr = ''){
    echo"<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}

function html_password($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

function login($user, $url = '/') {
    $_SESSION['user'] = $user;
    redirect($url);
}

function logout($url = '/') {
    unset($_SESSION['user']);
}

// Generate <textarea>
function html_textarea($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr>$value</textarea>";
}


function table_headers($fields, $sort, $dir, $href = ''){
    foreach ($fields as $k => $v) {
        $d = 'asc';
        $c = '';

        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc';
            $c = $dir;
        }
        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}

// error handling

$_err = [];

function err($key){
    global $_err;
    if ($_err[$key] ?? false){
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo'<span></span>';
    }
}

// authorization
function auth(...$roles){
    global $_user;
    if($_user){
        if($roles){
            if (in_array($_user->role, $roles)){
                return;
            }
        }
        else{
            return;
        }
    }
    redirect('/login.php');
}

// Initialize and return mail object
function get_mail() {
    require_once 'lib/PHPMailer.php';
    require_once 'lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->SMTPSecure = 'tls';
    $m->Username = 'annisk07118@gmail.com';
    $m->Password = 'rknmmiuikaoexoyv';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, '🐹 Admin');

    return $m;
}

//block specific roles
function deny(...$roles){
    global $_user;
    if($_user && in_array($_user->role, $roles)){
        redirect('/');
    }
}

// Shopping Cart

function get_cart(){
    return $_SESSION['cart'] ?? [];
}

function set_cart($cart = []){
    $_SESSION['cart'] = $cart;
}

function update_cart($id, $qty){
    global $_db, $_user;

    //Member cart handling
    if ($_user?->role == 'Member'){
        if($qty <= 0){
            $stm = $_db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stm->execute([$_user->id, $id]);
        }
        else{
            $stm= $_db->prepare("UPDATE cart SET qty = ? WHERE user_id =? AND product_id = ?");
            $stm->execute([$qty,$_user->id, $id]);
        }
       return;
    }
    
    // Guest user cart handling
    $cart = get_cart();

    if ($qty >= 1 && $qty<= 10 && is_exists($id, 'product', 'id')) {
        $cart[$id] = $qty;
        ksort($cart);
    }else{
        unset($cart[$id]);
    }

    set_cart($cart);
}

// database setup
$_db = new PDO('mysql:dbname=kkl_petver', 'root','',[
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);

function is_unique($value, $table, $field){
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

function is_exists($value, $table, $field){
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}


$_categories = $_db->query("SELECT id, name FROM category")
                    ->fetchAll(PDO::FETCH_KEY_PAIR);

$_genders = ['F' => 'Female', 'M' => 'Male'];
$_roles = ['Admin' => 'Admin', 'Super Admin' => 'Super Admin','Member' => 'Member'];
$_statuses = ['Blocked' => 'Blocked', 'Active' => 'Active'];




