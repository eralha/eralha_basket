
<div class="basketContainer clearfix">
	<form method="POST" action="{theme_url}/basket/">
		<div class="basketItemList">
			{content}
		</div>
		<div class="total">
			Total Carrinho: {total}€
		</div>
		<div class="pagamento">
			<div class="bold">Metodo de Pagamento</div>
			{pagamentos}
		</div>
		<div class="msg clearfix">
			<div class="bold">Menssagem</div>
			<div class="small">Observações/dúvidas acerca do serviço deixe aqui o seu comentário.</div>
			<textarea id="txtMsg" name="txtMsg"></textarea>
		</div>
		<div class="formButtons clearfix">
			<input type="submit" id="update" name="update"  value="Actualizar o carrinho"/>
			<input type="submit" id="checkout" name="checkout"  value="Finalizar a Compra"/>
		</div>
	</form>
</div>