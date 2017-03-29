<?php
/**
 * Cart Class
 *
 * @category  E-commerce
 * @package   Cart.class.php
 * @author    Sei Kan <seikan.dev@gmail.com>
 * @copyright Copyright (c) 2014
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   1.0
 **/
class Cart {
	private $sessionId = '', $cookie = false, $itemLimit = 0, $quantityLimit = 99, $items = array(), $attributes = array(), $errors = array();
	
	/**
	 * Initialize shopping cart
	 *
	 * @param string $sessionId An unique ID for shopping cart session
	 * @param boolean $cookie Store cart items in cookie
	 */
	public function __construct($sessionId = '', $cookie = false) {
		if(!session_id())
			session_start();
		$this->sessionId = (!empty($sessionId)) ? $sessionId : str_replace('.', '_', ((isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '')) . '_cart';
		$this->cookie = ($cookie) ? true : false;
		$this->read();
	}
	/**
	 * Get errors
	 *
	 * @return array An array of errors occured
	 */
	public function getErrors() {
		return $this->errors;
	}
	/**
	 * Get last error
	 *
	 * @return string The last error occured
	 */
	public function getLastError() {
		return end($this->errors);
	}
	/**
	 * Get list of items in cart
	 *
	 * @return array An array of items in the cart
	 */
	public function getItems() {
		return $this->items;
	}
	/**
	 * Check if item exists
	 *
	 * @return boolen true if exists false if doesn't
	 */
	public function itemExist($Id) {
		return array_key_exists($Id, $this->items);
	}
	/**
	 * Get total 'priceAttr' of all items (from Attribute)
	 *
	 * @return string All Prices added
	 */
	public function totalPrice($priceAttr="price") {
		$totalPrice=0;
		foreach($this->items as $Id => $Qty){
			for($x=1;$x<=$Qty;$x++) {
				$totalPrice += $this->getAttribute($Id, $priceAttr);
			}
		}
		return $totalPrice;
	}
	/**
	 * Get total items in cart w/ qty
	 *
	 * @return string All Items added
	 */
	public function totalCount() {
		$totalQty=0;
		foreach($this->items as $Id => $Qty){
			for($x=1;$x<=$Qty;$x++) {
				$totalQty++;
			}
		}
		return $totalQty;
	}
	/**
	 * Set the maximum quantity per item accepted in cart
	 *
	 * @param integer $qty Quantity limit
	 *
	 * @return boolean Result as true/false
	 */
	public function setQuantityLimit($qty) {
		if(!$this->isInteger($qty)) {
			$this->errors[] = 'Cart::setQuantityLimit($qty): $qty must be integer.';
			return false;
		}
		$this->quantityLimit = $qty;
		return true;
	}
	/**
	 * Set the maximum of item accepted in cart
	 *
	 * @param integer $limit Item limit
	 *
	 * @return boolean Result as true/false
	 */
	public function setItemLimit($limit) {
		if(!$this->isInteger($limit)) {
			$this->errors[] = 'Cart::setItemLimit($limit): $limit must be integer.';
			return false;
		}
		$this->itemLimit = $limit;
		return true;
	}
	/**
	 * Add an item to cart
	 *
	 * @param integer $id An unique ID for the item
	 * @param integer $qty Quantity of item
	 *
	 * @return boolean Result as true/false
	 */
	public function add($id, $qty = 1) {
		if(!$this->isInteger($qty)) {
			$this->errors[] = 'Cart::add($qty): $qty must be integer.';
			return false;
		}
		if($this->itemLimit > 0 && count($this->items) >= $this->itemLimit)
			$this->clear();
		$this->items[$id] = (isset($this->items[$id])) ? ($this->items[$id] + $qty) : $qty;
		$this->items[$id] = ($this->items[$id] > $this->quantityLimit) ? $this->quantityLimit : $this->items[$id];
		$this->write();
		return true;
	}
	/**
	 * Add extra attributes to item in cart
	 *
	 * @param integer $id ID of targeted item
	 * @param string $key Name of the attribute
	 * @param string $value Value of the attribute
	 *
	 * @return boolean Result as true/false
	 */
	public function setAttribute($id, $key = '', $value = '') {
		if(!isset($this->items[$id])) {
			$this->errors[] = 'Cart::setAttribute($id, $key, $value): Item #' . $id . ' does not exist.';
			return false;
		}
		if(empty($key) || empty($value)) {
			$this->errors[] = 'Cart::setAttribute($id, $key, $value): Invalid value for $key or $value.';
			return false;
		}
		$this->attributes[$id][$key] = $value;
		$this->write();
		return true;
	}
	/**
	 * Remove an attribute from an item
	 *
	 * @param integer $id ID of targeted item
	 * @param string $key Name of the attribute
	 */
	public function unsetAttribute($id, $key) {
		unset($this->attributes[$id][$key]);
	}
	/**
	 * Get item attribute by key
	 *
	 * @param integer $id ID of targeted item
	 * @param string $key Name of the attribute
	 *
	 * @return string Value of the attribute
	 */
	public function getAttribute($id, $key) {
		if(!isset($this->attributes[$id][$key])) {
			$this->errors[] = 'Cart::getAttribute($id, $key): The attribute does not exist.';
			return false;
		}
		return $this->attributes[$id][$key];
	}
	/**
	 * Update item quantity
	 *
	 * @param integer $id ID of targeted item
	 * @param integer $qty Quantity
	 *
	 * @return boolean Result as true/false
	 */
	public function update($id, $qty) {
		if(!$this->isInteger($qty)) {
			$this->errors[] = 'Cart::update($id, $qty): $qty must be integer.';
			return false;
		}
		if($qty < 1)
			return $this->remove($id);
		$this->items[$id] = ($qty > $this->quantityLimit) ? $this->quantityLimit : $qty;
		$this->write();
		return true;
	}
	/**
	 * Remove item from cart
	 *
	 * @param integer $id ID of targeted item
	 */
	public function remove($id) {
		unset($this->items[$id]);
		unset($this->attributes[$id]);
		$this->write();
	}
	/**
	 * Clear all items in the cart
	 */
	public function clear() {
		$this->items = array();
		$this->attributes = array();
		$this->write();
	}
	/**
	 * Wipe out cart session and cookie
	 */
	public function destroy() {
		unset($_SESSION[$this->sessionId]);
		if($this->cookie)
			setcookie($this->sessionId, '', time()-86400);
		$this->items = array();
		$this->attributes = array();
	}
	/**
	 * Check if a string is integer
	 *
	 * @param string $int String to validate
	 *
	 * @return boolean Result as true/false
	 */
	private function isInteger($int) {
		return preg_match('/^[0-9]+$/', $int);
	}
	/**
	 * Read items from cart session
	 */
	private function read() {
		$listItem = ($this->cookie && isset($_COOKIE[$this->sessionId])) ? $_COOKIE[$this->sessionId] : (isset($_SESSION[$this->sessionId]) ? $_SESSION[$this->sessionId] : '');
		$listAttribute = (isset($_SESSION[$this->sessionId . '_attributes'])) ? $_SESSION[$this->sessionId . '_attributes'] : (($this->cookie && isset($_COOKIE[$this->sessionId . '_attributes'])) ? $_COOKIE[$this->sessionId . '_attributes'] : '');
		$items = @explode(';', $listItem);
		foreach($items as $item) {
			if(!$item || !strpos($item, ','))
				continue;
			list($id, $qty) = @explode(',', $item);
			$this->items[$id] = $qty;
		}
		$attributes = @explode(';', $listAttribute);
		foreach($attributes as $attribute) {
			if(!strpos($attribute, ','))
				continue;
			list($id, $key, $value) = @explode(',', $attribute);
			$this->attributes[$id][$key] = $value;
		}
	}
	/**
	 * Write changes to cart session
	 */
	private function write() {
		$_SESSION[$this->sessionId] = '';
		foreach($this->items as $id => $qty) {
			if(!$id)
				continue;
			$_SESSION[$this->sessionId] .= $id . ',' . $qty . ';';
		}
		$_SESSION[$this->sessionId . '_attributes'] = '';
		foreach($this->attributes as $id => $attributes) {
			if(!$id)
				continue;
			foreach($attributes as $key => $value)
			$_SESSION[$this->sessionId . '_attributes'] .= $id . ',' . $key . ',' . $value . ';';
		}
		$_SESSION[$this->sessionId] = rtrim($_SESSION[$this->sessionId], ';');
		$_SESSION[$this->sessionId . '_attributes'] = rtrim($_SESSION[$this->sessionId . '_attributes'], ';');
		if($this->cookie) {
			setcookie($this->sessionId, $_SESSION[$this->sessionId], time() + 604800);
			setcookie($this->sessionId . '_attributes', $_SESSION[$this->sessionId . '_attributes'], time() + 604800);
		}
	}
}
?>
