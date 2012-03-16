
<div class="basketContainer">
	<form method="POST" action="/basket/">
		<div class="basketItemList">
			Artigos no carrinho
			<blockquote>
				{content}
			</blockquote>
		</div>
		<br /><br />
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
		<div class="total">
			{total}€
		</div>

		<input type="hidden" id="metodo" name="metodo" value="{metodo_form}" />
		<input type="hidden" id="txtMsg" name="txtMsg" value="{msg_form}" />
	</form>
</div>