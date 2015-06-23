
<!doctype html>
<!--[if IE 7]>    <html class="oldie ie7" lang="pt"> <![endif]-->
<!--[if IE 8]>    <html class="oldie ie8" lang="pt"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="pt"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  
  <title>Fatura Encomenda</title>
  <meta name="author" content="">
  <meta name="keywords" content="">
  <meta name="description" content="">
  


</head>
<body>

	<style>
		body{margin:0px;padding:0px;}
		.main{width:100%;background-color:#000;color:#fff;font-size:12px;font-family:Arial;padding:20px;}
		.topo{width:100%;margin:20px 0;}
		.topo .logo{}
		.prodList{width:100%;}
			.prodList .basket_item{width:100%;margin-top:-1px;}
			.prodList .basket_item div{float:left;padding:3px;}
			.prodList .title{font-weight:bold;width:50%;border-right:solid 1px #5d5d5d;font-size:13px;}
			.prodList .title a{color:#fff;text-decoration:none;}
			.prodList .title a:hover{color:#f00;text-decoration:underline;}
			.prodList .price{width:20%;text-align:center;border-right:solid 1px #5d5d5d;}
			.prodList .qtd{width:20%;text-align:center;}
		.bottom{margin-top:30px;border-top:solid 1px #fff;font-size:10px;padding-top:10px;clear:both;}
		.userInfo{margin-bottom:20px;}
		.userInfo div{width:100%;border-bottom:solid 0px #5d5d5d;padding-bottom:3px;margin-bottom:5px;}
		.clearfix:before, .clearfix:after { content: ""; display: table; }
		.clearfix:after { clear: both; }
		.clearfix { *zoom: 1; }
		.prodTitle{margin-top:30px;margin-bottom:10px;font-size:16px;font-weight:bold;}
		.valorTotal{clear:both;padding-top:15px;font-weight:bold;}
	</style>
	
	<div class="main">
		<div class="topo">
			<div class="logo"><img src="http://highridebike.pt/wp-content/themes/hight-riders/images/hr_logo.png" /></div>
		</div>

		<div class="userInfo">
			<div><b>Morada:</b> {adress}</div>
			<div><b>Localidade:</b> {localidade}</div>
			<div><b>Cod Postal:</b> {codPostal}</div>
		</div>

		<div class="prodTitle">Produtos</div>

		<div class="prodList clearfix">
			{itemList}
		</div>
		
		<div class="valorTotal">Valor Total: {valor-total}&#8364;</div>

		<div style="clear:both;"></div>
		<div class="bottom">Todos os direitos reservados highridebike.pt</div>
	</div>

</body>
</html>