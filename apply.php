<?php
/* 지원서 접수 처리 - index.html 의 지원서가 여기로 전송됩니다 */
require dirname(__FILE__) . '/config.php';

function back($q) {
    header('Location: ./?' . $q . '#apply');
    exit;
}
function field($k, $max) {
    $v = isset($_POST[$k]) ? trim($_POST[$k]) : '';
    if (function_exists('get_magic_quotes_gpc') && @get_magic_quotes_gpc()) { $v = stripslashes($v); }
    $v = str_replace(array("\r\n", "\r"), "\n", $v);
    if (function_exists('mb_substr')) { $v = mb_substr($v, 0, $max, 'UTF-8'); }
    else { $v = substr($v, 0, $max * 3); }
    return $v;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') { back(''); }

// 스팸 방지: 사람 눈에 안 보이는 칸이 채워져 있으면 로봇
if (!empty($_POST['website'])) { back('ok=1'); }

$name    = field('name', 30);
$tel     = field('tel', 20);
$job     = field('job', 30);
$mode    = field('mode', 30);
$area    = field('area', 30);
$message = field('message', 1000);
$agree   = isset($_POST['agree']) ? 1 : 0;

$digits = preg_replace('/[^0-9]/', '', $tel);
if ($name == '' || strlen($digits) < 9 || !$agree) { back('err=1'); }

$ip  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
// Cloudflare 뒤에 있으므로 실제 방문자 IP 사용
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) { $ip = $_SERVER['HTTP_CF_CONNECTING_IP']; }
$ref = isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 255) : '';

$db = pp_db();
$e = 'mysqli_real_escape_string';

// 같은 IP에서 1분 안에 연속 접수 막기
$r = mysqli_query($db, "SELECT COUNT(*) FROM `" . TBL . "` WHERE ip='" . $e($db, $ip) . "' AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
$row = mysqli_fetch_row($r);
if ($row[0] > 0) { back('ok=1'); }

$sql = "INSERT INTO `" . TBL . "` (name, tel, job, mode, area, message, ip, referer, created_at) VALUES ("
     . "'" . $e($db, $name) . "',"
     . "'" . $e($db, $tel) . "',"
     . "'" . $e($db, $job) . "',"
     . "'" . $e($db, $mode) . "',"
     . "'" . $e($db, $area) . "',"
     . "'" . $e($db, $message) . "',"
     . "'" . $e($db, $ip) . "',"
     . "'" . $e($db, $ref) . "',"
     . "NOW())";

if (!mysqli_query($db, $sql)) { back('err=2'); }

// 이메일 알림 (설정한 경우만)
if (NOTIFY_EMAIL != '') {
    $subject = '=?UTF-8?B?' . base64_encode('[편한손 창업] 새 지원서: ' . $name . ' (' . $area . ')') . '?=';
    $body = "이름: $name\n연락처: $tel\n하는 일: $job\n방식: $mode\n지역: $area\n\n$message\n\n관리자: https://pyeonhanson.co.kr/partners/admin.php";
    $headers = "Content-Type: text/plain; charset=UTF-8\r\nFrom: noreply@pyeonhanson.co.kr";
    @mail(NOTIFY_EMAIL, $subject, $body, $headers);
}

back('ok=1');
