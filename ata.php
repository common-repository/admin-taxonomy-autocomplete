<?php
/*
Plugin Name: Admin Taxonomy Autocomplete
Version: 1.0.0
Plugin URI: http://ruchevits.com/
Description: Adds autocomplete functionality to all taxonomy input fields in admin panel.
Author: Edward Ruchevits
Author URI: http://ruchevits.com/
*/

add_action('admin_enqueue_scripts', 'ata_enqueue_scripts');
add_action('wp_ajax_nopriv_get_the_taxonomy-submit', 'get_the_taxonomy_submit');
add_action('wp_ajax_get_the_taxonomy-submit', 'get_the_taxonomy_submit');

/**
 * This function loads all the javascripts required by plugin.
 *
 * @uses wp_enqueue_script()
 * @uses wp_localize_script()
 * @uses wp_create_nonce()
 * @uses plugins_url()
 * @uses admin_url()
 *
 * @since 1.0.0
 */
function ata_enqueue_scripts(){
	wp_enqueue_script('jquery-ui-autocomplete');
	wp_enqueue_script('admin-taxonomy-autocomplete', plugins_url('/ata.js', __FILE__), array('jquery-ui-autocomplete'));

	// Pass parameters to the Admin Taxonomy Autocomplete javascript
	wp_localize_script('admin-taxonomy-autocomplete', 'params', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'get_the_taxonomy_nonce' => wp_create_nonce('get_the_taxonomy-nonce')
	));
}

/**
 * This function is called via AJAX, verifies nonce and prints term 
 * names of the taxonomy, which name is passed as a POST parameter.
 *
 * @uses wp_verify_nonce()
 * @uses current_user_can()
 * @uses load_terms()
 *
 * @since 1.0.0
 */
function get_the_taxonomy_submit(){
	$nonce = $_POST['nonce'];
	
	// Verify nonce for security reasons
	if (!wp_verify_nonce($nonce, 'get_the_taxonomy-nonce')){
		die ('Busted!');
	}
	
	// Check user permissions
	if (current_user_can('edit_posts')){
		$taxonomy_type = $_POST['taxonomy_type'];
		$taxonomy_objects = load_terms($taxonomy_type);
		$response = json_encode($taxonomy_objects);
		header("Content-Type: application/json");
		echo $response;
	}
	
	exit;
}

/**
 * This function returns term names of a given taxonomy.
 *
 * @uses get_results()
 *
 * @since 1.0.0
 *
 * @param string $taxonomy Taxonomy name to get the term names of.
 * @return array The associative array of term names.
 */
function load_terms($taxonomy){
	global $wpdb;
	
	// Database query
	$query = 'SELECT DISTINCT t.name 
	FROM ' . DB_NAME . '.wp_terms t 
	INNER JOIN ' . DB_NAME . '.wp_term_taxonomy tax 
	ON `tax`.term_id = `t`.term_id 
	WHERE ( `tax`.taxonomy = \'' . $taxonomy . '\')';
	
	return $wpdb->get_results($query, ARRAY_A);                 
}