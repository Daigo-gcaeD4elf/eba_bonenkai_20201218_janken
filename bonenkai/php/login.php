<?php
session_start();

require_once('Common.php');
require_once('Db.php');

class Login extends Db
{
    /**
     * パスワードチェック
     *
     * @return array
     */
    public function chkUserPass()
    {
        $sql = <<< EOF
            SELECT
                member_id
                ,member_name
            FROM
                game_member
            WHERE
                user_id = :id
            AND password = :pass
EOF;
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([':id' => $_POST['id'], ':pass' => $_POST['password']]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * パスワードチェック
     *
     * @return void
     */
    public function updateLoginState($memberId)
    {
        $sql = <<< EOF
            UPDATE
                game_member
            SET
                login_state = 1
            WHERE
                member_id = :id
EOF;
        $stmt =  $this->dbh->prepare($sql);
        $stmt->execute([':id' => $memberId]);

        // return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$login = new Login();

$loginErr = '';
// ログインボタン
if (!empty($_POST['send'])) {

    $userInfo = $login->chkUserPass();

    if (!empty($userInfo)) {
        $_SESSION['member_id']   = $userInfo['member_id'];
        $_SESSION['member_name'] = $userInfo['member_name'];

        $login->updateLoginState($userInfo['member_id']);
        header('Location: routing.php');
        exit;
    }
    $loginErr = 'ユーザー名とパスワードが一致しません';
}
session_destroy();

require_once('../html/login.html');
