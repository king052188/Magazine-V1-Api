var set_c = function(name, value, days) {
    var expires;
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    }
    else {
        expires = "";
    }
    document.cookie = name + "=" + value + expires + "; path=/";
}
var rev_c = function(name) {
    var expires;
    var value = "deleted";
    var date = new Date();
    date.setTime(date.getTime() + -1);
    expires = "; expires=" + date.toGMTString();
    document.cookie = name + "=" + value + expires + "; path=/";
}
function get_c(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=");
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1;
            c_end = document.cookie.indexOf(";", c_start);
            if (c_end == -1) {
                c_end = document.cookie.length;
            }
            return unescape(document.cookie.substring(c_start, c_end));
        }
    }
    return null;
}
token_code = function(user) {
$(document).ready( function() {
$.get("/kpa/work/kpa-class-js", { user: user} )
.done(function( data ) {
    if(get_c("v_e") != null) { rev_c("t_s"); rev_c("v_e"); }
    set_c("t_s", data.Time_Stamp); set_c("v_e", data.Value_Encrypted);
});
})
}
