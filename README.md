cart.class.php
==============

A simple PHP shopping cart class to use in ecommerce web applications. You can store your cart items in both session and cookies.

###Example

```php
require_once('cart.class.php');

// Initialize cart
$cart = new Cart();

// Settings
$cart->setQuantityLimit(10); // Each items cannot exceed 10 in quantity
$cart->setItemLimit(5); // Each customer cannot buy more than 5 items

// Add items to cart
$cart->add(8001); // Add 1 item #8001 to cart
$cart->add(8012, 5); // Add 5 item #8012 to cart
$cart->add(8018, 3); // Add 3 item #8018 to cart

// Get items in cart
$items = $cart->getItems();

foreach($items as $id=>$qty)
    echo 'Item #' . $id . ' => ' . $qty . '<br />';

// Add new attribute to item #8012
$cart->setAttribute(8012, 'description', 'This item is having 30% off!');

// Update cart
$cart->update(8012, 2); // Change quantity for item #8012 from 5 to 2

// Remove item #8018
$cart->remove(8018);

// Close cart session
$cart->destroy();
```