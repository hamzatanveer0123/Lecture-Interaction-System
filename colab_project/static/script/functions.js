$( document ).ready(function() {
    $(".student_question").click(function () {
        $(".question_window").toggleClass("toggleQuestionWindowMargin");
        $(".wrapper").toggleClass("toggleWrapperMargin");
    });
});