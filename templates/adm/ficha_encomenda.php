<?php
	if(isset($_POST["alterarEstadoEncomenda"])){
		$wpdb->update($table_encomendas, array("vchEstadoEncomenda" => $_POST["cboEstadoEncomenda"]), array("idEncomenda" => $_GET["id-enc"]), array("%d"), array("%d"));
	}

	$dataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_encomendas." WHERE idEncomenda = %s", $_GET["id-enc"]), ARRAY_A);
	$prodDataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_produtos." WHERE idEncomenda = %s", $_GET["id-enc"]), ARRAY_A);

	global $current_user;
	get_currentuserinfo();
?>




<div class="warper">

	<h3>Dados do Utilizador</h3>
	
	<div class="row opt">
		<div class="c2" style="text-align:left;">Nome </div>
		<div class="c1">Morada</div>
		<div class="c2">Localidade</div>
		<div class="c2">Código Postal</div>
		<div class="c2">Email</div>
	</div>

	<div class="row reg">
		<div class="c2" style="text-align:left;"><?php echo get_user_meta($dataSet[0]["iUserId"], "first_name", true)." ".get_user_meta($dataSet[0]["iUserId"], "last_name", true);?></div>
		<div class="c1"><?php echo get_user_meta($dataSet[0]["iUserId"], "adress", true);?></div>
		<div class="c2"><?php echo get_user_meta($dataSet[0]["iUserId"], "localidade", true);?></div>
		<div class="c2"><?php echo get_user_meta($dataSet[0]["iUserId"], "codPostal", true);?></div>
		<div class="c2"><a href="mailto:<?php echo $current_user->user_email;?>"><?php echo $current_user->user_email;?></a></div>
	</div>

</div>

<div class="warper">

	<h3>Dados de encomenda</h3>
	
	<div class="row opt">
		<div class="c1">Comentário utilizador</div>
		<div class="c2">Data Encomenda</div>
		<div class="c2">Metodo pagamento</div>
		<div class="c2">Estado encomenda</div>
		<div class="c2">Referência</div>
		<div class="c2">Total</div>
	</div>

	<?php if(count($dataSet) > 0){?>
	<div>
		<?php foreach($dataSet as $data){?>
			<div class="row reg">
				<div class="c1"><?php echo $data["vchComentario"];?></div>
				<div class="c2"><?php echo date("d/m/Y", $data["iData"]);?></div>
				<div class="c2"><?php echo $this->pagamentos[$data["vchMetodoPagamento"]];?></div>
				<div class="c2"><?php echo $this->estados[$data["vchEstadoEncomenda"]][1];?></div>
				<div class="c2"><?php echo $data["vchEncRef"];?></div>
				<div class="c2"><?php echo $data["iTotal"];?>€</div>
			</div>
		<?php }?>
	</div>
	<div class="row opt">
		<form id="FormEstadoEncomenda" name="FormEstadoEncomenda" action="?page=enc-screen&action=enc-ficha&id-enc=<?php echo $_GET["id-enc"];?>" method="post">
			<div class="c1"></div>
			<div class="c2"></div>
			<div class="c2"></div>
			<div class="c2">
				<select id="cboEstadoEncomenda" name="cboEstadoEncomenda">
					<option>Alterar estado</option>
					<?php
						foreach($this->estados as $estado){ ?>
							<option value="<?php echo $estado[0];?>"><?php echo $estado[1];?></option>
						<?php }
					?>
				</select>
			</div>
			<div class="c2"></div>
			<div class="c2">
				<input type="submit" id="alterarEstadoEncomenda" name="alterarEstadoEncomenda" value="Alterar" />
			</div>
		</form>
	</div>
	<?php }else{?>
	<div>
		Não tem neste momento nenhuma encomenda em aberto!
	</div>
	<?php }?>

</div>

<div class="warper">

	<h3>Items de encomenda</h3>
	
	<div class="row opt">
		<div class="c1">Nome produto</div>
		<div class="c2">Valor</div>
		<div class="c2">Quantidade</div>
		<div class="c2">Informação</div>
	</div>

	<?php if(count($prodDataSet) > 0){?>
	<div>
		<?php foreach($prodDataSet as $data){?>
			<?php $prod = get_post($data["idPost"]);?>
			<div class="row reg">
				<div class="c1"><?php echo $prod->post_title." - ".get_post_meta($data["idPost"], 'tipoProd'.$data["idProduto"], true);?></div>
				<div class="c2"><?php echo get_post_meta($data["idPost"], 'precoProd'.$data["idProduto"], true);?>€</div>
				<div class="c2"><?php echo $data["iQuantidade"];?></div>
				<div class="c2"><?php 
				if($data["inStock"] == 1){
					echo "Quantidade disponível em stock";
				}else{
					echo "Stock de produto indisponível";
				}
				?></div>
			</div>
		<?php }?>
	</div>
	<?php }else{?>
	<div>
		Não tem neste momento nenhuma encomenda em aberto!
	</div>
	<?php }?>

</div>