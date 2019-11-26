//objet JS pour la pagination
Pagination = {};

Pagination.demarrer = function() {

	/**
	 * Méthode Ajax qui va charger l'element présent dans l'URL
	 */
	Pagination.Ajax = function(url, id_done, method = 'GET')
	{	
		$.ajax({
			method: method,
			url: url,
		})
		.done(function( html ) {
			$(id_done).html(html);
		});
	}
	
	/**
	 * chargement de la nouvelle page au click
	 */
	Pagination.EventChangePage = function(id, url_base)
	{
		$(id).click(function(){
			event.preventDefault();
			
			Pagination.Ajax($(this).attr('href'), url_base);
		});
	}
}