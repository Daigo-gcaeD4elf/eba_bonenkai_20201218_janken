<?php
session_start();

require_once('Common.php');
require_once('Db.php');

if (!in_array($_POST['rps'], ['0', '1', '2', '3'], true)) {
    header('Location: ../html/login.html');
}

if (empty($_SESSION['member_id'])) {

    if (empty($_POST['member_id'])) {
        session_destroy();
        header('Location: ../html/login.html');
        exit;
    }

    $_SESSION['member_id'] = $_POST['member_id'];
}

class PreResult extends Db
{
    public function getUserData()
    {
        $sql = 'SELECT * FROM game_member WHERE member_id = ?';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$_SESSION['member_id']]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function chkLoseFlg()
    {
        $sql = 'SELECT lose_flg FROM rock_paper_scissors WHERE member_id = ?';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$_POST['member_id']]);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getAdminerChoise()
    {
        $sql = 'SELECT admin_choise FROM admin_rock_paper_scissors LIMIT 1';
        $stmt = $this->dbh->query($sql);
        $stmt->execute([$_POST['username']]);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getNumberOfTimes()
    {
        $sql = 'SELECT number_of_times FROM admin_rock_paper_scissors WHERE admin_member_id = 1';
        $stmt = $this->dbh->query($sql);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getMode()
    {
        $sql = 'SELECT draw_judge FROM config LIMIT 1';
        $stmt = $this->dbh->query($sql);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function judge($yourChoise, $adminerChoise, $drawJudge)
    {
        // DB更新
        $sql = <<< EOF
            UPDATE
                rock_paper_scissors
            SET
                rps = :rps
            WHERE member_id = :id
            ;
EOF;
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([':rps' => $yourChoise, ':id' => $_SESSION['member_id']]);

        $winOrLose = 0;
        // 勝敗判定
        if (
                $yourChoise === '1' && $adminerChoise === '2' // 自分:グー   社長:チョキ
            ||  $yourChoise === '2' && $adminerChoise === '3' // 自分:チョキ 社長:パー
            ||  $yourChoise === '3' && $adminerChoise === '1' // 自分:パー   社長:グー
        ) {
            $winOrLose = 1;
        }

        // アイコは勝ちの場合
        if ($drawJudge === '0') {
            if ($yourChoise === $adminerChoise) {
                $winOrLose = 1;
            }
        }

        return $winOrLose;
    }

}

$preResult = new PreResult();

$userData = $preResult->getUserData();
// $loseFlg  = $preResult->chkLoseFlg();

$yourChoise    = $_POST['rps'];
$adminerChoise = $preResult->getAdminerChoise();

// 既に負けている場合は別の画面へ
if ($yourChoise === '0') {
    $numOfTime = $preResult->getNumberOfTimes();
    require_once('../html/loser_pre_result.html');
    exit;
}

$fmtYourChoise    = $rpsName[$yourChoise];
$fmtAdminerChoise = $rpsName[$adminerChoise];

$drawJudge = $preResult->getMode();

$winOrLose = 0;
if ($yourChoise !== 0) {
    $winOrLose = $preResult->judge($yourChoise, $adminerChoise, $drawJudge);  //勝敗判定フラグ  0:負け  1:勝ち
}

require_once('../html/pre_result.html');
