<?php
session_start();

require_once('Common.php');
require_once('Db.php');

if (empty($_SESSION['member_id'])) {

    if (empty($_POST['member_id'])) {
        session_destroy();
        header('Location: ../html/login.html');
        exit;
    }

    $_SESSION['member_id'] = $_POST['member_id'];
}

class Result extends Db
{
    public function getUserData()
    {
        $sql = 'SELECT * FROM game_member WHERE member_id = ?';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$_SESSION['member_id']]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
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
                AND RPS.number_of_wins = (
                    SELECT
                        number_of_times + 1
                    FROM
                        admin_rock_paper_scissors
                )
            AND
                RPS.rps = {$winnerRps}
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
                AND RPS.number_of_wins = (
                    SELECT
                        number_of_times + (1 - {$drawJudge})
                    FROM
                        admin_rock_paper_scissors
                )
            AND
                RPS.rps = {$drawerRps}
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

    public function getSurvivor()
    {
//         $survivorRps = '';
//         if ($adminerRps === '1') {
//             $survivorRps = '2';
//         } elseif ($adminerRps === '2') {
//             $survivorRps = '3';
//         } elseif ($adminerRps === '3') {
//             $survivorRps = '1';
//         }

//         $sql = <<< EOF
//             SELECT
//                 GM.member_name AS member_name
//             FROM
//                 rock_paper_scissors RPS
//             LEFT JOIN
//                 game_member GM
//             ON
//                 GM.member_id = RPS.member_id
//             WHERE
//                 GM.login_state = 1
//                 AND RPS.number_of_wins = (
//                     SELECT
//                         number_of_times
//                     FROM
//                         admin_rock_paper_scissors
//                 )
//             AND
//                 RPS.rps = {$loserRps}
//             ;
// EOF;

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
}

$result = new Result();

$userData = $result->getUserData();

$adminerRps = $result->getAdminerRps();

$numOfPlayers   = $result->getNumOfPlayer();
$numOfSurvivors = $result->getNumOfSurvivor();

$winners = $result->getWinner($adminerRps);
$drawers = $result->getDrawer($adminerRps);
$losers  = $result->getLoser($adminerRps);

$drawJudge = $result->getMode();

// if ($drawJudge === '0') {
//     $survivors = array_merge($winners, $drawers);
// } else {
//     $survivors = $winners;
// }

$survivors = $result->getSurvivor();

$numOfTimes = $result->getNumberOfTimes() + 1;

require_once('../html/result.html');
