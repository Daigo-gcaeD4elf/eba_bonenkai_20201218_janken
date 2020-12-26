<?php
session_start();

require_once('Common.php');
require_once('Db.php');

class AdminResult extends Db
{
    public function changeGameState()
    {
        $sql = <<< EOF
            UPDATE
                admin_rock_paper_scissors
            SET
                state = 3
            WHERE
                admin_member_id = 1
            ;
EOF;
        $stmt = $this->dbh->query($sql);
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
                AND RPS.number_of_wins = (
                    SELECT
                        number_of_times + 1
                    FROM
                        admin_rock_paper_scissors
                )
            ;
EOF;

        $stmt = $this->dbh->query($sql);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function chkState()
    {
        $sql = <<< EOF
            SELECT
                state
            FROM
                admin_rock_paper_scissors
            WHERE
                admin_member_id = 1
EOF;
        $stmt = $this->dbh->query($sql);

        return $stmt->fetch(PDO::FETCH_COLUMN);
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

    public function finishGame()
    {
        $sql = <<< EOF
            UPDATE admin_rock_paper_scissors
            SET
                state = 0
                ,renewal_time = NOW()
                ,number_of_times = 0
            WHERE
                admin_member_id = 1
            ;
EOF;
        $stmt = $this->dbh->query($sql);

        $sql = <<< EOF
            UPDATE
                rock_paper_scissors
            SET
                number_of_wins = 0
                ,lose_flg = 0
            ;
EOF;
        $stmt = $this->dbh->query($sql);
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

    public function nextGame()
    {
        $sql = <<< EOF
            UPDATE admin_rock_paper_scissors
            SET
                renewal_time = NOW()
                ,state = 1
                ,number_of_times = number_of_times + 1
            WHERE
                admin_member_id = 1
            ;
EOF;

        $stmt = $this->dbh->query($sql);
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
                AND lose_flg = 0
            ;
EOF;

        $stmt = $this->dbh->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWinner($adminerRps)
    {
        $winnerRps = '';
        if ($adminerRps === '1') {
            $winnerRps = '3';
        } elseif ($adminerRps === '2') {
            $winnerRps = '1';
        } elseif ($adminerRps === '3') {
            $winnerRps = '2';
        }

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
                AND RPS.rps = {$winnerRps}
            ;
EOF;

        $stmt = $this->dbh->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDrawer($adminerRps)
    {
        $sql = 'SELECT draw_judge FROM config LIMIT 1';
        $stmt = $this->dbh->query($sql);

        $drawJudge = $stmt->fetch(PDO::FETCH_COLUMN);

        $drawerRps = $adminerRps;

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
                AND RPS.rps = {$drawerRps}
            ;
EOF;

        $stmt = $this->dbh->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLoser($adminerRps)
    {
        $loserRps = '';
        if ($adminerRps === '1') {
            $loserRps = '2';
        } elseif ($adminerRps === '2') {
            $loserRps = '3';
        } elseif ($adminerRps === '3') {
            $loserRps = '1';
        }

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
                AND RPS.number_of_wins = (
                    SELECT
                        number_of_times
                    FROM
                        admin_rock_paper_scissors
                )
            AND
                RPS.rps = {$loserRps}
            ;
EOF;

        $stmt = $this->dbh->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMode()
    {
        $sql = 'SELECT draw_judge FROM config LIMIT 1';
        $stmt = $this->dbh->query($sql);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

}

// if (empty($_SESSION['auth'])) {
//     header('Location: admin_login.php');
//     exit;
// }

$adminResult = new AdminResult();
$adminResult->changeGameState();

// 次のじゃんけんへ
if (!empty($_POST['next'])) {
    $adminResult->nextGame();
    header('Location: ./admin_game.php');
    exit;
}

// ゲーム終了
if (!empty($_POST['finish'])) {
    $adminResult->finishGame();
    header('Location: ./admin.php');
    exit;
}

$adminerRps = $adminResult->getAdminerRps();

$numOfPlayers   = $adminResult->getNumOfPlayer();
$numOfSurvivors = $adminResult->getNumOfSurvivor();

$winners = $adminResult->getWinner($adminerRps);
$drawers = $adminResult->getDrawer($adminerRps);
$losers  = $adminResult->getLoser($adminerRps);

$drawJudge = $adminResult->getMode();

if ($drawJudge === '0') {
    $survivors = array_merge($winners, $drawers);
} else {
    $survivors = $winners;
}

$numOfTimes = $adminResult->getNumberOfTimes() + 1;

require_once('../html/admin_result.html');
