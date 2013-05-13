<?php
namespace Neto\Commerce;

use Neto\Commerce\Shipping\ShippingMethod;

class ShoppingCart implements \Countable, \IteratorAggregate
{
    private $items = array();
    private $quantities = array();

    public function addItem(Product $product, $quantity = 1)
    {
        $productId = $product->getProductId();

        if (isset($this->items[$productId])) {
            $quantity += $this->quantities[$productId];
        }

        $this->items[$productId] = $product;
        $this->quantities[$productId] = $quantity;
    }

    /**
     * @see \Countable::count()
     */
    public function count()
    {
        return count($this->items);
    }

    public function getItemAmount($productId)
    {
        if (!isset($this->items[$productId])) {
            throw new \UnexpectedValueException('Item not found');
        }

        $quantity = $this->quantities[$productId];

        return $quantity * $this->getItemPrice($productId);
    }

    public function getItemPrice($productId)
    {
        if (!isset($this->items[$productId])) {
            throw new \UnexpectedValueException('Item not found');
        }

        return $this->items[$productId]->getProductPrice();
    }

    public function getItemQuantity($productId)
    {
        if (!isset($this->quantities[$productId])) {
            return 0;
        }

        return $this->quantities[$productId];
    }

    public function getItemTotal()
    {
        $total = 0;

        foreach ($this->items as $productId => $item) {
            $total += $this->getItemAmount($productId);
        }

        return $total;
    }

    /**
     * @see \IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    public function getShippingAmount(ShippingMethod $shippingMethod,
                                      $shippingFrom,
                                      $shippingTo)
    {
        if ($this->count() == 0) {
            return 0;
        }

        return $shippingMethod->getShippingAmount($this,
                                                  $shippingFrom,
                                                  $shippingTo);
    }
}