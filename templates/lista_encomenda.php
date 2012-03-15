<?php
	$dataSet = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$table_encomendas." ORDER BY idEncomenda DESC"), ARRAY_A);
?>

<div class="warper">
	
	<div class="row opt">
		<div class="c1">User</div>
		<div class="c2">Data Encomenda</div>
		<div class="c2">Valor (€)</div>
		<div class="c2">Estado Encomenda</div>
		<div class="c3">Acções de encomenda</div>
	</div>
	<div class="row reg">
		<div class="c1">Mclaren F1</div>
		<div class="c2">18/12/2012</div>
		<div class="c2">525.89€</div>
		<div class="c2">Em processamento</div>
		<div class="c3">
			<a href="">Ver</a> | <a href="">Apagar</a>
		</div>
	</div>

</div>