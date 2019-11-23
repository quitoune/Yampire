function is_defined(element){
	if(is_null(element) || typeof element !== 'undefined' || element == ""){
		return false;
	}
	return true;
}

$("#utilisateur_password_first").keyup(function(){
	console.log('ok');
	var valid = true;
	var value = $(this).val();
	
	if(value.length < 6 && value.length > 20){
		valid = false;
	}
	
	if(!value.match(/[0-9]/)){
		valid = false;
	}
	
	if(!value.match(/[a-z]/)){
		valid = false;
	}
	
	if(!value.match(/[A-Z]/)){
		valid = false;
	}	
	
	if(valid){
		$(this).removeClass('is-invalid');
	} else {
		if(!$(this).hasClass('is-invalid')){
			$(this).addClass('is-invalid');
		}
	}
	return valid;
});

$("#register_user").submit(function(){
	if($("#utilisateur_password_first").hasClass("is-invalid")){
		$("#error_password_submit").show();
		return false;
	}
	$("#error_password_submit").hide();
	return true;
});
