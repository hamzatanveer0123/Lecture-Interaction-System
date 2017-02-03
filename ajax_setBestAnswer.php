<?php
/**
 * Created by PhpStorm.
 * User: hamzamalik0123
 * Date: 05/12/2016
 * Time: 23:05
 */

require_once('config.php');
require_once('lib/database.php');

$uinfo = checkLoggedInUser();

$question_id    = "";
$message_id     = "";

if (isset($_REQUEST['qID'])) {
    $question_id = $_REQUEST['qID'];
}
if (isset($_REQUEST['mID'])) {
    $message_id = $_REQUEST['mID'];
}

$question = new studentsQuestion();
$question->setBestAnswer($question_id, $message_id);
