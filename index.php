<?php
$products = array(
	array('id'=>8001, 'name'=>'Apple iPhone', 'price'=>'699.00'),
	array('id'=>8012, 'name'=>'Samsung Galaxy', 'price'=>'599.00'),
	array('id'=>8018, 'name'=>'Nokia Lumia', 'price'=>'499.00'),
);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Cart.class.php Example by Sei Kan</title>

	<style>
	body,table,td{font-family:Helvetica,sans-serif;font-size:.750em}
	a{color:#990000;text-decoration:none}
	table{border-collapse:collapse;border:2px solid #2e2e2e}
	</style>
</head>
<body>
	<h4>Products</h4>
	<table style="width:400px">
	<tr>
		<td><strong>Product</strong></td>
		<td><strong>Price</strong></td>
		<td></td>
	</tr>
	<?php
	foreach($products as $product){
		echo '
		<tr>
			<td>' . $product['name'] . '</td>
			<td>$' . $product['price'] . '</td>
			<td><a href="?action=add&id=' . $product['id'] . '">[+ Add to cart]</a></td>
		</tr>';
	}
	?>
	</table>

	<p>&nbsp;</p>

	<h4>Shopping Cart</h4>

	<?php
	require_once('cart.class.php');
	$cart = new Cart();

	$action = (isset($_GET['action'])) ? $_GET['action'] : '';
	$id = (isset($_GET['id'])) ? $_GET['id'] : 0;

	switch($action) {
		case 'add':
			foreach($products as $product) {
				if($product['id'] == $id) {
					$cart->add($id);
					break;
				}
			}
		break;

		case 'remove':
			$cart->remove($id);
		break;

		case 'empty':
			$cart->clear();
		break;
	}

	$items = $cart->getItems();

	if(!empty($items)){
		echo '
		<table style="border:2px solid #cc0000;width:400px">
		<tr>
			<td><strong>Item</strong></td>
			<td><strong>Price</strong></td>
			<td><strong>Quantity</strong></td>
			<td><strong>Total</strong></td>
			<td></td>
		</tr>';

		$total = 0;
		foreach($items as $id=>$qty) {
			foreach($products as $product) {
				if($product['id'] == $id)
					break;
			}
			if(!isset($product['name']))
				continue;

			echo '
			<tr>
				<td>' . $product['name'] . '</td>
				<td>$' . $product['price'] . '</td>
				<td>' . $qty . '</td>
				<td>$' . ($product['price'] * $qty) . '</td>
				<td><a href="?action=remove&id=' . $id . '">[x Remove]</a></td>
			</tr>';

			$total += $product['price'] * $qty;
		}

		echo '
		<tr>
			<td><a href="?action=empty">[Empty Cartt]</a></td>
			<td colspan="4" align="right"><strong>Grand Total: $' . $total . '</strong></td>
		</tr>
		</table>';
	}
	else{
		echo '<p style="color:#990000;">Your shopping cart is empty.</p>';
	}
	?>
</body>
</html>