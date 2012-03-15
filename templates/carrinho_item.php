<div class="basket_item">
	<div class="img">{image}</div>
	<div class="title"><a href="{permlink}">{title}</a></div>
	<div class="excerpt">{excerpt}</div>
	<div class="price">{preco}€</div>
	<div class="quantidade">Quantidade a comprar: {quantidade}</div>
	<div class="remover">
		<label for="prod_qtd">
			Quantidade:
		</label>
		<input type="text" id="prod_qtd_{postId}" name="prod_qtd_{postId}" value="{quantidade}" />
		<input type="hidden" id="p_{postId}" name="p_{postId}" value="{postId}" />
	</div>
</div>