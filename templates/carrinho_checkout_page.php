
<div class="basketContainer">
	<form method="POST" action="{theme_url}/basket/">
		<div class="basketItemList">
			<b>Confirme todos os dados da encomenda</b><br /><br />
			
				{content}
			</blockquote>
		</div>
		<div class="total">
			Total Carrinho: {total}€
		</div>

		<div class="pagamento">
			<div><b>Metodo de Pagamento</b></div>
			{metodo}
		</div>
		<br /><br />
		{userdata}
		<br /><br />
		{msg}
		<br /><br />
		{controls}
		
		<input type="hidden" id="metodo" name="metodo" value="{metodo_form}" />
		<input type="hidden" id="txtMsg" name="txtMsg" value="{msg_form}" />
	</form>
</div>