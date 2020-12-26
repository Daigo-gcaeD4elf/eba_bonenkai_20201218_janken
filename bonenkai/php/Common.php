<?php
function h($str) {
    return htmlspecialchars($str);
}

function print_pre($param) {
    echo '<pre>';
    print_r($param);
    echo '</pre>';
}

$rpsName = [
    '0' => '',
    '1' => 'グー',
    '2' => 'チョキ',
    '3' => 'パー',
];
