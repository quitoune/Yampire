$( document ).ready(function() {
	$("#question_type_question").select2();
	$("#question_type_proposition").select2();
	$("#question_reponse").select2();
	$("#question_response").select2();
	
	$('#question_type_question').on('change', function() {
		var val = $(this).val();
	      if(val == 2){
	    	  
	      } else {
	    	  
	      }
	})
});