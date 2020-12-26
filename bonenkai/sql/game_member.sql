CREATE TABLE game_member (
    member_id SERIAL PRIMARY KEY
    ,member_name CHAR(20)
    ,mail_address VARCHAR(50) UNIQUE
    ,whether_to_send_mail TINYINT DEFAULT 0
    ,user_id CHAR(20)
    ,password VARCHAR(2000)
    ,login_state TINYINT DEFAULT 0
);