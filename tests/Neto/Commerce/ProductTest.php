<?php
namespace Neto\Commerce;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    private function createProductMock(array $methods)
    {
        return $this->getMockBuilder('\Neto\Commerce\Product')
                    ->setMethods($methods)
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @testdox Product::setProductId($productId) will throw an InvalidArgumentException if id is not an integer
     */
    public function testSetIdWillThrowAnExceptionIfIdIsNotAnInteger()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductId('invalidId');
    }

    /**
     * @testdox Product::getProductId() will return the id setted with Product::setProductId()
     */
    public function testGetIdWillReturnTheSettedId()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductId(123);

        $this->assertEquals(123, $product->getProductId());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @testdox Product::setProductName($productName) will throw an InvalidArgumentException if name is not scalar
     */
    public function testSetNameWillThrowAnExceptionIfNameIsNotScalar()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductName(array());
    }

    /**
     * @testdox Product::getProductName() will return the name setted with Product::setProductName()
     */
    public function testGetNameWillReturnTheSettedName()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductName('name');

        $this->assertEquals('name', $product->getProductName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @testdox Product::setProductPrice($productPrice) will throw an InvalidArgumentException if price is not numeric
     */
    public function testSetPriceWillThrowAnExceptionIfPriceIsNotNumeric()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductPrice('invalid price');
    }

    /**
     * @testdox Product::getProductPrice() will return the price setted with Product::setProductPrice()
     */
    public function testGetPriceWillReturnTheSettedPrice()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductPrice(123.0);

        $this->assertEquals(123.0, $product->getProductPrice());
    }

    /**
     * @testdox Product::getProductPrice() will always return a float
     */
    public function testGetPriceWillAlwaysReturnAFloat()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductPrice(123);

        $this->assertInternalType('float', $product->getProductPrice());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @testdox Product::setProductWeight($productWeight) will throw an InvalidArgumentException if weight is not numeric
     */
    public function testSetWeightWillThrowAnExceptionIfWeightIsNotNumeric()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductWeight('invalid weight');
    }

    /**
     * @testdox Product::getProductWeight() will return the weight setted with Product::setProductWeight()
     */
    public function testGetWeightWillReturnTheSettedWeight()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductWeight(1);

        $this->assertEquals(1, $product->getProductWeight());
    }

     /**
     * @expectedException \InvalidArgumentException
     * @testdox Product::setProductHeight($productHeight) will throw an InvalidArgumentException if height is not numeric
     */
    public function testSetHeightWillThrowAnExceptionIfHeightIsNotNumeric()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductHeight('invalid height');
    }

    /**
     * @testdox Product::getProductHeight() will return the height setted with Product::setProductHeight()
     */
    public function testGetHeightWillReturnTheSettedHeight()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductHeight(1);

        $this->assertEquals(1, $product->getProductHeight());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @testdox Product::setProductWidth($productWidth) will throw an InvalidArgumentException if width is not numeric
     */
    public function testSetWidthWillThrowAnExceptionIfWidthIsNotNumeric()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductWidth('invalid width');
    }

    /**
     * @testdox Product::getProductWidth() will return the width setted with Product::setProductWidth()
     */
    public function testGetWidthWillReturnTheSettedWidth()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductWidth(1);

        $this->assertEquals(1, $product->getProductWidth());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @testdox Product::setProductLength($productLength) will throw an InvalidArgumentException if length is not numeric
     */
    public function testSetLengthWillThrowAnExceptionIfLengthIsNotNumeric()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductLength('invalid length');
    }

    /**
     * @testdox Product::getProductLength() will return the width setted with Product::setProductLength()
     */
    public function testGetLengthWillReturnTheSettedLength()
    {
        $product = $this->createProductMock(array('__construct'));
        $product->setProductLength(1);

        $this->assertEquals(1, $product->getProductLength());
    }

    /**
     * @testdox Product::__construct($id, $name, $price, $weight, $height, $width, $length) will set ID, name, price, weight and product dimensions
     */
    public function testProductConstructorWillSetIdNamePriceAndDimensions()
    {
        $productId = 123;
        $productName = 'name';
        $productPrice = 100.00;
        $productWeight = 1;
        $productHeight = 2;
        $productWidth = 15;
        $productLength = 30;

        $product = $this->createProductMock(array('setProductId',
                                                  'setProductName',
                                                  'setProductPrice',
                                                  'setProductWeight',
                                                  'setProductHeight',
                                                  'setProductWidth',
                                                  'setProductLength'));

        $product->expects($this->any())
                ->method('setProductId')
                ->with($productId);

        $product->expects($this->any())
                ->method('setProductName')
                ->with($productName);

        $product->expects($this->any())
                ->method('setProductPrice')
                ->with($productPrice);

        $product->expects($this->any())
                ->method('setProductWeight')
                ->with($productWeight);

        $product->expects($this->any())
                ->method('setProductHeight')
                ->with($productHeight);

        $product->expects($this->any())
                ->method('setProductWidth')
                ->with($productWidth);

        $product->expects($this->any())
                ->method('setProductLength')
                ->with($productLength);

        $product->__construct($productId,
                              $productName,
                              $productPrice,
                              $productWeight,
                              $productHeight,
                              $productWidth,
                              $productLength);
    }
}