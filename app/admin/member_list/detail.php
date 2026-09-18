<?php
include '../../_base.php';

auth('Admin', 'Super Admin');

$id = req('id');
$stm = $_db->prepare('SELECT * FROM user WHERE id =?');
$stm->execute([$id]);
$u = $stm->fetch();

if (!$u) redirect('member_list.php');

$title = 'User| Detail';
include '../../_head.php';
?>

<div class="member-detail">
    <h2>Member Detail</h2>

    <div>
        <img src="/user_photo/<?= $u->photo ?>" style="border-radius:50%; height:250px; width:250px; display: block;">
    </div>
    <div class='detail-row'>
        <label>ID</label>
        <div><?= $u->id ?></div>
    </div>

    <div class='detail-row'>
        <label>Name</label>
        <div><?= $u->name ?></div>
    </div>

    <div class='detail-row'>
        <label>Email</label>
        <div><?= $u->email ?></div>
    </div>

    <div class='detail-row'>
        <label>Phone</label>
        <div><?= $u->phone ?></div>
    </div>

    <div class='detail-row'>
        <label>Gender</label>
        <div><?= $u->gender ?></div>
    </div>

    <div class='detail-row'>
        <label>Role</label>
        <div><?= $u->role ?></div>
    </div>

    <?php if ($u->role == 'Member'): ?>
        <div class='detail-row'>
            <label>Points</label>
            <div><?= $u->point ?></div>
        </div>
    <?php endif ?>

    <div class="detail-actions">
        <button data-get="member_list.php">Back</button>
        <button data-get="update.php?id=<?= $u->id ?>">Update</button>
    </div>

</div>