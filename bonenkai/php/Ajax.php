<?php
require_once('Common.php');
require_once('Db.php');

class Ajax extends Db
{
    /**
    * ゲーム状態取得
    *
    * @return string
    */
    public function chkGameState()
    {
        $sql = 'SELECT state FROM admin_rock_paper_scissors WHERE admin_member_id = 1';
        $stmt = $this->dbh->query($sql);
        echo $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
    * じゃんけんの選択項目変更をDBに反映(ユーザー)
    *
    * @param array $post
    *
    * @return void
    */
    public function changeRps($post)
    {
        $rps = $post['rps'];
        $id  = $post['member_id'];

        $sql = <<< EOF
            UPDATE
                rock_paper_scissors
            SET
                rps = ?
            WHERE
                member_id = ?
        ;
EOF;

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$rps, $id]);

        $rpsNm = '';
        if ($rps === '1') {
            $rpsNm = 'グー';
        } else if ($rps === '2') {
            $rpsNm = 'チョキ';
        } else if ($rps === '3') {
            $rpsNm = 'パー';
        }

        echo $rpsNm;
    }

    /**
    * じゃんけんの選択項目変更をDBに反映(管理者)
    *
    * @param array $post
    *
    * @return void
    */

    public function changeAdminerRps($post)
    {
        $rps = $post['rps'];
        $sql = <<< EOF
            UPDATE
                admin_rock_paper_scissors
            SET
                admin_choise = ?
            WHERE
                admin_member_id = 1
        ;
EOF;

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$rps]);

        $rpsNm = '';
        if ($rps === '1') {
            $rpsNm = 'グー';
        } else if ($rps === '2') {
            $rpsNm = 'チョキ';
        } else if ($rps === '3') {
            $rpsNm = 'パー';
        }

        echo $rpsNm;
    }

//     /**
//     * メンバーのじゃんけんステータスを更新
//     *
//     * @return string
//     */
//     public function updateNumberOfWins()
//     {
//         $db = new Db;
//         $db->connect();

//         $sql = <<< EOF
//             UPDATE
//                 rock_paper_scissors
//             SET
//                 number_of_wins = (
//                     SELECT
//                         number_of_times
//                     FROM
//                         admin_rock_paper_scissors
//                     WHERE
//                         admin_member_id = 1
//                 ) + 1
//             WHERE member_id = :id
//             ;
// EOF;
//         $stmt = $db->dbh->prepare($sql);
//         echo 'ステータス更新完了';
//     }

    /**
    * 次のじゃんけんへ進むがクリックされたかどうかチェック
    *
    * @return string
    */
    public function chkNextGame()
    {
        $sql = <<< EOF
            SELECT
                CONCAT(DATE_FORMAT(renewal_time, '%H%i%s'), state)
            FROM
                admin_rock_paper_scissors
            WHERE admin_member_id = 1
EOF;
        $stmt = $this->dbh->query($sql);
        echo $stmt->fetch(PDO::FETCH_COLUMN);
    }
}

$fnc = $_POST['fnc_name'];
$req = new Ajax();
$req->$fnc($_POST);
