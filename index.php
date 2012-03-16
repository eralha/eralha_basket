<?php
	/*
		Plugin Name: Eralha Item Basket
		Plugin URI: 
		Description: Adds a basket page to theme
		Version: 0.0.0.1
		Author: Emanuel Ralha
		Author URI: 
	*/

// No direct access to this file
if (!session_id())
    session_start();

defined('ABSPATH') or die('Restricted access');

if (!class_exists("eralha_basket")){
	class eralha_basket{

		var $optionsName = "eralha_basket";
		var $dbVersion = "0.1";

		function eralha_galeria(){
			
		}

		function init(){
			$installed_ver = get_option($this->optionsName."_db_version");
			if($installed_ver != $this->dbVersion){
				$this->activationHandler();
				update_option($this->optionsName."_db_version", $this->dbVersion);
			}
		}
		function activationHandler(){
			global $wpdb;

			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			$sqlTblGalerias = "CREATE TABLE ".$table_encomendas." 
			(
				`idEncomenda` int(8) NOT NULL auto_increment, 
				`iData` int(32) NOT NULL, 
				`iUserId` int(32) NOT NULL, 
				`iTotal` int(32) NOT NULL, 
				`vchMetodoPagamento` varchar(32) NOT NULL, 
				`vchEstadoEncomenda` varchar(32) NOT NULL, 
				`vchComentario` varchar(700) NOT NULL, 
				PRIMARY KEY  (`idEncomenda`)
			);";

			$sqlTblImages = "CREATE TABLE ".$table_produtos."
			(
				`idRegisto` INT(8) NOT NULL AUTO_INCREMENT, 
				`idProduto` INT(8) ,
				`idEncomenda` INT(8) NOT NULL, 
				`iQuantidade` INT(8) NOT NULL, 
				PRIMARY KEY  (`idRegisto`)
			);";

			require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			dbDelta($sqlTblGalerias);
			dbDelta($sqlTblImages);

			add_option($this->optionsName."_db_version", $this->dbVersion);
		}
		function deactivationHandler(){
			global $wpdb;

			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			//$wpdb->query("DROP TABLE IF EXISTS ". $table_encomendas);
			//$wpdb->query("DROP TABLE IF EXISTS ". $table_produtos);
		}

		function printAdminPage(){
			global $wpdb;
			global $user_ID;

			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			//$pluginDir = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));
			set_include_path($pluginDir);

			//SET CSS
				include "templates/styles.php";

			if(isset($_GET["page"]) && $_GET["page"] == "enc-screen"){
				//SHOW SPECIFIC ADMIN SCREEN
					include "templates/lista_encomenda.php";

			}
		}

		function get_token($str){
			return md5($this->optionsName.$str);
		}

		function deleteEnc($encID){
			global $wpdb;
			global $user_ID;

			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			if(current_user_can('administrator') || current_user_can('editor')){
				$image = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idImagem = '".$imageID."' "), ARRAY_A);
			}else{
				$image = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_images." WHERE idImagem = '".$imageID."' AND iUserId = '".$user_ID."'"), ARRAY_A);
			}

			foreach($image as $img){
				//DELETE IMAGE FROM UPLOAD FOLDER
					$uploadPath = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
					@unlink("..".$uploadPath."uploads/".$img[0]["vchImageName"]);

				//DELETE FILE FROM DATA BASE
					$wpdb->query($wpdb->prepare("DELETE FROM ".$table_images." WHERE idImagem = '".$imageID."' "));
			}
		}

		function listItems($basket){
			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));
			set_include_path($pluginDir);

			$itemTemplate = file_get_contents($pluginDir."templates/carrinho_item.php");
			$list = "";

			foreach($basket->items as $item){
				$post = get_post($item[0], OBJECT);

				$image_id = get_post_thumbnail_id($post->ID);
				$image_url = wp_get_attachment_image_src($image_id,'large');
				$image_url = $image_url[0];

				$list .= str_replace("{permlink}", get_permalink($post->ID), $itemTemplate);
				$list = str_replace("{title}", $post->post_title, $list);
				$list = str_replace("{excerpt}", $post->post_excerpt, $list);
				if($image_url != ""){
					$list = str_replace("{image}", '<img src="'.$image_url.'" alt="" width="100" />', $list);
				}else{
					$list = str_replace("{image}", '', $list);
				}
				$list = str_replace("{preco}", $basket->getPrice($post->ID), $list);
				$list = str_replace("{quantidade}", $item[1], $list);
				$list = str_replace("{postId}", $post->ID, $list);

				//$output .= $list;
			}

			return $list;
		}

		function addContent($content=''){
			global $wpdb;

			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			//$pluginDir = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));

			set_include_path($pluginDir);

			preg_match_all('(\[eralha-basket\])', $content, $matches, PREG_PATTERN_ORDER);

			if(count($matches[0]) == 0){return $content;}

			$output = "";
			$output .= file_get_contents($pluginDir."templates/carrinho_menu.php");

			//SET SESSION BASKET

				include "objects/carrinho.php";
				if(isset($_SESSION["basket"])){
					$basket = unserialize($_SESSION["basket"]);
				}else{
					$basket = new carrinho();
				}

			//ACTION ----- ADDPROD
				if(isset($_POST["addprod"]) && isset($_POST["p_id"])){
					//CHECK IF POST EXISTS
						$post = get_post($_POST["p_id"], OBJECT);
						$qtd = get_post_meta($post->ID, 'produto-qtd', true );

						//IF WE WANT TO SET QUANTITY BASED ON PROD QT
						/*
							if($_POST["prod_qtd"] > $qtd){
								$qtd = $qtd;
							}else{
								$qtd = $_POST["prod_qtd"];
							}
						*/
						$qtd = $_POST["prod_qtd"]; //QT based on user request.

						if($post){
							$basket->addItem($post->ID, $qtd);
							//echo "Produto adicionado";
						}else{
							//echo "Produto nao existe";
						}
				}

			//ACTION ----- REMOVE PROD
				if(isset($_POST["remove"]) && isset($_POST["p_id"])){
					$basket->removeItem($_POST["p_id"], $_POST["prod_qtd"]);
				}

			//ACTION ----- ACTUALIZAR CARRINHO
				if(isset($_POST["update"])){
					$basket->updateItems();
				}
			
			//ACTION ----- SAVE ENCOMENDA
				if(isset($_POST["confirm_buy"]) && $basket->getItemNum() > 0){
					//SET ENC ON DB CLEAR AND SHOW MESSAGE
					$finishMsg = file_get_contents($pluginDir."templates/carrinho_checkout_finish_msg.php");

					$wpdb->insert($table_encomendas, 
						array(
						'iData'=>time(), 
						'iUserId'=> get_current_user_id(), 
						'iTotal'=> $basket->calcTotalCarrinho(), 
						'vchMetodoPagamento'=> $wpdb->prepare($_POST["metodo"]), 
						'vchEstadoEncomenda'=>'open', 
						'vchComentario'=> $wpdb->prepare($_POST["txtMsg"])
					));

					$encId = $wpdb->insert_id;
					$items = $basket->getItems();

					//insert items
						foreach($items as $item){
							$wpdb->insert($table_produtos, 
								array(
								'idProduto'=>$item[0], 
								'idEncomenda'=>$encId, 
								'iQuantidade'=>$item[1]
							));
						}

					$basket->clearAllItems();

					$output .= $finishMsg;

					return $output;
				}
			
			//INTERFACE - ACTION ----- LIST OPEN ORDERS
				if(isset($_GET["action"])){
					if($_GET["action"] == "orders"){
						$output .= "Order List";
						return $output;
					}
					if($_GET["action"] == "history"){
						$output .= "History List";
						return $output;
					}
				}
			
			//ACTION ----- CHECKOUT
				if(isset($_POST["checkout"]) && $basket->getItemNum() > 0){

					$checkoutTemp = file_get_contents($pluginDir."templates/carrinho_checkout_page.php");
					$list = $this->listItems($basket);

					$output .= str_replace("{content}", $list, $checkoutTemp);
					$output = str_replace("{total}", $basket->calcTotalCarrinho(), $output);
					$output = str_replace("{metodo}", "Transferencia bancária", $output);
					$output = str_replace("{metodo_form}", $_POST["metodo"], $output);
					$output = str_replace("{msg_form}", $_POST["txtMsg"], $output);

					if(is_user_logged_in()){
						$userdata = file_get_contents($pluginDir."templates/carrinho_checkout_user_data_login.php");
						$userdata = str_replace("{adress}", get_user_meta(get_current_user_id(), "adress", true), $userdata);
						$userdata = str_replace("{localidade}", get_user_meta(get_current_user_id(), "localidade", true), $userdata);
						$userdata = str_replace("{codPostal}", get_user_meta(get_current_user_id(), "codPostal", true), $userdata);

						$output = str_replace("{userdata}", $userdata, $output);

						$controls = file_get_contents($pluginDir."templates/carrinho_checkout_bts_login.php");
						$output = str_replace("{controls}", $controls, $output);
					}else{
						$output = str_replace("{userdata}", "", $output);

						$controls = file_get_contents($pluginDir."templates/carrinho_checkout_bts_logout.php");
						$output = str_replace("{controls}", $controls, $output);
					}

					if($_POST["txtMsg"] != ""){
						$msg = file_get_contents($pluginDir."templates/carrinho_checkout_msg.php");
						$msg = str_replace("{menssagem}", $_POST["txtMsg"], $msg);

						$output = str_replace("{msg}", $msg, $output);
					}else{
						$output = str_replace("{msg}", "", $output);
					}

					//$basket->clearAllItems();
						
					return $output;
				}


			//If have items in the basket list them
				if(isset($basket)){
					$listTemplate = file_get_contents($pluginDir."templates/carrinho_item_page.php");
					$list = $this->listItems($basket);
					$output .= str_replace("{content}", $list, $listTemplate);
					$output = str_replace("{total}", $basket->calcTotalCarrinho(), $output);
				}
			
			//Set Basket Output
			if(count($matches[0]) > 0){
				//Process screen
				$content = str_replace("[eralha-basket]", $output, $content);
			}
			
			return $content;
		}
	}
}
if (class_exists("eralha_basket")) {
	$eralha_basket_obj = new eralha_basket();
}

//Actions and Filters
if (isset($eralha_basket_obj)) {
	//VARS

	//Actions
		register_activation_hook(__FILE__, array($eralha_basket_obj, 'activationHandler'));
		register_deactivation_hook(__FILE__, array($eralha_basket_obj, 'deactivationHandler'));
		add_action('admin_menu', 'eralha_basket_admin_initialize');
		add_action('plugins_loaded', array($eralha_basket_obj, 'init'));

	//Filters
		//Search the content for galery matches
		add_filter('the_content', array($eralha_basket_obj, 'addContent'));

}

//Initialize the admin panel
if (!function_exists("eralha_basket_admin_initialize")) {
	function eralha_basket_admin_initialize() {
		global $eralha_basket_obj;
		if (!isset($eralha_basket_obj)) {
			return;
		}
		if ( function_exists('add_submenu_page') ){
			//ADDS A LINK TO TO A SPECIFIC ADMIN PAGE
			add_menu_page('Encomendas', 'Encomendas', 'publish_posts', 'enc-screen', array($eralha_basket_obj, 'printAdminPage'));
			/*
				add_submenu_page('enc-screen', 'Gallery List', 'Gallery List', 'publish_posts', 'enc-screen', array($eralha_basket_obj, 'printAdminPage'));
				add_submenu_page('enc-screen', 'Create Gallery', 'Create Gallery', 'publish_posts', 'enc-screen', array($eralha_basket_obj, 'printAdminPage'));
			*/
		}
	}
}
?>