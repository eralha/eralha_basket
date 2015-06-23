<?php
	/*
		Plugin Name: Eralha Item Basket
		Plugin URI: 
		Description: Adds a basket page to theme [eralha-basket] cada produto deve ter nos meta fields os campos: produto_qtd, produto_preco
		Version: 0.0.0.1
		Author: Emanuel Ralha
		Author URI: 

		Obs: cada post ou tax item tem de ter o seguinte template de html na página com o form

		<div>Preço: <?php echo get_post_meta($post->ID, 'produto_preco', true);?></div>
		<div>Quantidade disponível: <?php echo get_post_meta($post->ID, 'produto_qtd', true);?></div>
		<form method="POST" action="<?php echo get_stylesheet_directory_uri();?>/basket/">
			<label for="prod_qtd">
				Quantidade:
			</label>
			<input type="text" id="prod_qtd" name="prod_qtd" value="1" />
			<input type="hidden" id="p_id" name="p_id" value="<?php the_ID();?>" />
			<input type="submit" id="addprod" name="addprod"  value="Adicionar ao Carrinho"/>
		</form>


	*/

// No direct access to this file
if (!session_id())
    session_start();

defined('ABSPATH') or die('Restricted access');

if (!class_exists("eralha_basket")){
	class eralha_basket{

		var $optionsName = "eralha_basket";
		var $dbVersion = "0.1";
		var $estados = array(array(0, "A aguardar pagamento"), array(1, "Pago e em processamento"), array(2, "Em processo de entrega"), array(3, "Finalizada"));
		var $pagamentos = array("Tranferencia Bancária", "Envio à cobrança", "Paypal");

		//PAY PAL VPN CONFIG
		//var $paypalURL = "https://www.paypal.com/cgi-bin/webscr";
		var $paypalURL = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		var $paypalEmail = "ts_test@gmail.com";
		var $paypalReturnURL = "http://highridebike.pt/basket/?action=ipn";
		//var $paypalReturnURL = "http://localhost/basket/?action=ipn";

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
				`vchEncRef` varchar(32) NOT NULL,
				`vchMetodoPagamento` varchar(32) NOT NULL, 
				`vchEstadoEncomenda` varchar(32) NOT NULL, 
				`vchComentario` varchar(700) NOT NULL, 
				PRIMARY KEY  (`idEncomenda`)
			);";

			$sqlTblImages = "CREATE TABLE ".$table_produtos."
			(
				`idRegisto` INT(8) NOT NULL AUTO_INCREMENT, 
				`idPost` INT(8) ,
				`idProduto` INT(8) ,
				`inStock` INT(8) ,
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

		function instanciateCart(){
			//SET SESSION BASKET
			include "objects/carrinho.php";
			if(isset($_SESSION["basket"])){
				$basket = unserialize($_SESSION["basket"]);
			}else{
				$basket = new carrinho();
			}

			return $basket;
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

			if(isset($_GET["page"]) && $_GET["page"] == "enc-screen" && !isset($_GET["action"])){
				//SHOW SPECIFIC ADMIN SCREEN
					include "templates/adm/lista_encomenda.php";
					include "templates/adm/lista_encomenda_finalizada.php";
			}

			if(isset($_GET["page"]) && $_GET["page"] == "enc-screen" && $_GET["action"] == "enc-ficha" && isset($_GET["id-enc"])){
				//SHOW SPECIFIC ADMIN SCREEN
					include "templates/adm/ficha_encomenda.php";
			}

			//DELETE ENCOMENDA
			if(isset($_GET["page"]) && $_GET["page"] == "enc-screen" && $_GET["action"] == "enc-delete" && isset($_GET["id-enc"])){
				//Deleting From table encomendas
					$wpdb->get_results($wpdb->prepare("DELETE FROM ".$table_encomendas." WHERE idEncomenda = %s", $_GET["id-enc"]), ARRAY_A);
				
				//Deleting from table produtos and add it to produt count again
					$dataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_produtos." WHERE idEncomenda = %s", $_GET["id-enc"]), ARRAY_A);
					foreach($dataSet as $data){
						$prod = get_post($data["idProduto"]);

						$currProdQtd = get_post_meta($data["idProduto"], 'produto_qtd', true);
						$currProdQtd += $data["iQuantidade"];
						//Update post meta
						update_post_meta($data["idProduto"], 'produto_qtd', $currProdQtd);
					}
					//Deleting from protucts table
					$wpdb->get_results($wpdb->prepare("DELETE FROM ".$table_produtos." WHERE idEncomenda = %s", $_GET["id-enc"]), ARRAY_A);

				//SHOW SPECIFIC ADMIN SCREEN
					include "templates/adm/encomenda_removida.php";
					include "templates/adm/lista_encomenda.php";
					include "templates/adm/lista_encomenda_finalizada.php";
			}

			//Change Encestado
			if(isset($_GET["page"]) && $_GET["page"] == "enc-screen" && $_GET["action"] == "enc-delete" && isset($_GET["id-enc"])){
			}
		}

		function get_token($str){
			return md5($this->optionsName.$str);
		}

		function getPagamentos($exclude = NULL){
			$out = "<ul>";
			for($i = 0; $i < count($this->pagamentos); $i++){
				if($i == 0){$chk = "checked";}else{$chk = "";}
				if($exclude."_" != $i."_"){
					$out .= "<li><label for='m_transf'>".$this->pagamentos[$i]."</label><input type='radio' id='m_pag_".$i."' name='metodo' value='".$i."' ".$chk."/></li>";
				}
			}
			return $out."</ul>";
		}

		function deleteEnc($encID){
			global $wpdb;
			global $user_ID;

			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			if(current_user_can('administrator') || current_user_can('editor')){
				$image = $wpdb->get_results("SELECT * FROM ".$table_images." WHERE idImagem = '".$imageID."' ", ARRAY_A);
			}else{
				$image = $wpdb->get_results("SELECT * FROM ".$table_images." WHERE idImagem = '".$imageID."' AND iUserId = '".$user_ID."'", ARRAY_A);
			}

			foreach($image as $img){
				//DELETE IMAGE FROM UPLOAD FOLDER
					$uploadPath = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
					@unlink("..".$uploadPath."uploads/".$img[0]["vchImageName"]);

				//DELETE FILE FROM DATA BASE
					$wpdb->query("DELETE FROM ".$table_images." WHERE idImagem = '".$imageID."' ");
			}
		}

		function generateEncRef(){
			$letters = array("1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","X","W","Y","Z");
			$cod = "";
			$time = time();
			for($i = 0; $i < 9; $i++){
				$cod = $cod.$letters[array_rand($letters)];
			}
			return $cod;
		}

		function addProdToCart($postId, $precoId, $cartObj){
			//CHECK IF POST EXISTS
			$post = get_post($postId, OBJECT);

			/*
			//IF WE WANT TO SET QUANTITY BASED ON PROD QT
			$qtd = get_post_meta($postId, 'quantidadeProd'.$precoId, true );
			
				if($_qtd > $qtd){
					$qtd = $qtd;
				}else{
					$qtd = $_qtd;
				}
				//$qtd = $_POST["prod_qtd"]; //QT based on user request.
			*/

			if($post){
				$cartObj->addItem($post->ID, 1, $precoId);
				//echo "Produto adicionado";
			}else{
				//echo "Produto nao existe";
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

				$title = $post->post_title." - ".get_post_meta($post->ID, 'tipoProd'.$item[2], true);

				$list .= str_replace("{permlink}", get_permalink($post->ID), $itemTemplate);
				$list = str_replace("{title}", $title, $list);
				$list = str_replace("{excerpt}", $post->post_excerpt, $list);
				if($image_url != ""){
					$list = str_replace("{image}", '<img src="'.$image_url.'" alt="" width="100" />', $list);
				}else{
					$list = str_replace("{image}", '', $list);
				}
				$list = str_replace("{preco}", ($basket->getPrice($post->ID, $item[2])*$item[1]), $list);
				$list = str_replace("{quantidade}", $item[1], $list);
				$list = str_replace("{postId}", $post->ID, $list);
				$list = str_replace("{prodTipo}", $item[2], $list);

				//$output .= $list;
			}

			if($list === ""){
				$list = "<b>Não existem produtos no carrinho de compras.</b>";
			}

			return $list;
		}

		function sendEncEmail($idEnc){
			global $wpdb;
			global $current_user;
			global $user_ID;
      		get_currentuserinfo();

			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));
			set_include_path($pluginDir);

			$emailTemplate = file_get_contents($pluginDir."templates/email_encomenda.php");
			$itemTemplate = file_get_contents($pluginDir."templates/email_item.php");

			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			$encData = $wpdb->get_results("SELECT * FROM ".$table_encomendas." WHERE idEncomenda = ".$idEnc, ARRAY_A);
			$prodsData = $wpdb->get_results("SELECT * FROM ".$table_produtos." WHERE idEncomenda = ".$idEnc, ARRAY_A);

			$list = "";//Will cointain the html generated by item list
			$total = 0;

			foreach($prodsData as $item){
				$postData = get_post($item["idPost"], OBJECT);

				$title = $postData->post_title." - ".get_post_meta($postData->ID, 'tipoProd'.$item["idProduto"], true);

				$list .= str_replace("{permlink}", get_permalink($postData->ID), $itemTemplate);
				$list = str_replace("{title}", $title, $list);

				$price = get_post_meta($postData->ID, 'precoProd'.$item["idProduto"], true);
				$price = str_replace("€", "", $price);
				$price = str_replace(" ", "", $price);

				$list = str_replace("{preco}", ($price*$item["iQuantidade"]), $list);
				$list = str_replace("{quantidade}", $item["iQuantidade"], $list);
				$list = str_replace("{postId}", $postData->ID, $list);
				$list = str_replace("{prodTipo}", $item["idProduto"], $list);

				//set total
				$total += ($price*$item["iQuantidade"]);
			}

			$emailTemplate = str_replace("{itemList}", $list, $emailTemplate);
			$emailTemplate = str_replace("{valor-total}", $total, $emailTemplate);
			$emailTemplate = str_replace("{adress}", get_user_meta(get_current_user_id(), "adress", true), $emailTemplate);
			$emailTemplate = str_replace("{localidade}", get_user_meta(get_current_user_id(), "localidade", true), $emailTemplate);
			$emailTemplate = str_replace("{codPostal}", get_user_meta(get_current_user_id(), "codPostal", true), $emailTemplate);

			//Email configuration - send email with all the enc info to user email.
				$to = $current_user->user_email;

				$subject = 'A sua encomenda Highridebike';

				$headers = "From: shop@highridebike.pt\r\n";
				$headers .= "Reply-To: shop@highridebike.pt\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

				$status = mail($to, $subject, $emailTemplate, $headers);
				//$status = wp_mail($to, $subject, $emailTemplate, $headers);
		}

		function confirmBuy($output, $basket){
			global $wpdb;

			$theme_url = get_bloginfo('wpurl');
			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));

			set_include_path($pluginDir);

			//Call transaction services and set enc state based on services feedback
			$estado = $this->estados[0][0];//Não cobrada

			//SET ENC ON DB CLEAR AND SHOW MESSAGE
			$encRef = $this->generateEncRef();
			$wpdb->insert($table_encomendas, 
				array(
				'iData'=>time(), 
				'iUserId'=> get_current_user_id(), 
				'iTotal'=> $basket->calcTotalCarrinho(), 
				'vchMetodoPagamento'=> $_POST["metodo"], 
				'vchEstadoEncomenda'=> $estado, 
				'vchComentario'=> $_POST["txtMsg"]
			));

			//update EncRef
				$encId = $wpdb->insert_id;
				$wpdb->update(
				    $table_encomendas, // Table
				    array(
				        'vchEncRef' => $encRef.$encId
				    ), // Array of key(col) => val(value to update to)
				    array(
				        'idEncomenda' => $encId
				    )
				);

			$items = $basket->getItems();

			//insert items
				if(isset($encId)){
					foreach($items as $item){
						$inStock = "1";
						$qtd = get_post_meta($item[0], 'quantidadeProd'.$item[2], true);
						$qtd = $qtd - $item[1];

						//Se ficar menos que 0 produtos criamos um alerta para avisar o vendedor
						if($qtd < 0){
							$qtd = 0;
							$inStock = "0";
						}

						update_post_meta($item[0], 'quantidadeProd'.$item[2], $qtd);

						$wpdb->insert($table_produtos, 
							array(
							'idPost'=>$item[0], 
							'idProduto'=>$item[2], 
							'idEncomenda'=>$encId, 
							'inStock'=> $inStock, 
							'iQuantidade'=>$item[1]
						));
					}
				}

			$finishMsg = file_get_contents($pluginDir."templates/carrinho_checkout_finish_msg.php");
			$output .= $finishMsg;

			if($_POST["metodo"] == 2){//Paypal Standart checkout
				$paypalForm = file_get_contents($pluginDir."templates/checkout_paypal_form.php");

				$paypalForm = str_replace("{paypalurl}", $this->paypalURL, $paypalForm);
				$paypalForm = str_replace("{paypalEmail}", $this->paypalEmail, $paypalForm);
				$paypalForm = str_replace("{item_name}", "Encomenda ".$encRef.$encId, $paypalForm);
				$paypalForm = str_replace("{item_number}", $encRef.$encId, $paypalForm);
				$paypalForm = str_replace("{amount}", $basket->calcTotalCarrinho(), $paypalForm);
				$paypalForm = str_replace("{paypalReturnURL}", $this->paypalReturnURL, $paypalForm);

				//Clear Basket
					$basket->clearAllItems();

				$output .= $paypalForm;

				$this->sendEncEmail($encId);//Generating email whit all enc data

				return $output;
			}

			if($_POST["metodo"] == 0){//Transferencia
				$paypalForm = file_get_contents($pluginDir."templates/checkout_transferencia.php");

				$paypalForm = str_replace("{ref}", $encRef.$encId, $paypalForm);

				//Clear Basket
					$basket->clearAllItems();

				$output .= $paypalForm;

				$this->sendEncEmail($encId);//Generating email whit all enc data

				return $output;
			}

			if($_POST["metodo"] == 1){//Envio cobranca
				$paypalForm = file_get_contents($pluginDir."templates/checkout_envio_cobranca.php");

				$paypalForm = str_replace("{ref}", $encRef.$encId, $paypalForm);

				//Clear Basket
					$basket->clearAllItems();

				$output .= $paypalForm;

				$this->sendEncEmail($encId);//Generating email whit all enc data

				return $output;
			}

			//Clear Basket
				$basket->clearAllItems();

			//$output .= $finishMsg;

			return $output;
		}

		function processPaypalData(){
			global $wpdb;

			$theme_url = get_bloginfo('wpurl');
			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			$output = "";

			//$pluginDir = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));

			set_include_path($pluginDir);

			//including object that will process our paypal data
			include('objects/ipnlistener.php');
			include('objects/paypal_handler.php');

			return $output;
		}

		function addContent($content=''){
			global $wpdb;

			$theme_url = get_bloginfo('wpurl');
			$table_encomendas = $wpdb->prefix.$this->optionsName."_encomendas";
			$table_produtos = $wpdb->prefix.$this->optionsName."_produtos";

			//$pluginDir = str_replace("http://".$_SERVER['HTTP_HOST']."", "", plugin_dir_url( __FILE__ ));
			$pluginDir = str_replace("", "", plugin_dir_url( __FILE__ ));

			set_include_path($pluginDir);

			preg_match_all('(\[eralha-basket\])', $content, $matches, PREG_PATTERN_ORDER);

			if(count($matches[0]) == 0){return $content;}

			$output = "";
			if(is_user_logged_in()){
				$output .= file_get_contents($pluginDir."templates/carrinho_menu.php");
			}

			//SET SESSION BASKET
				$basket = $this->instanciateCart();

			//ACTION ----- ADDPROD
				if(isset($_POST["addprod"]) && isset($_POST["p_id"])){
					$this->addProdToCart($_POST["p_id"], $_POST["precoId"], $basket);
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
				if(isset($_POST["confirm_buy"]) && $basket->getItemNum() > 0 && is_user_logged_in()){
					$output = $this->confirmBuy($output, $basket);
					return $output;
				}

			//ACTION ----- PROCESS PAYPAL DATA
				if($_GET["action"] === "ipn" || $_GET["action"] === "IPN"){
					$output .= $this->processPaypalData();
					return $output;
				}
			
			//INTERFACE - ACTION ----- LIST OPEN ORDERS
				if(isset($_GET["action"])){
					if(!is_user_logged_in()){//Impede que um user que não esteja logado veja estas páginas
						return $output .= file_get_contents($pluginDir."templates/log_out_view.php");
					}

					if($_GET["action"] == "orders"){
						echo $output;
						include "templates/order_list.php";
						return;
					}
					if($_GET["action"] == "history"){
						echo $output;
						include "templates/order_history.php";
						return;
					}
				}
			
			//ACTION ----- CHECKOUT
				if(isset($_POST["checkout"]) && $basket->getItemNum() > 0){

					$checkoutTemp = file_get_contents($pluginDir."templates/carrinho_checkout_page.php");
					$list = $this->listItems($basket);

					$output .= str_replace("{content}", $list, $checkoutTemp);
					$output = str_replace("{theme_url}", $theme_url, $output);
					$output = str_replace("{total}", $basket->calcTotalCarrinho(), $output);
					$output = str_replace("{metodo}", $this->pagamentos[$_POST["metodo"]], $output);
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
					$output = str_replace("{theme_url}", $theme_url, $output);
					$output = str_replace("{pagamentos}", $this->getPagamentos(), $output);
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

/*
$labels = array(
	'name' => _x( 'Loja Online', 'taxonomy general name' ),
	'singular_name' => _x( 'Loja Online', 'taxonomy singular name' ),
	'search_items' =>  __( 'Procurar na loja' ),
	'all_items' => __( 'Todos os Items' ),
	'parent_item' => __( 'Items Anteriores' ),
	'parent_item_colon' => __( 'Items Anteriores:' ),
	'edit_item' => __( 'Editar Item' ), 
	'update_item' => __( 'Actualizar Item' ),
	'add_new_item' => __( 'Adicionar novo Item' ),
	'new_item_name' => __( 'Novo Item' ),
	'menu_name' => __( 'Loja Online' ),
); 

register_taxonomy('loja', 'produto', array(
	'hierarchical' => true,
	'labels' => $labels,
	'show_ui' => true,
	'query_var' => true,
	'rewrite' => array( 'slug' => 'loja' ),
));
//REGISTER ITEM POST TYPE
register_post_type( 'produto',
    array(
        'labels' => array(
            'name' => __( 'Produtos' ),
            'singular_name' => __( 'Produto' )
        ),
    'public' => true,
    'has_archive' => true,
    'taxonomies' => array('loja')
    )
);
add_post_type_support( 'produto', array('custom-fields', 'thumbnail' ) );
*/

?>