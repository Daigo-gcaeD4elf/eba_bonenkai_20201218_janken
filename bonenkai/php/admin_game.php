<?php
session_start();

require_once('Common.php');
require_once('Db.php');

class AdminGame extends Db
{
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
                        number_of_times
                    FROM
                        admin_rock_paper_scissors
                )
            ;
EOF;

        $stmt = $this->dbh->query($sql);
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function startGame()
    {
        $sql = <<< EOF
            UPDATE admin_rock_paper_scissors
            SET
                renewal_time = NOW()
                ,state = 1
            WHERE
                admin_member_id = 1
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
        $rps = $stmt->fetch(PDO::FETCH_COLUMN);

        return $rps;
    }

    public function stopGame()
    {
        $sql = <<< EOF
            UPDATE admin_rock_paper_scissors
            SET
                state = 0
            WHERE
                admin_member_id = 1
            ;
EOF;

        $stmt = $this->dbh->query($sql);

        $sql = 'SELECT state FROM admin_rock_paper_scissors WHERE admin_member_id = 1';
        $stmt = $this->dbh->query($sql);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function updateNumberOfTimes()
    {
        $sql = <<< EOF
            UPDATE admin_rock_paper_scissors
            SET
                renewal_time = NOW()
                ,number_of_times = number_of_times + 1
            WHERE
                admin_member_id = 1
            ;
EOF;

        $stmt = $this->dbh->query($sql);

        $sql = <<< EOF
            SELECT
                number_of_times
            FROM
                admin_rock_paper_scissors
            WHERE
                admin_member_id = 1
EOF;

        $stmt = $this->dbh->query($sql);
        $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getTimeLimit()
    {
        $sql = <<< EOF
        SELECT
            TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(renewal_time, INTERVAL (
                SELECT
                    time_limit
                FROM
                    config
                WHERE
                    type = 1
            ) SECOND)) AS time_limit
        FROM
            admin_rock_paper_scissors
        WHERE
            admin_member_id = 1
EOF;
        $stmt = $this->dbh->query($sql);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    public function getNumberOfTimes()
    {
        $sql = 'SELECT number_of_times FROM admin_rock_paper_scissors WHERE admin_member_id = 1';
        $stmt = $this->dbh->query($sql);

        return $stmt->fetch(PDO::FETCH_COLUMN);
    }
}

// if (empty($_SESSION['auth'])) {
//     header('Location: admin_login.php');
//     exit;
// }

$adminGame = new AdminGame();
if (!empty($_POST['game_start'])) {
    $adminGame->startGame();
}
$nowAdminerRps = $adminGame->getAdminerRps();
$timeLimit = $adminGame->getTimeLimit();

$numOfPlayers   = $adminGame->getNumOfPlayer();
$numOfSurvivors = $adminGame->getNumOfSurvivor();

$numOfTime = $adminGame->getNumberOfTimes();

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

require_once('../html/admin_game.html');
