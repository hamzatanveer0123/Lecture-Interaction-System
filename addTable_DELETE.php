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
    $query = "CREATE TABLE yacrs_question_liked(id INTEGER PRIMARY KEY AUTO_INCREMENT, session_id INTEGER, question_id INTEGER, student_id VARCHAR(35), liked INTEGER, posted DATETIME);";
    dataConnection::runQuery($query);
}