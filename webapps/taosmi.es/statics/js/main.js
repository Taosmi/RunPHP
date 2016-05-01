// Loads and shows a random saying.
$.ajax({
    type: "GET",
    url: "/v1/saying",
    contentType: "application/json"
}).done(function(data) {
    $('#quote').text('"' + data.saying.quote + '"');
    $('#author').text(data.saying.author);
    $('.saying').fadeIn(1500);
}).fail(function() {
    $('#quote').text('Las citas célebres no están disponibles temporalmente');
    $('#author').text("Disculpen las molestias");
    $('.saying').fadeIn(1500);
});