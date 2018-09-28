<?php

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'mod_miquiz\task\sync_users',
        'blocking' => 0,
        'minute' => '*/10',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ],
];
