<?php

class carrinho{

		var $items;

		//		$this->items[$i][0]		->	This Var stores the post ID
		//		$this->items[$i][1]		->	This Var stores the product quantity
		//		$this->items[$i][2]		->	This Var stores the price ID
		//		$this->items[$i][3]		->	This Var stores "out_off_stock" control var to show the admin some information

		function carrinho(){
			$this->items = array();
		}

		function addItem($postId, $qtd, $precoId){
			$count = 0;
			if(get_post_meta($postId, 'precoProd'.$precoId, true) == "" && get_post_meta($postId, 'precoProd'.$precoId, true) == 0){return;}
			//if(get_post_meta($postId, 'produto_qtd', true) == "" && get_post_meta($postId, 'produto_qtd', true) == 0){return;}

			$availQtd = get_post_meta($postId, 'quantidadeProd'.$precoId, true );

			for($i = 0; $i < count($this->items); $i++){
				if($this->items[$i][0] == $postId && $this->items[$i][2] == $precoId){
					$this->items[$i][1] += $qtd;

					if($this->items[$i][1] > $availQtd){
						//$this->items[$i][1] = $availQtd; //Uncoment this line if we dont want to go over the product availability
						$this->items[0][3] = "out_off_stock";
					}

					$count ++;
				}
			}
			if($count == 0){
				$this->items[] = array($postId, $qtd, $precoId, "avail");
				//se o nr de produtos disponiveis passar o desejado entao
				if($this->items[0][1] > $availQtd){
					//$this->items[0][1] = $availQtd;
					$this->items[0][3] = "out_off_stock";
				}
			}
			$_SESSION["basket"] = serialize($this);
		}

		function removeItem($postId, $qtd){
			for($i = 0; $i < count($this->items); $i++){
				if($this->items[$i][0] == $postId){
					$this->items[$i][1] -= $qtd;
					if($this->items[$i][1] <= 1){
						unset($this->items[$i]);
						$this->items = array_values($this->items);
					}
				}
			}
			$_SESSION["basket"] = serialize($this);
		}

		function getPrice($postId, $precoId){
			for($i = 0; $i < count($this->items); $i++){
				if($this->items[$i][0] == $postId && $this->items[$i][2] == $precoId){
					$val = get_post_meta($postId, 'precoProd'.$precoId, true);
					$val = str_replace("€", "", $val);
					$val = str_replace(" ", "", $val);

					$this->items[$i][4] = $val;

					return $val;
				}
			}
		}

		function updateItems(){
			for($i = 0; $i < count($this->items); $i++){

				$availQtd = get_post_meta($this->items[$i][0], 'quantidadeProd'.$this->items[$i][2], true );

				$this->items[$i][1] = $_POST["prod_qtd_".$this->items[$i][0]."_".$this->items[$i][2]];

				if($_POST["prod_qtd_".$this->items[$i][0]."_".$this->items[$i][2]] > $availQtd){
					//$this->items[$i][1] = $availQtd;
					$this->items[$i][1] = $_POST["prod_qtd_".$this->items[$i][0]."_".$this->items[$i][2]];
					$this->items[$i][3] = "out_off_stock";
				}

				if($this->items[$i][1] <= 0){
					unset($this->items[$i]);
					$this->items = array_values($this->items);
				}
			}
			$_SESSION["basket"] = serialize($this);
		}

		function clearAllItems(){
			$this->items = array_values($this->items);
			for($i = count($this->items); $i >= 0; $i--){
				unset($this->items[$i]);
			}
			$this->items = array_values($this->items);

			$_SESSION["basket"] = serialize($this);
		}

		function calcTotalCarrinho(){
			$total = 0;
			for($i = 0; $i < count($this->items); $i++){
				if(isset($this->items[$i][4])){
					$total += $this->items[$i][4] * $this->items[$i][1];
				}else{
					$this->items[$i][4] = $this->getPrice($this->items[$i][0], $this->items[$i][2]);
					$total += $this->items[$i][4] * $this->items[$i][1];
				}
			}

			return $total;
		}

		function getItemNum(){
			return count($this->items);
		}

		function getItems(){
			return $this->items;
		}
}

?>