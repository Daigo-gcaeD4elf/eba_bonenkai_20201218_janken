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

class Explanation extends Db
{
    public function getUserData()
    {
        $sql = 'SELECT * FROM game_member WHERE member_id = ?';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$_SESSION['member_id']]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMemberState()
    {
        $sql = <<< EOF
            SELECT
                A.member_name
                ,B.number_of_wins
            FROM
                game_member A
            LEFT JOIN
                rock_paper_scissors B
            ON
                A.member_id = B.member_id
            WHERE A.member_id < 15
            ORDER BY B.number_of_wins
EOF;

        $stmt = $this->dbh->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$exp = new Explanation();
$userData = $exp->getUserData();

$members = $exp->getMemberState();

require_once('../html/explanation.html');
