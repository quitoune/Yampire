function is_defined(element){
	if(element === null){
		return false;
	}
	if(element === undefined){
		return false;
	} 
	if(element = ""){
		return false;
	}
	return true;
}

function Ajax(url, id_done, loading, method){
	if(!is_defined(loading)){
		loading = 'html';
	}
	
	if(!is_defined(method)){
		method = 'GET';
	}
	
	$.ajax({
        url: url,
        method: method
    }).done(function(html) {
        $(id_done).html(html);
        $(loading).hideLoading();
    });
}

function chargerTabs(url_base, id_bloc){
	$('.card-body').showLoading();
    Ajax(url_base, id_bloc, '.card-body');
};

function OuvrirModal(id, id_content_modal){
	$("html").showLoading();
    Ajax($(id).attr('href'), id_content_modal);
    
    return false;
};

function SubmitModal(objet, type, url, id_modal, url_base, id_bloc){
    
    $("form[name*='" + objet + "']").on('submit', function(event){
        
    	$("#container").showLoading();
        $("#" + objet + "_save").prop('disabled', true).html('Chargement...');
       
       event.preventDefault();
       
       $.ajax({
           url:url,
           method:'POST',
           data:$(this).serialize()
       }).done(function(reponse){
           
           if(reponse.statut){
               $(id_modal).modal('hide');
               Ajax(url_base, id_bloc, "#container");
           } else {
               $('#modal_' + objet).html(reponse);
           }
       });
    });
};