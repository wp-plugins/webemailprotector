function webemailprotector_email_change($emo_nu,$email) {
 //alert('emo_nu:'+$emo_nu+' email:'+$email);
 jQuery.ajax({
     type: "POST",
	 data: {action:'wep_email_change',emo_nu:$emo_nu,email:$email,security:MyAjax.security},
	 url: "admin-ajax.php",
     success: function (response) {
	 //alert('hit email'+response);S
     }
 });
textfieldID='wep_emailtxt_'+$emo_nu;
document.getElementById(textfieldID).style.color="red";
}

function webemailprotector_displayname_change($emo_nu,$displayname) {
 //alert('emo_nu:'+$emo_nu+' displayname:'+$displayname);
 jQuery.ajax({
     type: "POST",
	 data: {action:'wep_displayname_change',emo_nu:$emo_nu,displayname:$displayname,security:MyAjax.security},
	 url: "admin-ajax.php",
     success: function (response) {
	 //alert('hit display'+response);
     }    
 });
}

function webemailprotector_emo_new() {
 //alert('hit');
 jQuery.ajax({
     type: "POST",
	 dataType: 'json',
	 data:{action:'wep_emo_new',security:MyAjax.security},
	 url: "admin-ajax.php",
     success: function (response) {
         //alert ("New email no."+response+" added");
		 tableID='wep_table';
         var row = document.getElementById(tableID).insertRow(response.row);
		 row.id='wep_tablerow_'+response.id;
		 var emailtxt = row.insertCell(0);
		 emailtxt.innerHTML = "<input type=\"text\" id=\"wep_emailtxt_"+response.id+"\" value=\"email@yourdomain.com\" style=\"color:red;\" onkeyup=\"webemailprotector_email_change('"+response.id+"',this.value)\">";
		 var displaytxt = row.insertCell(1);
		 displaytxt.innerHTML = "<input type=\"text\" id=\"wep_displaytxt_"+response.id+"\" value=\"Email Us\" onkeyup=\"webemailprotector_displayname_change('"+response.id+"',this.value)\">";
		 var validatekey = row.insertCell(2);
		 validatekey.innerHTML = "<input id=\"wep_validate_"+response.id+"\" type=\"button\" class=\"button add another\" value=\"validate\" onclick=\"webemailprotector_validate('"+response.id+"')\">";
		 var deletekey = row.insertCell(3);
		 deletekey.innerHTML="<input id=\"wep_delete_"+response.id+"\" type=\"button\" class=\"button add another\" value=\"delete\" onclick=\"webemailprotector_emo_delete('"+response.id+"')\">";
		 textfieldID='wep_emailtxt_'+response.id;
         document.getElementById(textfieldID).style.color="red";		 
     }    
 });
}

function webemailprotector_emo_delete($emo_nu) {
 if(confirm('delete entry?')){
 jQuery.ajax({
     type: "POST",
	 dataType: 'json',
	 data: {action:'wep_emo_delete',emo_nu:$emo_nu,security:MyAjax.security},
	 url: "admin-ajax.php",
     success: function (response) {
	     // alert ("email no."+response.emo_nu+" deleted, "+response.nuemails+" remaining");
		 // delete the old row from the display
		 rowID='wep_tablerow_'+response.emo_nu;
         //document.getElementById(rowID).remove();//remove as not supported by ie
		 document.getElementById(rowID).parentNode.removeChild(document.getElementById(rowID));
		 }    
 });
}
}

function webemailprotector_donothing(){
}

function webemailprotector_validate($emo_nu) {
 //start spinner
 document.getElementById('wep_spinner').style.display='block';
 document.getElementById('wep_dullout').style.display='block';
 setTimeout('webemailprotector_donothing()',1000); // to make sure any updated to email have reached the db
 email='undefined';
 //first get the email address from db associated with emo_nu as mayhave been updated since last php load
 jQuery.ajax({
     type:"GET",
	 data: {action:'wep_email_get',emo_nu:$emo_nu,security:MyAjax.security},
	 url: "admin-ajax.php",
     success: function (response) {
         email=response;
		 //alert(email);
         //jsonp as cross domain
         jQuery.ajax({
           url: 'http://www.webemailprotector.com/cgi-bin/emo_validate_wp.py?callback=?',
           type: "POST",
           crossDomain: true,
           data: {'email':email,'emo_nu':$emo_nu},
           dataType: "jsonp", 
           cache: false,
           jsonpCallback: "webemailprotector_emocb" });
     }    
     });	 
}

function webemailprotector_emocb(response) {
  //alert('callback');
  document.getElementById('wep_spinner').style.display='none';
  if (response.success == "true") {
   alert (response.message+'\n'+'( email: '+response.email+' )');
   // update the valid status for that element in db with another ajax call
   jQuery.ajax({
     type: "GET",
	 data: {action:'wep_emo_validate',code_1:response.code_1,code_2:response.code_2,code_3:response.code_3,code_4:response.code_4,
	 code_5:response.code_5,emo_nu:response.emo_nu,security:MyAjax.security},
	 dataType: 'json',
	 url: "admin-ajax.php",
     success: function (next_response) {
		 //alert (next_response.emo_nu);
		 textfieldID='wep_emailtxt_'+next_response.emo_nu;
         document.getElementById(textfieldID).style.color="green";
     }    
 });
  }
  if (response.success == "false") {
   alert (response.message+'\n'+'( email: '+response.email+' )');
   textfieldID='wep_emailtxt_'+response.emo_nu;
   document.getElementById(textfieldID).style.color="red";
  }
document.getElementById('wep_dullout').style.display='none';
}
