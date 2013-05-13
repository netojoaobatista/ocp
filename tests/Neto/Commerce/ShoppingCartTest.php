<?php
namespace Neto\Commerce;

class ShoppingCartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @testdox Initial value for ShoppingCart::count() will be zero
     */
    public function testInitialItemCountInTheCartIsZero()
    {
        $cart = new ShoppingCart();

        $this->assertCount(0, $cart);
    }

    /**
     * @testdox Adding an item to shopping cart with ShoppingCart::addItem(Product $product, $quantity = 1) will increase the item count.
     */
    public function testAddingAnItemToShoppingCartWillIncreaseTheItemCount()
    {
        $cart = new ShoppingCart();

        $this->assertCount(0, $cart);

        $cart->addItem(new Product(123, 'item 0', 100, 1, 2, 15, 30));

        $this->assertCount(1, $cart);

        $cart->addItem(new Product(234, 'item 1', 100, 1, 2, 15, 30));

        $this->assertCount(2, $cart);
    }

    /**
     * @testdox Adding the same item twice to shopping cart will not change the item count.
     */
    public function testAddingTheSameItemTwiceToShoppingCartWillNotChangeTheItemCount()
    {
        $cart = new ShoppingCart();

        $this->assertCount(0, $cart);

        $cart->addItem(new Product(123, 'item 0', 100, 1, 2, 15, 30));

        $this->assertCount(1, $cart);

        $cart->addItem(new Product(123, 'item 0', 100, 1, 2, 15, 30));

        $this->assertCount(1, $cart);
    }

    /**
     * @testdox ShoppingCart::getItemQuantity($productId) will return zero if the product was not added to the shopping cart.
     */
    public function testGetProductQuantityWillReturnZeroIfTheProductWasNotAddedToTheShoppingCart()
    {
        $cart = new ShoppingCart();

        $this->assertEquals(0, $cart->getItemQuantity(123));
    }

    /**
     * @testdox Adding the same item twice to shopping cart will increase its quantity
     */
    public function testAddingTheSameItemTwiceToShoppingCartWillIncreaseItsQuantity()
    {
        $product = new Product(123, 'item 0', 100, 1, 2, 15, 30);
        $productId = $product->getProductId();

        $cart = new ShoppingCart();

        $this->assertEquals(0, $cart->getItemQuantity($productId));

        $cart->addItem($product);

        $this->assertEquals(1, $cart->getItemQuantity($productId));

        $cart->addItem($product);

        $this->assertEquals(2, $cart->getItemQuantity($productId));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @testdox ShoppingCart::getItemPrice($productId) will throw an exception if product was not found in the cart.
     */
    public function testGetItemPriceWillThrowAnExceptionIfProductWasNotFoundInTheCart()
    {
        $cart = new ShoppingCart();

        $cart->getItemPrice(123);
    }

    /**
     * @testdox ShoppingCart::getItemPrice($productId) will return the product price.
     */
    public function testGetItemPriceWillReturnTheProductPrice()
    {
        $productPrice = 100;
        $product = new Product(123, 'item 0', $productPrice, 1, 2, 15, 30);
        $productId = $product->getProductId();
        $cart = new ShoppingCart();

        $cart->addItem($product);

        $this->assertEquals($productPrice, $cart->getItemPrice($productId));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @testdox ShoppingCart::getItemAmount($productId) will throw an exception if product was not found in the cart.
     */
    public function testGetItemAmountWillThrowAnExceptionIfProductWasNotFoundInTheCart()
    {
        $cart = new ShoppingCart();

        $cart->getItemAmount(123);
    }

    /**
     * @testdox ShoppingCart::getItemAmount($productId) will return the product price multiplied by its quantity.
     */
    public function testGetItemAmountWillReturnTheProductPriceMultipliedByItsQuantity()
    {
        $productPrice = 100;
        $quantity = 5;
        $product = new Product(123, 'item 0', $productPrice, 1, 2, 15, 30);
        $productId = $product->getProductId();
        $cart = new ShoppingCart();

        $cart->addItem($product, $quantity);

        $this->assertEquals($productPrice * $quantity,
                            $cart->getItemAmount($productId));
    }

    /**
     * @testdox ShoppingCart::getItemTotal() will return the sum of all product price multiplied by its quantity in the cart.
     */
    public function testGetItemTotalWillReturnTheSumOfAllItemsInTheCart()
    {
        $cart = new ShoppingCart();

        $this->assertEquals(0, $cart->getItemTotal());

        $cart->addItem(new Product(123, 'item 0', 100, 1, 2, 15, 30), 2);
        $cart->addItem(new Product(234, 'item 1', 100, 1, 2, 15, 30), 2);

        $this->assertEquals(400, $cart->getItemTotal());
    }

    /**
     * @testdox ShoppingCart::getIterator() will return an \Iterator with all products in the cart.
     */
    public function testGetIteratorWillReturnAnIteratorWithAllProductsInTheCart()
    {
        $products = array(
            new Product(123, 'item 1', 100, 1, 2, 15, 30),
            new Product(456, 'item 2', 100, 1, 2, 15, 30),
            new Product(789, 'item 3', 100, 1, 2, 15, 30)
        );

        $cart = new ShoppingCart();

        foreach ($products as $product) {
            $cart->addItem($product);
        }

        $iterator = $cart->getIterator();
        $iterator->rewind();

        $this->assertInstanceOf('\Iterator', $iterator);

        for ($i = 0, $t = count($products); $i < $t; ++$i, $iterator->next()) {
            $this->assertSame($products[$i], $iterator->current());
        }

        $this->assertFalse($iterator->valid());
    }

    /**
     * @testdox ShoppingCart::getShippingAmount() will return zero if cart is empty.
     */
    public function testGetShippingAmountWillReturnZeroIfCartIfEmpty()
    {
        $shippingMethod = $this->getMock('\Neto\Commerce\Shipping\ShippingMethod',
                                         array('getShippingAmount'));

        $cart = new ShoppingCart();

        $this->assertEquals(0, $cart->getShippingAmount($shippingMethod,
                                                        '14400000',
                                                        '01000000'));
    }

    /**
     * @testdox ShoppingCart::getShippingAmount() will call ShippingMethod::getShippingAmount() to calculates the shipping amount.
     */
    public function testGetShippingAmountWillCallShippingMethodToCalculatesTheShippingAmount()
    {
        $cart = new ShoppingCart();
        $cart->addItem(new Product(123, 'item 0', 100, 1, 2, 15, 30), 2);

        $shippingFrom = '14400000';
        $shippingTo = '01000000';

        $shippingMethod = $this->getMock('\Neto\Commerce\Shipping\ShippingMethod',
                                         array('getShippingAmount'));

        $shippingMethod->expects($this->at(0))
                       ->method('getShippingAmount')
                       ->with($cart, $shippingFrom, $shippingTo);

        $cart->getShippingAmount($shippingMethod,
                                 $shippingFrom,
                                 $shippingTo);
    }
}