<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/ajax.php');
require_once('lib/shared_funcs.php');
include_once('corelib/mobile.php');

$template = new templateMerge($TEMPLATE);

$sessionID = requestInt('sessionID');
$questionID = requestInt('quId');

if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$sessionID}&mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$sessionID}&mode=mobile'>Use mobile mode</a>";

//H2 hack for fake login
//$uinfo = userInfo::retrieve_fakeUserInfo(5);
$uinfo = checkLoggedInUser();


$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php">YACRS</a></li>';
if((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
{
    $serverURL = 'https://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 443)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
else
{
    $serverURL = 'http://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 80)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
$serverURL .= $_SERVER['SCRIPT_NAME'];
if($uinfo==false)
{
    header("Location: index.php");
    // actually should allow join a session as guest...
}
else
{
    $template->pageData['mainBody'] = '';
    $thisSession = requestSet('sessionID')? session::retrieve_session($sessionID):false;
    $thisQuestion = requestSet('quId')? studentsQuestion::retrieve_studentQuestion($questionID):false;

    if($thisSession == false || $thisQuestion == false)
    {
        $template->pageData['mainBody'] = "<p><b>Invalid session/question or missing number.</b></p>".sessionCodeinput();
    }
    else
    {
        $smemb = sessionMember::retrieve($uinfo['uname'], $thisSession->id);
        if($smemb == false)
        {
            $smemb = new sessionMember();
            $smemb->session_id = $thisSession->id;
            $smemb->userID = $uinfo['uname'];
            $smemb->name = $uinfo['gn'].' '.$uinfo['sn'];
            $smemb->email = $uinfo['email'];
            $smemb->joined = time();
            $smemb->lastresponse = time();
            $smemb->insert();
        }
        else
        {
            $smemb->lastresponse = time();
            $smemb->update();
        }
        if((requestSet('submit'))&&(requestSet('mublog')))
        {
            $post = trim(requestRaw('mublog',''));
            if(strlen($post))
            {
                $msg = new message();
                $msg->user_id = $smemb->id;
                $msg->posted = time();
                $msg->message = $post;
                $msg->session_id = $thisSession->id;
                $msg->insert();
                preg_match_all('/#[^s]+/', $post, $matches);
                foreach($matches[0] as $mtag)
                {
                    $msg->addTag($mtag);
                }
            }
        }

        $breadcrumb = "";
        $prevURL = "";
        if(isset($_SERVER['HTTP_REFERER']))
        {
            $prevURL = $_SERVER['HTTP_REFERER'];
            if (strpos($prevURL, 'viewQuestions.php') !== false) {
                $breadcrumb = "<li><a href='viewQuestions.php?sessionID={$thisSession->id}'>View Questions</a></li>";

            } else if(strpos($prevURL, 'presentationView.php') !== false){
                $breadcrumb = "<li><a href='presentationView.php?sessionID={$thisSession->id}'>Presentation View</a></li>";
            }
        }

        $bestAnswer = studentsQuestion::getBestAnswer($questionID);
        $bestAnswer = $bestAnswer[0];

        if($bestAnswer){
            if($bestAnswer->student_id) {
                $temp_user  = sessionMember::retrieve_sessionMember_matching("userID", $bestAnswer->student_id);
                $user       = $temp_user[0];
                $username   = $user->name;
                $posted     = ago($bestAnswer->posted);
            }
            $answer = '<div class="info correct-answer"><p class="bubble" style="border-radius: 5px;">'.$bestAnswer->message.'</p><p class="meta"><span class="username">'.$username.'</span><span class="time">'.$posted.'</span></p></div>';
        }

        $question = studentsQuestion::retrieve_studentsQuestion_matching("id",$questionID);
        $questionText = $question[0]->question;
        $template->pageData['afterContent'] = getAJAXScript($thisSession->id, $questionID);
        $template->pageData['breadcrumb'] .= "<li><a href='session_page.php?sessionID={$thisSession->id}'>{$thisSession->title}</a></li>";
        $template->pageData['breadcrumb'] .= $breadcrumb;
        $template->pageData['breadcrumb'] .= "<li>Discussion</li>";
        $template->pageData['mainBody'] .= '<h2 style="text-decoration: underline">Discuss<span class="hidden-xs"> This Question</h2>';
        $template->pageData['mainBody'] .= '<h3>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$questionText.'</h3>';
        $template->pageData['mainBody'] .= '<h3>'.$answer.'</h3>';
        $template->pageData['mainBody'] .= "<form id='add_questionChat' method='POST' class='form-horizontal'><div class='form-group'>";
        $template->pageData['mainBody'] .= "<div class='col-sm-12 col-xs-12'><input type='hidden' name='questionID' value='{$thisQuestion->id}' />";
        $template->pageData['mainBody'] .= "<input type='hidden' name='sessionID' value='{$thisSession->id}' />";
        $template->pageData['mainBody'] .= "<textarea name='chatMessage' rows='3' class='form-control input-reply'></textarea>";
        $template->pageData['mainBody'] .= "<input type='submit' name='submit' value='' class='btn btn-block btn-info submit-reply'/></div>";
        $template->pageData['mainBody'] .= "</div></form>";
        $template->pageData['mainBody'] .= "<div id='messages'></div></div>";
    }
    //$template->pageData['mainBody'] .= '<pre>'.print_r($uinfo,1).'</pre>';
    //$template->pageData['mainBody'] .= '<pre>'.print_r($smemb,1).'</pre>';
    $template->pageData['logoutLink'] = loginBox($uinfo);
}
echo $template->render();
function getAJAXScript($sessionID, $questionID)
{
    return getChatUpdateAJAXScript($questionID,$sessionID);
}
?>
<script src="javascript/jquery.min.js"></script>
<script src="javascript/add_questionChat.js"></script>
