<?php
/* ============================================================
   편한손 창업 모집 - 설정 파일
   아래 값만 바꿔서 올리세요. (PHP 5.3 / MySQL 5.1 / UTF-8 기준)
   ============================================================ */

// 1) DB 접속 정보 - 호스팅 관리자 페이지에서 확인 (기존 pyeonhanson.co.kr 사이트와 같은 DB를 써도 됩니다)
define('DB_HOST', 'localhost');
define('DB_USER', '여기에_DB_아이디');
define('DB_PASS', '여기에_DB_비밀번호');
define('DB_NAME', '여기에_DB_이름');

// 2) 관리자 화면(admin.php) 비밀번호 - 꼭 바꾸세요
define('ADMIN_PASS', '여기에_관리자_비밀번호');

// 3) 새 지원서가 들어오면 알림 받을 이메일 (안 받으려면 '' 로 두세요)
//    호스팅에서 mail() 발송을 막아둔 경우 알림은 오지 않지만 접수는 정상 저장됩니다.
define('NOTIFY_EMAIL', '');

// 테이블 이름 (기존 사이트 테이블과 겹치지 않게 pp_ 로 시작)
define('TBL', 'pp_applications');

date_default_timezone_set('Asia/Seoul');

function pp_db() {
    $db = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        header('Content-Type: text/html; charset=utf-8');
        exit('DB 접속 실패: config.php 의 DB 정보를 확인해 주세요.');
    }
    mysqli_query($db, "SET NAMES utf8");
    return $db;
}

function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
