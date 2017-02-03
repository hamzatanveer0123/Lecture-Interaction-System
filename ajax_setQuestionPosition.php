<?php
/**
 * Created by PhpStorm.
 * User: hamzamalik0123
 * Date: 05/12/2016
 * Time: 23:05
 */

require_once('config.php');
require_once('lib/database.php');

$question_id    = "";
$position       = "";

if(isset($_REQUEST['questionID'])){$question_id=$_REQUEST['questionID'];}
if(isset($_REQUEST['position'])){$position=$_REQUEST['position'];}


$question = new studentsQuestion();
$question->setQuestionPoistion($question_id, $position);
