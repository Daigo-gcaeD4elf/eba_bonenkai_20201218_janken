CREATE TABLE admin_rock_paper_scissors (
    admin_member_id SERIAL PRIMARY KEY
    ,state TINYINT                       /* 0:ゲーム中ではない 1:ゲーム進行中 */
    ,renewal_time DATETIME
    ,admin_choise INT
    ,number_of_times INT
);

INSERT INTO admin_rock_paper_scissors(
    state
    ,renewal_time
    ,admin_choise
    ,number_of_times
) VALUES (
    0
    ,NOW()
    ,1
    ,0
);