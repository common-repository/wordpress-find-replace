<?php
/*
Plugin Name: Find & Replace
Description: Replaces keywords on the page with other keywords.
Version: 1.0
Author: Liam Parker
Author URI: http://liamparker.com/
*/

add_action('admin_menu', 'findReplace');

//Define Initial Variables
define("fr_plugin_id", 'fr', true);
define("fr_plugin_name", 'Find & Replace', true);
define("fr_plugin_name_id", 'findReplace', true);
define("fr_form_row_id", fr_plugin_id."-id", true);

//Process Form
if ($_POST[fr_form_row_id]) {
	$findReplaceOptions = array();
	$counter = 0;
	foreach($_POST[fr_form_row_id] as $id) {
		$counter;
		$replace = $_POST['replace'][$id]; 
		$replacement = $_POST['replacement'][$id]; 
		if($replace){
			$findReplaceOptions['replace'][$counter] = $_POST['replace'][$id]; 
		}
		if($replacement){
			$findReplaceOptions['replacement'][$counter] = $_POST['replacement'][$id]; 
		}
		$counter++;
	}
	if (count($findReplaceOptions)>0){
		update_option(fr_plugin_name_id, $findReplaceOptions);
	}else{
		delete_option(fr_plugin_name_id);
	}
}

//Setup Admin
function findReplace() {
	$page = add_options_page('Find & Replace Options', 'Find & Replace', 8, 'FindReplace', 'findReplaceAdmin');
	add_action( "admin_print_scripts-$page", 'findReplaceAdminScripts'); 
}

//Include Needed Scripts
function findReplaceAdminScripts(){ 
	wp_enqueue_script("jquery"); 
}

//Make Input Row
function frMakeRow($id, $replace='', $replacement=''){
	$fr_form_row_id = fr_form_row_id."[$id]";
	$row = "<div class='box'><input type='hidden' name='$fr_form_row_id' value='$id'/><label>Replace:</label><input type='text' name='replace[$id]' value='$replace'/><label>Replace With:</label><input type='text' name='replacement[$id]' value='$replacement'/><input class='button' id='remove' type='button' name='remove' value='Remove Row'/></div>";
	return $row;
}

//Setup Admin Page
function findReplaceAdmin() {
?>

<div id="findReplace">

<script>
	var $j = jQuery.noConflict();
	$j(document).ready(function() {
		
		$j('#add').click(function() {
			newRow();
		});
		
		$j('#remove').live('click', function() {
			$j(this).parent().remove();
		});
		
		function newRow(){
			$j('#newSet').before("<?php echo frMakeRow(rand(1000, 2000)); ?>");
		}
		
		$j('#clear').click(function() {
			$j(".box").remove();
			newRow();
		})
		
	})
</script>

<style>
	#findReplace .box{margin: 5px 0;overflow: hidden;}
	#findReplace{}
	#findReplace input{margin-left: 5px;margin-right: 5px;}
</style>

<h2><?php echo fr_plugin_name; ?></h2>

<form method="POST">
<?php
	$findReplaceOptions = get_option('FindReplace'); 
	if (is_array($findReplaceOptions)){ 
		foreach($findReplaceOptions['replace'] as $id => $replace ) {
			$replacement = $findReplaceOptions['replacement'][$id];
			echo frMakeRow($id, $replace, $replacement);
		}
	}
	for ($counter = 1000; $counter <= 1003; $counter++){
		echo frMakeRow($counter);
	}
?>		
	<div id="newSet"></div>
	<p>
		<input id="add"  type="button" name="add" value="Add New Row"/>
		<input id="clear"  type="button" name="clear" value="Clear Rows"/>
	</p>
	<p>
		<input id="submit" class="button" type="submit" name="Submit" value="Save Changes"/>
	</p>
</form>
<p>
	<a href="http://liamparker.com">Developer's Website</a>
</p>
</div>
<?php
}

//Filter Content
function findReplaceFilter($text) {
	$findReplaceOptions = get_option(fr_plugin_name_id); 
	if (is_array($findReplaceOptions)){ 
		foreach($findReplaceOptions['replace'] as $id => $replace ) {
			$replace = "/$replace/";
			$replacement = $findReplaceOptions['replacement'][$id];
			$text = preg_replace($replace , $replacement, $text);
		}
	}
	return $text;
}

function templateReplace(){
	ob_start('findReplaceFilter');
}
add_action('template_redirect', 'templateReplace'); 

?>