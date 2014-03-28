// Loads and shows a random saying.
$.ajax({
    type: "POST",
    url: "/randomSaying",
    dataType: "json"
}).done(function(saying) {
    $('#quote').text('"' + saying.quote + '"');
    $('#author').text(saying.author);
    $('.saying').fadeIn(1500);
});