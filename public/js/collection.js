function addNewActeurPersonnage(element, principal, index, id) {
	var select = '<div id="acteurPersonnages_' + index + '" class="col-sm-12 collection">';
	select += '<div class="row w-100">';
	select += '<div class="col-sm-5">';
	if(id !== undefined){
		select += '<input type="hidden" id="acteur_acteurPersonnages_INDEX_id" name="acteur[acteurPersonnages][INDEX][id]" value="' + id + '">';
	}
	select += element;
	select += '</div>';
	
	select += '<div class="col-sm-5">';
	select += principal;
	select += '</div>';
	
	select += '<div class="col-sm-2">';
	select += '<span onclick="deleteElement(\'#acteurPersonnages_' + index + '\')" class="delete"><i class="fas fa-times"></i></span>';
	select += '</div>';
	
	select += '</div>';
	select += '</div>';
	
	$("#acteurPersonnages").append(select.replace(/INDEX/g, index));
	
	$("#acteur_acteurPersonnages_" + index + "_personnage").select2();
	$("#acteur_acteurPersonnages_" + index + "_principal").select2({
	    minimumResultsForSearch: -1
	});
	
	index++;
	
	return index;
}

function addNewPersonnageActeur(element, principal, index, id) {
	var select = '<div id="personnageActeurs_' + index + '" class="col-sm-12 collection">';
	select += '<div class="row w-100">';
	select += '<div class="col-sm-5">';
	if(id !== undefined){
		select += '<input type="hidden" id="personnage_personnageActeurs_INDEX_id" name="personnage[personnageActeurs][INDEX][id]" value="' + id + '">';
	}
	select += element;
	select += '</div>';
	
	select += '<div class="col-sm-5">';
	select += principal;
	select += '</div>';
	
	select += '<div class="col-sm-2">';
	select += '<span onclick="deleteElement(\'#personnageActeurs_' + index + '\')" class="delete"><i class="fas fa-times"></i></span>';
	select += '</div>';
	
	select += '</div>';
	select += '</div>';
	
	$("#personnageActeurs").append(select.replace(/INDEX/g, index));
	
	$("#personnage_personnageActeurs_" + index + "_acteur").select2();
	$("#personnage_personnageActeurs_" + index + "_principal").select2({
	    minimumResultsForSearch: -1
	});
	
	index++;
	
	return index;
}

function deleteElement(id){
	$(id).remove();
}