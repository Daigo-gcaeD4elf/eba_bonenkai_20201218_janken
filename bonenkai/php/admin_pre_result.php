<?php
session_start();

require_once('Common.php');
require_once('Db.php');

class AdminPreResult extends Db
{
    public function changeGameState()
    {
        $sql = <<< EOF
            UPDATE
                admin_rock_paper_scissors
            SET
                state = 2
            WHERE
                admin_member_id = 1
            ;
EOF;
        $stmt = $this->dbh->query($sql);
    }

    public function judgeWinOrLose()
    {
        $adminerRps = $this->getAdminerRps();
        if ($adminerRps === '1') {
            $winnerRps = '3';
            $loserRps  = '2';
        } elseif ($adminerRps === '2') {
            $winnerRps = '1';
            $loserRps  = '3';
        } elseif ($adminerRps === '3') {
            $winnerRps = '2';
            $loserRps  = '1';
        }

        $mode = $this->__getMode();
        if ($mode === '0') {
            $winnerRps .= ', '. $adminerRps;
        } else {
            $loserRps .= ', '. $adminerRps;
        }

        // 勝者の処理
        $sql = <<< EOF
            UPDATE
                rock_paper_scissors
            SET
                number_of_wins = number_of_wins + 1
            WHERE
                lose_flg = 0
                AND rps IN ({$winnerRps})
            ;
EOF;
        $stmt = $this->dbh->query($sql);

        // 敗者の処理
        $sql = <<< EOF
            UPDATE
                rock_paper_scissors
            SET
                lose_flg = 1
            WHERE
                lose_flg = 0
                AND rps IN ({$loserRps})
            ;
EOF;
        $stmt = $this->dbh->query($sql);
    }

    public function getAdminerRps()
    {
        $sql = <<< EOF
            SELECT
                admin_choise
            FROM
                admin_rock_paper_scissors
            WHERE
                admin_member_id = 1
EOF;
        $stmt = $this->dbh->query($sql);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    private function __getMode()
    {
        $sql = 'SELECT draw_judge FROM config LIMIT 1';
        $stmt = $this->dbh->query($sql);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }


    public function getNumberOfTimes()
    {
        $sql = <<< EOF
            SELECT
                number_of_times
            FROM
                admin_rock_paper_scissors
            WHERE
                admin_member_id = 1
            ;
EOF;

        $stmt = $this->dbh->query($sql);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getSurvivor()
    {
        $sql = <<< EOF
            SELECT
                GM.member_name AS member_name
            FROM
                rock_paper_scissors RPS
            LEFT JOIN
                game_member GM
            ON
                GM.member_id = RPS.member_id
            WHERE
                GM.login_state = 1
                AND RPS.lose_flg = 0
            ;
EOF;

        $stmt = $this->dbh->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNumOfPlayer()
    {
        $sql = 'SELECT count(member_id) FROM game_member WHERE login_state = 1';
        $stmt = $this->dbh->query($sql);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getNumOfSurvivor()
    {
        $sql = <<< EOF
            SELECT
                COUNT(GM.member_id) AS member_name
            FROM
                rock_paper_scissors RPS
            LEFT JOIN
                game_member GM
            ON
                GM.member_id = RPS.member_id
            WHERE
                GM.login_state = 1
                AND RPS.lose_flg = 0
            ;
EOF;
        $stmt = $this->dbh->query($sql);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }
}

// if (empty($_SESSION['auth'])) {
//     header('Location: admin_login.php');
//     exit;
// }

$adminPreResult = new AdminPreResult();
$adminPreResult->changeGameState();
$adminPreResult->judgeWinOrLose();

$numOfPlayers   = $adminPreResult->getNumOfPlayer();
$numOfSurvivors = $adminPreResult->getNumOfSurvivor();

// $drawJudge = $adminResult->getMode();

$nowAdminerRps = $adminPreResult->getAdminerRps();

if ($nowAdminerRps === '1') {
    $radioOne = ' checked';
    $adminerChoise = 'グー';
} elseif ($nowAdminerRps === '2') {
    $radioTwo = ' checked';
    $adminerChoise = 'チョキ';
} elseif ($nowAdminerRps === '3') {
    $radioThree = ' checked';
    $adminerChoise = 'パー';
}

require_once('../html/admin_pre_result.html');
