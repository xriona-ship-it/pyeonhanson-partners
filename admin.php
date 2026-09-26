<?php
/* 지원자 관리 화면: https://pyeonhanson.co.kr/partners/admin.php */
require dirname(__FILE__) . '/config.php';
session_start();
header('Content-Type: text/html; charset=utf-8');

// 로그인 / 로그아웃
if (isset($_GET['logout'])) { $_SESSION['pp_admin'] = 0; header('Location: admin.php'); exit; }
if (isset($_POST['pass'])) {
    if ($_POST['pass'] === ADMIN_PASS && ADMIN_PASS != '여기에_관리자_비밀번호') {
        $_SESSION['pp_admin'] = 1;
        header('Location: admin.php'); exit;
    }
    $login_err = 1;
}
$logged = !empty($_SESSION['pp_admin']);
$statuses = array('신규', '통화 완료', '교육 예정', '교육 중', '합류', '보류');
?>
<!DOCTYPE html>
<html lang="ko"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>편한손 창업 지원자 관리</title>
<style>
body{margin:0;font-family:"Apple SD Gothic Neo","Malgun Gothic",sans-serif;background:#F5F4EF;color:#121A2C;font-size:15px}
.wrap{max-width:1100px;margin:0 auto;padding:20px 16px}
h1{font-size:22px;margin:0}
.top{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.stats{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.stats a{background:#fff;border:2px solid #E3E2DC;border-radius:10px;padding:8px 12px;text-decoration:none;color:#121A2C}
.stats a.on{border-color:#121A2C;background:#FFC629}
.card{background:#fff;border-radius:12px;padding:16px;margin-bottom:10px;border-left:6px solid #E3E2DC}
.card.new{border-left-color:#FFC629}
.row{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
.name{font-size:18px;font-weight:700}
.tel{font-size:18px;font-weight:700;color:#121A2C}
.meta{color:#4B5366;font-size:14px;margin-top:4px}
.msg{background:#F5F4EF;border-radius:8px;padding:10px;margin-top:10px;white-space:pre-wrap}
form.inline{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px;align-items:center}
select,input[type=text],input[type=password]{font-size:15px;padding:8px;border:1px solid #ccc;border-radius:8px}
input[type=text].memo{flex:1;min-width:180px}
button{font-size:15px;padding:8px 14px;border:0;border-radius:8px;background:#121A2C;color:#FFC629;cursor:pointer}
button.del{background:#eee;color:#a33}
.login{max-width:320px;margin:80px auto;background:#fff;padding:24px;border-radius:12px}
.login input{width:100%;box-sizing:border-box;margin:10px 0}
.err{color:#a33}
</style></head><body><div class="wrap">
<?php if (!$logged) { ?>
  <form class="login" method="post">
    <h1>지원자 관리</h1>
    <?php if (!empty($login_err)) echo '<p class="err">비밀번호가 맞지 않습니다. (config.php 의 ADMIN_PASS 를 바꿨는지 확인)</p>'; ?>
    <input type="password" name="pass" placeholder="관리자 비밀번호" autofocus>
    <button type="submit" style="width:100%">들어가기</button>
  </form>
<?php } else {
    $db = pp_db();
    $e = 'mysqli_real_escape_string';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if (isset($_POST['delete'])) {
            mysqli_query($db, "DELETE FROM `" . TBL . "` WHERE id=$id");
        } else {
            $st = in_array($_POST['status'], $statuses) ? $_POST['status'] : '신규';
            $memo = isset($_POST['memo']) ? $_POST['memo'] : '';
            if (function_exists('get_magic_quotes_gpc') && @get_magic_quotes_gpc()) { $memo = stripslashes($memo); }
            mysqli_query($db, "UPDATE `" . TBL . "` SET status='" . $e($db, $st) . "', memo='" . $e($db, $memo) . "' WHERE id=$id");
        }
        header('Location: admin.php' . (isset($_GET['s']) ? '?s=' . urlencode($_GET['s']) : '')); exit;
    }

    $counts = array(); $total = 0;
    $r = mysqli_query($db, "SELECT status, COUNT(*) c FROM `" . TBL . "` GROUP BY status");
    while ($row = mysqli_fetch_assoc($r)) { $counts[$row['status']] = $row['c']; $total += $row['c']; }

    $filter = (isset($_GET['s']) && in_array($_GET['s'], $statuses)) ? $_GET['s'] : '';
    $where = $filter ? "WHERE status='" . $e($db, $filter) . "'" : '';
    $list = mysqli_query($db, "SELECT * FROM `" . TBL . "` $where ORDER BY id DESC LIMIT 300");
?>
  <div class="top"><h1>편한손 창업 지원자</h1><div><a href="./" target="_blank">모집 페이지 보기</a> · <a href="?logout=1">로그아웃</a></div></div>
  <div class="stats">
    <a href="admin.php" class="<?php echo $filter ? '' : 'on'; ?>">전체 <?php echo $total; ?></a>
    <?php foreach ($statuses as $s) { ?>
      <a href="?s=<?php echo urlencode($s); ?>" class="<?php echo $filter == $s ? 'on' : ''; ?>"><?php echo h($s); ?> <?php echo isset($counts[$s]) ? $counts[$s] : 0; ?></a>
    <?php } ?>
  </div>
  <?php if (!mysqli_num_rows($list)) echo '<p>아직 지원서가 없습니다.</p>'; ?>
  <?php while ($a = mysqli_fetch_assoc($list)) { ?>
    <div class="card <?php echo $a['status'] == '신규' ? 'new' : ''; ?>">
      <div class="row">
        <div>
          <div class="name"><?php echo h($a['name']); ?></div>
          <div class="meta"><?php echo h($a['job']); ?> · <?php echo h($a['mode']); ?> · <?php echo h($a['area']); ?> · <?php echo h(substr($a['created_at'], 0, 16)); ?></div>
        </div>
        <a class="tel" href="tel:<?php echo h(preg_replace('/[^0-9]/', '', $a['tel'])); ?>"><?php echo h($a['tel']); ?></a>
      </div>
      <?php if ($a['message'] != '') { ?><div class="msg"><?php echo h($a['message']); ?></div><?php } ?>
      <form class="inline" method="post" action="admin.php<?php echo $filter ? '?s=' . urlencode($filter) : ''; ?>">
        <input type="hidden" name="id" value="<?php echo (int)$a['id']; ?>">
        <select name="status"><?php foreach ($statuses as $s) { echo '<option' . ($a['status'] == $s ? ' selected' : '') . '>' . h($s) . '</option>'; } ?></select>
        <input type="text" class="memo" name="memo" value="<?php echo h($a['memo']); ?>" placeholder="메모 (통화 내용 등)">
        <button type="submit">저장</button>
        <button type="submit" class="del" name="delete" value="1" onclick="return window.confirm('삭제할까요?');">삭제</button>
      </form>
    </div>
  <?php } ?>
<?php } ?>
</div></body></html>
