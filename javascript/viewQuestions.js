/**
 * Created by Hamza Tanveer on 05/12/2016.
 */

$( document ).ready(function() {

    $('[data-toggle="tooltip"]').tooltip({placement: 'left'});

    //loaders & listeners
    loadPage();
    setListener();

    //word count for question textarea
    var maxLength = 240;
    $('.ask-question-textarea').keyup(function() {
        var length = $(this).val().length;
        var length = maxLength-length;
        $('#chars').text(length);
    });

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

$(".badge-toggle").click(function (e) {
    iconClass = $(".badge-toggle").find("i")[0];
    var className = $(iconClass).attr("class");
    if(className.indexOf("slash") < 0){
        $(iconClass).attr("class","fa fa-eye-slash fa-2x");
        $(".ask-question").click();
    } else {
        $(iconClass).attr("class","fa fa-eye fa-2x ");
        $(".button-close").click();
    }
});

$(".badge-sort").click(function (e) {
    sort();
});

function sort() {
    var divList = $(".ask-question");
    divList.sort(function(a, b){
        return $(a).data("attention") < $(b).data("attention") ? 1 : -1;
    });
    $(".message-container").html(divList);
    setListener();
}

function closeQuestionCard(qId) {

    var qDiv = $(".badge-close-"+qId).parentsUntil($("ask-question"),".col-sm-12");

    var contentDiv = $(qDiv).find('div')[0];
    $(contentDiv).hide();
    $(qDiv).addClass("hide-unImpQuestion");

    //to create smooth animation  of opening and closing
    // var WAIT = 40;
    // setTimeout(function(){
    //     //toggle to open and close card
    //     $(qDiv).addClass("hide-unImpQuestion");
    // }, WAIT);
}

//setting listeners
function setListener() {

    //expand on click
    $(".hide-unImpQuestion").click(function (e) {

        //if close button is pressed!
        var target = e.target.className;
        if(target == "card-badge" || (target.indexOf("bubble-for-badge") == 0)) return false;

        //to create smooth animation  of opening and closing
        var WAIT = 40;

        var cardContent     = $(this).find('.question-content');
        // $(cardContent).show();

        setTimeout(function(){
            $(cardContent).show();
        }, WAIT);

        //toggle to open and close card
        $(this).removeClass("hide-unImpQuestion");

    });

}

/*
* Ajax calls to other php pages!
*/

$('#add_studentsQuestion').on('submit', function (e) {
    e.preventDefault();
    if ($('textarea[name="question"]').val()) {
        $.ajax({
            type: 'POST',
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

function plusplusLike(sId,qId,liked) {
    $.ajax({
        type: 'POST',
        url: 'ajax_likeQuestion.php',
        data: {
            sessionID: sId,
            questionID: qId,
            liked: liked
        },
        success: function (data) {
            console.log(data);
            if(liked == 1) {
                $(".badge-question-" + qId).css("color", "#197fcd");
                $(".badge-question-" + qId).css("font-weight", "800");
                $(".badge-question-" + qId).attr("onclick","plusplusLike("+sId+","+qId+",0)");
            }
            else {
                $(".badge-question-" + qId).css("color", "#888");
                $(".badge-question-" + qId).css("font-weight", "300");
                $(".badge-question-" + qId).attr("onclick","plusplusLike("+sId+","+qId+",1)");
            }
        },
        failure: function (data) {
            alert(data);
        }
    });
}

