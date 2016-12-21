/**
 * Created by Hamza Tanveer on 05/12/2016.
 */

$( document ).ready(function() {

    loadPage();

    //hide initially
    $(".no-reaction").toggleClass("hide-unImpQuestion");
    $(".no-reaction a").toggle();
    $(".no-reaction div").toggle();
    $(".close-unImpQuestion").toggle();

    //expand on click
    $(".hide-unImpQuestion").click(function (e) {

        //to create smooth animation  of opening and closing
        var WAIT;
        if($(this).css("width") == "32px") WAIT = 60;
        else WAIT = 10;

        var linkToHide  = $(this).find('.link');
        var closeButton = $(this).find('.close-unImpQuestion');
        var attenButton = $(this).find(".needs-attention-badge")

        setTimeout(function(){
            $(linkToHide).toggle();
            $(closeButton).toggle();
            $(attenButton).toggle();
        }, WAIT);

        //toggle to open and close card
        $(this).toggleClass("hide-unImpQuestion");

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

function plusplusNeedHelp(qId) {
    //H2 create a new table
    $.ajax({
        type: 'post',
        url: 'upvoteQuestion.php',
        data: $('form').serialize(),
        success: function (data) {
            console.log(data);
        },
        failure: function (data) {
            alert(data);
        }
    });
}


