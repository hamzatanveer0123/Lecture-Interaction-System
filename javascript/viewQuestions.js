/**
 * Created by Hamza Tanveer on 05/12/2016.
 */

$( document ).ready(function() {

    loadPage();

    //expand on click
    $(".hide-unImpQuestion").click(function (e) {

        //if close button is pressed!
        var target = e.target.className;
        if(target == "card-badge" || (target.indexOf("bubble-for-badge") == 0)) return false;

        //to create smooth animation  of opening and closing
        var WAIT = 40;

        var cardContent     = $(this).find('.question-content');
        setTimeout(function(){
            $(cardContent).show();
        }, WAIT);

        //toggle to open and close card
        $(this).removeClass("hide-unImpQuestion");

    });
});

$('#add_studentsQuestion').on('submit', function (e) {
    e.preventDefault();
    if ($('textarea[name="question"]').val()) {
        $.ajax({
            type: 'post',
            url: 'ajax_studentsQuestion.php',
            data: $('form').serialize(),
            success: function (data) {
                console.log(data);
                $(".message-container").animate({ scrollTop: $(".message-container").prop("scrollHeight")}, "slow");
                $('textarea[name="question"]').val("");
            },
            failure: function (data) {
                alert(data);
            }
        });
    } else {
        alert("please add a question!");
    }
});

function loadPage() {
    setTimeout(function() {
        $(".loading-screen").hide();
    }, 1000);
    setTimeout(function() {
        $(".message-container").css('visibility', 'visible');
        $(".form-container").css('visibility', 'visible');
        //if overflow scroll to the bottom
        $(".message-container").scrollTop($(".message-container")[0].scrollHeight);
        // $(".message-container").animate({ scrollTop: $(".message-container").prop("scrollHeight")}, "slow");
        // $(".message-container").animate({ scrollTop: $(".message-container").height() }, "slow");
    }, 1000);

}

function plusplusLike(sId,qId,liked) {
    $.ajax({
        type: 'get',
        url: 'ajax_likeQuestion.php?sessionID='+sId+'&questionID='+qId+'&liked='+liked,
        success: function (data) {
            console.log(data);
            if(liked = 1)
                $(".badge-question-"+qId).css("color","#197fcd");
            else
                $(".badge-question-"+qId).css("color","#888");
        },
        failure: function (data) {
            alert(data);
        }
    });
}

function closeQuestionCard(qId) {

    var qDiv = $(".badge-close-"+qId).parentsUntil($("ask-question"),".col-sm-12");

    var contentDiv = $(qDiv).find('div')[0];
    $(contentDiv).hide();

    //to create smooth animation  of opening and closing
    var WAIT = 40;
    setTimeout(function(){
        //toggle to open and close card
        $(qDiv).addClass("hide-unImpQuestion");
    }, WAIT);

}


