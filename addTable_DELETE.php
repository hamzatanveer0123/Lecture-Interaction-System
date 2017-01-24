<?php
/**
 * Created by PhpStorm.
 * User: hamzamalik0123
 * Date: 21/12/2016
 * Time: 23:15
 */
require_once('config.php');
require_once('lib/database.php');
initializeDataBase_();
function initializeDataBase_()
{
    $query = "CREATE TABLE yacrs_studentsQuestion(id INTEGER PRIMARY KEY AUTO_INCREMENT, student_id VARCHAR(35), session_id INTEGER, question VARCHAR(140), timeadded DATETIME, answer_id INTEGER, viewed INTEGER, pin_location INTEGER, needs_attention INTEGER);";
    dataConnection::runQuery($query);
}