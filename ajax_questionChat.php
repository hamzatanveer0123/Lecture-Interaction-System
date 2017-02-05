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

$session_id     = "";
$student_id     = $uinfo['uname'];
$question_id    = "";
$message        = "";

if(isset($_POST['sessionID'])){
    $session_id=$_POST['sessionID'];
}
if(isset($_POST['questionID'])){
    $question_id=$_POST['questionID'];
}
if(isset($_POST['chatMessage'])){
    $message = htmlspecialchars($_POST['chatMessage']);
}

$chat_message = new chat_messages();

$chat_message->session_id   = $session_id;
$chat_message->question_id  = $question_id;
$chat_message->student_id   = $student_id;
$chat_message->message      = $message;
$chat_message->posted       = time();
$chat_message->viewed       = false;

$id = $chat_message->insert();
echo $id;