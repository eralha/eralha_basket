<?php
	$dataSet = $wpdb->get_results("SELECT * FROM ".$table_encomendas." WHERE iUserId = '".get_current_user_id()."' AND vchEstadoEncomenda = 3 ORDER BY idEncomenda DESC", ARRAY_A);
?>

<?php if(count($dataSet) > 0){?>
<div class="account-orders">
	<?php foreach($dataSet as $data){?>
		<div class="order">
			<h3>Encomenda Ref: <?php echo $data["vchEncRef"];?></h3>
			<div>Data: <?php echo date("d/m/Y", $data["iData"]);?></div>
			<div>Total: <?php echo $data["iTotal"];?>€</div>
			<div>Referência: <?php echo $data["vchEncRef"];?></div>
			<div>Metodo Pagamento: <?php echo $this->pagamentos[$data["vchMetodoPagamento"]];?></div>
			<div>Estado: <?php echo $this->estados[$data["vchEstadoEncomenda"]][1];?></div>

			<div class="prodList">

				<h4>Items de encomenda</h4>
				
				<?php 
					$prodsData = $wpdb->get_results("SELECT * FROM ".$table_produtos." WHERE idEncomenda = ".$data["idEncomenda"], ARRAY_A);
					foreach($prodsData as $item){
					$postData = get_post($item["idPost"], OBJECT);
					$title = $postData->post_title." - ".get_post_meta($postData->ID, 'tipoProd'.$item["idProduto"], true);
				?>
					<div class="basket_item clearfix">
						<div class="title"><a href="<?php echo get_permalink($postData->ID);?>"><?php echo $title;?></a></div>
						<div class="price"><?php echo get_post_meta($postData->ID, 'precoProd'.$item["idProduto"], true);?>€</div>
						<div class="qtd">Quantidade: <?php echo $item["iQuantidade"];?></div>
					</div>
				<?php } ?>

			</div>

		</div>
	<?php }?>
</div>
<?php }else{?>
<div>
	Ainda não tem nenhuma encomenda concluída!
</div>
<?php }?>