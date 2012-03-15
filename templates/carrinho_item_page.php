
<div class="basketContainer">
	<form method="POST" action="/basket/">
		<div class="basketItemList">
			{content}
		</div>
		<div class="pagamento">
			<div>Metodo de Pagamento</div>
			<label for="m_transf">Transferência Bancária:</label><input type="radio" id="m_transf" name="metodo" value="transferencia" checked/>
			<label for="m_transf">Easy pay:</label><input type="radio" id="m_easypay" name="metodo" value="easy pay" />
		</div>
		<div class="msg">
			<div>Menssagem</div>
			<smal>Se tem algo a dizer sobre a encomenda, ou alguma dúvida acerca do serviço deixe aqui o seu comentário.</small>
			<textarea id="txtMsg" name="txtMsg"></textarea>
		</div>
		<div class="formButtons">
			<input type="submit" id="update" name="update"  value="Actualizar o carrinho"/>
			<input type="submit" id="checkout" name="checkout"  value="Finalizar a Compra"/>
		</div>
		<div class="total">
			{total}€
		</div>
	</form>
</div>