<?php
namespace Neto\Commerce\Shipping;

use Neto\Commerce\ShoppingCart;

interface ShippingMethod
{
    public function getShippingAmount(ShoppingCart $shoppingCart,
                                      $shippingFrom,
                                      $shippingTo);
}