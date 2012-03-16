<?php

class carrinho{

		var $items;

		function carrinho(){
			$this->items = array();
		}

		function addItem($postId, $qtd){
			$count = 0;
			for($i = 0; $i < count($this->items); $i++){
				if($this->items[$i][0] == $postId){
					$this->items[$i][1] += $qtd;
					$count ++;
				}
			}
			if($count == 0){
				$this->items[] = array($postId, $qtd);
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

		function getPrice($postId){
			for($i = 0; $i < count($this->items); $i++){
				if($this->items[$i][0] == $postId){
					$val = get_post_meta($postId, 'produto-preco', true);
					$val = str_replace("€", "", $val);
					$val = str_replace(" ", "", $val);

					$this->items[$i][2] = $val;

					return $val;
				}
			}
		}

		function updateItems(){
			for($i = 0; $i < count($this->items); $i++){
				$this->items[$i][1] = $_POST["prod_qtd_".$this->items[$i][0]];

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
				if(isset($this->items[$i][2])){
					$total += $this->items[$i][2] * $this->items[$i][1];
				}else{
					$this->items[$i][2] = $this->getPrice($this->items[$i][0]);
					$total += $this->items[$i][2] * $this->items[$i][1];
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