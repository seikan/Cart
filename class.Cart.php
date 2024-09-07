<?php

/**
 * Cart: A very simple PHP cart library.
 *
 * Copyright (c) 2017 Sei Kan
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright  2017 Sei Kan <seikan.dev@gmail.com>
 * @license    http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 * @see       https://github.com/seikan/Cart
 */
class Cart
{
    /**
     * An unique ID for the cart.
     *
     * @var string
     */
    protected $cartId;

    /**
     * Maximum item allowed in the cart.
     *
     * @var int
     */
    protected $cartMaxItem = 0;

    /**
     * Maximum quantity of a item allowed in the cart.
     *
     * @var int
     */
    protected $itemMaxQuantity = 0;

    /**
     * Enable or disable cookie.
     *
     * @var bool
     */
    protected $useCookie = false;

    /**
     * A collection of cart items.
     *
     * @var array
     */
    private $items = [];

    /**
     * Discount applied to the cart.
     *
     * @var float
     */
    private $discount = 0.0;

    /**
     * Shipping cost for the cart.
     *
     * @var float
     */
    private $shippingCost = 0.0;

    /**
     * Initialize cart.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        if (!session_id()) {
            session_start();
        }

        if (isset($options['cartMaxItem']) && preg_match('/^\d+$/', $options['cartMaxItem'])) {
            $this->cartMaxItem = $options['cartMaxItem'];
        }

        if (isset($options['itemMaxQuantity']) && preg_match('/^\d+$/', $options['itemMaxQuantity'])) {
            $this->itemMaxQuantity = $options['itemMaxQuantity'];
        }

        if (isset($options['useCookie']) && $options['useCookie']) {
            $this->useCookie = true;
        }

        $this->cartId = md5((isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : 'SimpleCart') . '_cart';

        $this->read();
    }

    /**
     * Get items in the cart.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Check if the cart is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty(array_filter($this->items));
    }

    /**
     * Get the total number of items in the cart.
     *
     * @return int
     */
    public function getTotalItem()
    {
        $total = 0;

        foreach ($this->items as $items) {
            foreach ($items as $item) {
                ++$total;
            }
        }

        return $total;
    }

    /**
     * Get the total quantity of items in the cart.
     *
     * @return int
     */
    public function getTotalQuantity()
    {
        $quantity = 0;

        foreach ($this->items as $items) {
            foreach ($items as $item) {
                $quantity += $item['quantity'];
            }
        }

        return $quantity;
    }

    /**
     * Get the sum of a specific attribute (e.g., price) in the cart.
     *
     * @param string $attribute
     *
     * @return int
     */
    public function getAttributeTotal($attribute = 'price')
    {
        $total = 0;

        foreach ($this->items as $items) {
            foreach ($items as $item) {
                if (isset($item['attributes'][$attribute])) {
                    $total += $item['attributes'][$attribute] * $item['quantity'];
                }
            }
        }

        return $total;
    }

    /**
     * Remove all items from the cart.
     */
    public function clear()
    {
        $this->items = [];
        $this->write();
    }

    /**
     * Check if an item exists in the cart.
     *
     * @param string $id
     * @param array  $attributes
     *
     * @return bool
     */
    public function isItemExists($id, $attributes = [])
    {
        $attributes = (is_array($attributes)) ? array_filter($attributes) : [$attributes];

        if (isset($this->items[$id])) {
            $hash = md5(json_encode($attributes));
            foreach ($this->items[$id] as $item) {
                if ($item['hash'] == $hash) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get one item from the cart.
     *
     * @param string $id
     * @param string $hash
     *
     * @return array
     */
    public function getItem($id, $hash = null)
    {
        if ($hash) {
            $key = array_search($hash, array_column($this->items[$id], 'hash'));
            if ($key !== false) {
                return $this->items[$id][$key];
            }
            return false;
        } else {
            return reset($this->items[$id]);
        }
    }

    /**
     * Add an item to the cart.
     *
     * @param string $id
     * @param int    $quantity
     * @param array  $attributes
     *
     * @return bool
     */
    public function add($id, $quantity = 1, $attributes = [])
    {
        $quantity = (preg_match('/^\d+$/', $quantity)) ? $quantity : 1;
        $attributes = (is_array($attributes)) ? array_filter($attributes) : [$attributes];
        $hash = md5(json_encode($attributes));

        if (count($this->items) >= $this->cartMaxItem && $this->cartMaxItem != 0) {
            return false;
        }

        if (isset($this->items[$id])) {
            foreach ($this->items[$id] as $index => $item) {
                if ($item['hash'] == $hash) {
                    $this->items[$id][$index]['quantity'] += $quantity;
                    $this->items[$id][$index]['quantity'] = ($this->itemMaxQuantity < $this->items[$id][$index]['quantity'] && $this->itemMaxQuantity != 0) ? $this->itemMaxQuantity : $this->items[$id][$index]['quantity'];

                    $this->write();

                    return true;
                }
            }
        }

        $this->items[$id][] = [
            'id'         => $id,
            'quantity'   => ($quantity > $this->itemMaxQuantity && $this->itemMaxQuantity != 0) ? $this->itemMaxQuantity : $quantity,
            'hash'       => $hash,
            'attributes' => $attributes,
        ];

        $this->write();

        return true;
    }

    /**
     * Update item quantity.
     *
     * @param string $id
     * @param int    $quantity
     * @param array  $attributes
     *
     * @return bool
     */
    public function update($id, $quantity = 1, $attributes = [])
    {
        $quantity = (preg_match('/^\d+$/', $quantity)) ? $quantity : 1;

        if ($quantity == 0) {
            $this->remove($id, $attributes);

            return true;
        }

        if (isset($this->items[$id])) {
            $hash = md5(json_encode(array_filter($attributes)));

            foreach ($this->items[$id] as $index => $item) {
                if ($item['hash'] == $hash) {
                    $this->items[$id][$index]['quantity'] = $quantity;
                    $this->items[$id][$index]['quantity'] = ($this->itemMaxQuantity < $this->items[$id][$index]['quantity'] && $this->itemMaxQuantity != 0) ? $this->itemMaxQuantity : $this->items[$id][$index]['quantity'];

                    $this->write();

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update the attributes of a specific item in the cart.
     *
     * @param string $id
     * @param string $hash
     * @param array  $attributes
     *
     * @return bool
     */
    public function updateAttributes($id, $hash, $attributes = [])
    {
        if (isset($this->items[$id])) {
            foreach ($this->items[$id] as $index => $item) {
                if ($item['hash'] == $hash) {
                    foreach ($attributes as $key => $value) {
                        $this->items[$id][$index]['attributes'][$key] = $value;
                    }

                    $this->write();

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Remove an item from the cart.
     *
     * @param string $id
     * @param array  $attributes
     *
     * @return bool
     */
    public function remove($id, $attributes = [])
    {
        $attributes = (is_array($attributes)) ? array_filter($attributes) : [$attributes];

        if (isset($this->items[$id])) {
            if (empty($attributes)) {
                unset($this->items[$id]);
            } else {
                $hash = md5(json_encode($attributes));

                foreach ($this->items[$id] as $index => $item) {
                    if ($item['hash'] == $hash) {
                        unset($this->items[$id][$index]);

                        if (empty($this->items[$id])) {
                            unset($this->items[$id]);
                        }
                    }
                }
            }

            $this->write();

            return true;
        }

        return false;
    }

    /**
     * Apply a discount code to the cart.
     *
     * @param string $code
     * @param float  $amount
     */
    public function applyDiscount($code, $amount)
    {
        $this->discount = $amount;
    }

    /**
     * Get the total after applying the discount.
     *
     * @return float
     */
    public function getTotalWithDiscount()
    {
        return $this->getAttributeTotal('price') - $this->discount;
    }

    /**
     * Set shipping cost for the cart.
     *
     * @param float $cost
     */
    public function setShippingCost($cost)
    {
        $this->shippingCost = $cost;
    }

    /**
     * Get the total after adding shipping cost.
     *
     * @return float
     */
    public function getTotalWithShipping()
    {
        return $this->getTotalWithDiscount() + $this->shippingCost;
    }

    /**
     * Save cart to database for persistent sessions.
     *
     * @param int $userId
     */
    public function saveToDatabase($userId)
    {
        $cartData = serialize($this->items);
        // Save $cartData to the database associated with the $userId.
    }

    /**
     * Load cart from database for persistent sessions.
     *
     * @param int $userId
     */
    public function loadFromDatabase($userId)
    {
        // Fetch cart data from the database for the given $userId.
        // Example: $cartData = fetch_cart_data($userId);
        // $this->items = unserialize($cartData);
    }

    /**
     * Save cart data to session or cookie.
     */
    protected function write()
    {
        if ($this->useCookie) {
            setcookie($this->cartId, json_encode($this->items), time() + 604800, '/');
        } else {
            $_SESSION[$this->cartId] = $this->items;
        }
    }

    /**
     * Read cart data from session or cookie.
     */
    protected function read()
    {
        $this->items = ($this->useCookie && isset($_COOKIE[$this->cartId])) ? json_decode($_COOKIE[$this->cartId], true) : ((isset($_SESSION[$this->cartId])) ? $_SESSION[$this->cartId] : []);
    }
}
