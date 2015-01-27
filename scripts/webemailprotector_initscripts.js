  jQuery.ajax({
    url: 'http://www.webemailprotector.com/cgi-bin/emo_act_wp.py', ////!!!!!
    type: "POST",
    crossDomain: true,
    data: {'current_user_email':$current_user_email},
    dataType: "jsonp", 
    cache: false });
