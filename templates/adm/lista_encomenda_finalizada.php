<?php
	$dataSet = $wpdb->get_results("SELECT * FROM ".$table_encomendas." WHERE vchEstadoEncomenda = 3 ORDER BY idEncomenda DESC", ARRAY_A);
?>




<div class="warper">

	<h3>Encomendas Finalizadas</h3>
	
	<div class="row opt">
		<div class="c1">User</div>
		<div class="c2">Data Encomenda</div>
		<div class="c2">Valor (€)</div>
		<div class="c2">Estado Encomenda</div>
		<div class="c2">Referência</div>
		<div class="c3">Acções de encomenda</div>
	</div>

	<?php if(count($dataSet) > 0){?>
	<div>
		<?php foreach($dataSet as $data){?>
			<div class="row reg">
				<div class="c1"><?php echo get_user_meta($data["iUserId"], "first_name", true)." ".get_user_meta($data["iUserId"], "last_name", true);?></div>
				<div class="c2"><?php echo date("d/m/Y", $data["iData"]);?></div>
				<div class="c2"><?php echo $data["iTotal"];?>€</div>
				<div class="c2"><?php echo $this->estados[$data["vchEstadoEncomenda"]][1];?></div>
				<div class="c2"><?php echo $data["vchEncRef"];?></div>
				<div class="c3">
					<a href="?page=enc-screen&action=enc-ficha&id-enc=<?php echo $data["idEncomenda"];?>">Ver</a>
					 | 
					<a href="?page=enc-screen&action=enc-delete&id-enc=<?php echo $data["idEncomenda"];?>">Apagar</a>
				</div>
			</div>
		<?php }?>
	</div>
	<?php }else{?>
	<div class="row reg">
		<div class="c1">Não tem neste momento nenhuma encomenda Finalizada</div>
	</div>
	<?php }?>

</div>