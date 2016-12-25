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
$liked          = "";

if(isset($_GET['sessionID'])){$session_id=$_GET['sessionID'];}
if(isset($_GET['questionID'])){$question_id=$_GET['questionID'];}
if(isset($_GET['liked'])){$liked=$_GET['liked'];}

$question_liked = new question_liked();


$question_liked->session_id   = $session_id;
$question_liked->question_id  = $question_id;
$question_liked->student_id   = $student_id;
$question_liked->liked        = $liked;
$question_liked->posted       = time();

$id = $question_liked->insert();

$question = new studentsQuestion();
$question->plusOneAttention($question_id);

echo $id;