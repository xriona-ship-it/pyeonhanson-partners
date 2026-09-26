<?php
/* 처음 한 번만 실행: https://pyeonhanson.co.kr/partners/install.php
   "설치 완료"가 뜨면 이 파일은 FTP에서 지우세요. */
require dirname(__FILE__) . '/config.php';
header('Content-Type: text/html; charset=utf-8');

$db = pp_db();
$sql = "CREATE TABLE IF NOT EXISTS `" . TBL . "` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(30) NOT NULL,
  `tel` VARCHAR(20) NOT NULL,
  `job` VARCHAR(30) NOT NULL DEFAULT '',
  `mode` VARCHAR(30) NOT NULL DEFAULT '',
  `area` VARCHAR(30) NOT NULL DEFAULT '',
  `message` TEXT,
  `status` VARCHAR(20) NOT NULL DEFAULT '신규',
  `memo` TEXT,
  `ip` VARCHAR(45) NOT NULL DEFAULT '',
  `referer` VARCHAR(255) NOT NULL DEFAULT '',
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_created` (`created_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";

if (mysqli_query($db, $sql)) {
    echo '<h2>설치 완료</h2><p>이제 FTP에서 install.php 파일을 지워주세요.</p><p><a href="admin.php">관리자 화면으로 가기</a></p>';
} else {
    echo '<h2>설치 실패</h2><p>' . h(mysqli_error($db)) . '</p>';
}
