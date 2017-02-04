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

if(isset($_POST['questionID'])){$question_id=$_POST['questionID'];}
if(isset($_POST['position'])){$position=$_POST['position'];}


$question = new studentsQuestion();
$question->setQuestionPoistion($question_id, $position);
