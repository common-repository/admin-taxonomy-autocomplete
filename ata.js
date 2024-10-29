jQuery(document).ready(function(){
    jQuery('.newtag').each(function(){
		var taxonomy = jQuery(this);
		var taxonomy_name = taxonomy.attr("name");
		var taxonomy_type = taxonomy_name.match(/\[(.*)\]/)[1];
		jQuery.post(
			params.ajax_url,
			{
				action : 'get_the_taxonomy-submit',
				taxonomy_type : taxonomy_type,
				nonce : params.get_the_taxonomy_nonce
			},
			function(response){
				proceed_autocomplete(response);
			}
		);
		function proceed_autocomplete(response) {
			var available_data = [];
			
			jQuery.each(response, function(taxonomy_id, taxonomy_item) {
				available_data.push(taxonomy_item.name);
			});
			
			// Autocomplete
			jQuery(taxonomy).autocomplete({
				source: available_data
			});
		}
	});
});