<?php
include '../../_base.php';

auth('Admin', 'Super Admin');


//sorting page
// 
$fields = ['id' => 'ID', 'name' => 'Name', 'gender' => 'Gender','email' => 'Email', 'phone' => 'Phone', 'role' => 'Role'];

$sort = req('sort');
key_exists($sort,$fields) || $sort = 'id';

$dir = req('dir');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

$page = req('page',1);
require_once '../../lib/SimplePager.php';


//search bar
$name = req('name');
$role = req('role');
$status = req('status');

$sql= 'SELECT * FROM user WHERE 1=1';
$para = [];

if ($_user->role != "Super Admin"){
    $sql.= " AND role = 'Member'";
}

if ($role != ''){
    $sql.=" AND role = ?";
    $para[]= $role;
}

if ($name != ''){
    $sql.=" AND name LIKE ? ";
    $para[] = "%$name%";
}

if ($status != ''){
    $sql.=" AND status = ?";
    $para[] = $status;
}

$sql .= " ORDER BY $sort $dir";
$p = new SimplePager($sql,$para, 10, $page);
$_users = $p->result;


// block and unblock function

if (is_post()) {
    $id = req('id');
//prevent block admin
    if ($id == $_user->id) {
        temp('info', 'Cannot block yourself');
        redirect('/index.php');
    }

    $stm = $_db->prepare('SELECT status FROM user WHERE id = ?');
    $stm->execute([$id]);
    $status = trim(strtolower($stm->fetchColumn()));

    $action = req('action');

    if ($action == 'block') {
        $newStatus = 'Blocked';
        $message = 'Blocked';
    }
    else {
        $newStatus = 'Active';
        $message = 'Unblocked';
    }

    $_db->prepare('UPDATE user SET status = ? WHERE id = ?')
        ->execute([$newStatus, $id]);

    temp('info', "User $message successfully");
}


$_title = ' User List';
include '../../_head.php';
?>

<style>

    form{
        display: flex;
        gap: 8px;
        align-items: center;
    }
</style>

<div class="toolbar">
    <form>
        <?= html_search('name','style="width: 200px;" placeholder="Search name" ')?>
        <?=  html_select('role', $_roles, 'All', 'style="width:120px;"') ?>
        <?=  html_select('status',$_statuses,'All', 'style="width:120px;"') ?>
        <button class="insert-btn">Search</button>
    </form>
</div>  

    <p style="text-align: center;"> <?= count($_users) ?> record(s)</p>
<div class="add">
    <?php if ($_user->role == 'Super Admin') : ?>
    <button data-get='insert.php' class="insert-btn">Add Admin</button>
    <?php endif; ?>
</div>  

<table class="member-list">
    
    <tr>
        <?= table_headers($fields, $sort, $dir, "page=$page") ?>
        <th>Photo</th>
        <th>Action</th>
    </tr>
    <?php foreach ($_users as $u): ?>
        <tr>
            <td ><?= $u->id?></td>
            <td ><?= $u->name?></td>
            <td ><?= $u->gender?></td>
            <td ><?= $u->email?></td>
            <td><?= $u->phone ?></td>
            <td ><?= $u->role?></td>
            <td ><img src="/user_photo/<?= $u->photo ?: 'default_user.jpg' ?>" style="width: 100px; height: 100px;"></td>

            <td>
                <button data-get="detail.php?id=<?= $u->id ?>" class="icon">&#xf06e;</button>
                <button data-get='update.php?id=<?= $u->id ?>' class='icon'>&#xf304;</button>
                <button data-post='delete.php?id=<?= $u->id ?>' data-confirm='Delete member?' class='icon'>&#xf2ed;</button>

                <form method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $u->id ?>">
                    <input type="hidden" name="action" value="<?= $u->status == 'Active' ? 'block' : 'unblock' ?>">

                    <button type="submit"
                            class="icon"
                            onclick="return confirm('<?= $u->status == 'Active' ? 'Block' : 'Unblock' ?> this user?')">
                        <?= $u->status == 'Blocked' ? '&#xf023;' : '&#xf09c;' ?>
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>

</table>
<br>
<?= $p->html("sort=$sort&dir=$dir") ?>

<?php
include '../../_foot.php';