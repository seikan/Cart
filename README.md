# Cart

This is a very simple PHP cart library. Cart data can either be saved in PHP session or browser cookie.



## Usage

### Configuration

> \$cart= new Cart( \[**array** $options\] );

##### Options

| Parameter       | Type     | Description                                                  |
| --------------- | -------- | ------------------------------------------------------------ |
| cartMaxItem     | **int**  | The maximum item can be added to cart. 0 = Unlimited         |
| itemMaxQuantity | **int**  | The maximum quantity per item can be added to cart. 0 = Unlimited |
| useCookie       | **bool** | Use cookie to keep cart data when browser is closed.         |



```php
// Include core Cart library
require_once 'class.Cart.php';

// Initialize Cart object
$cart = new Cart([
  // Can add unlimited number of item to cart
  'cartMaxItem'      => 0,
  
  // Set maximum quantity allowed per item to 99
  'itemMaxQuantity'  => 99,
  
  // Do not use cookie, cart data will lost when browser is closed
  'useCookie'        => false,
]);
```



### Add Item

Adds an item to cart. 

> **bool** \$cart->add( **string** \$id\[, **int** \$quantity\]\[, **array** $attributes\] );

```php
// Add item with ID #1001
$cart->add('1001');

// Add 5 item with ID #1002
$cart->add('1002', 5);

// Add item with ID #1003 with price, color, and size
$cart->add('1003', 1, [
  'price'  => '5.99',
  'color'  => 'White',
  'size'   => 'XS',
]);

// Item with same ID but different attributes will added as separate item in cart
$cart->add('1003', 1, [
  'price'  => '5.99',
  'color'  => 'Red',
  'size'   => 'M',
]);
```



### Update Item

Updates quantity of an item. Attributes **must be** provides if item with same ID exists with different attributes.

> **bool** \$cart->update( **string** \$id, **int** $quantity\[, **array** \$attributes\] );

```php
// Set quantity for item #1001 to 5
$cart->update('1001', 5);

// Set quantity for item #1003 to 2
$cart->update('1003', 2, [
  'price'  => '5.99',
  'color'  => 'Red',
  'size'   => 'M',
]);
```



### Remove Item

Removes an item. Attributes **must be** provided to remove specified item, or all items with same ID will be removed from cart.

> **bool** \$cart->remove( **string** $id\[, **array** \$attributes\] );

```php
// Remove item #1001
$cart->remove('1001');

// Remove item #1003 with color White and size XS
$cart->remove('1003', [
  'price'  => '5.99',
  'color'  => 'White',
  'size'   => 'XS',
]);
```



### Get Items

Gets a multi-dimensional array of items stored in cart.

> **array** \$cart->getItems( );

```php
// Get all items in the cart
$allItems = $cart->getItems();

foreach ($allItems as $items) {
  foreach ($items as $item) {
    echo 'ID: '.$item['id'].'<br />';
    echo 'Qty: '.$item['quantity'].'<br />';
    echo 'Price: '.$item['attributes']['price'].'<br />';
    echo 'Size: '.$item['attributes']['size'].'<br />';
    echo 'Color: '.$item['attributes']['color'].'<br />';
  }
}
```


### Get Item

Gets a multi-dimensional array of one item stored in cart.

> **array** \$cart->getItem( **string** $id\[, **string** \$hash\] );

```php
// Get first one item from the cart with id 1001
$theItem = $cart->getItem('1001');

// Get one item from the cart with any id and hash
$theItem = $cart->getItem($item['id'], $item['hash']);
```



### Check Cart Empty

Checks if the cart is empty.

> **bool** \$cart->isEmpty( );

```php
if ($cart->isEmpty()) {
  echo 'There is nothing in the basket.';
}
```



### Get Total Item

Gets the total of items in the cart.

> **int** \$cart->getTotaltem( );

```php
echo 'There are '.$cart->getTotalItem().' items in the cart.';
```



### Get Total Quantity

Gets the total of quantity in the cart.

> **int** \$cart->getTotalQuantity( );

```php
echo $cart->getTotalQuantity();
```



### Get Attribute Total

Gets the sum of a specific attribute.

> **int** \$cart->getAttributeTotal( **string** $attribute );

```php
echo '<h3>Total Price: $'.number_format($cart->getAttributeTotal('price'), 2, '.', ',').'</h3>';
```



### Clear Cart

Clears all items in the cart.

> \$cart->clear( );

```php
$cart->clear();
```



### Destroy Cart

Destroys the entire cart session.

> \$cart->destroy( );

```php
$cart->destroy();
```



### Item Exists

Checks if an item exists in cart.

> **bool** \$cart->isItemExists( **string** \$id\[, **array** \$attributes\] );

```php
if ($cart->isItemExists('1001')) {
  echo 'This item already added to cart.';
}
```

