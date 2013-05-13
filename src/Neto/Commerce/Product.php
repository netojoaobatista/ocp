<?php
namespace Neto\Commerce;

class Product
{
    private $productHeight;
    private $productId;
    private $productLength;
    private $productName;
    private $productPrice;
    private $productWeight;
    private $productWidth;

    public function __construct($productId,
                                $productName,
                                $productPrice,
                                $productWeight,
                                $productHeight,
                                $productWidth,
                                $productLength)
    {
        $this->setProductId($productId);
        $this->setProductName($productName);
        $this->setProductPrice($productPrice);
        $this->setProductWeight($productWeight);
        $this->setProductHeight($productHeight);
        $this->setProductWidth($productWidth);
        $this->setProductLength($productLength);
    }

    public function getProductHeight()
    {
        return $this->productHeight;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function getProductLength()
    {
        return $this->productLength;
    }

    public function getProductName()
    {
        return $this->productName;
    }

    public function getProductPrice()
    {
        return $this->productPrice;
    }

    public function getProductWeight()
    {
        return $this->productWeight;
    }

    public function getProductWidth()
    {
        return $this->productWidth;
    }

    public function setProductHeight($productHeight)
    {
        if (!is_numeric($productHeight)) {
            throw new \InvalidArgumentException('Invalid height');
        }

        $this->productHeight = $productHeight;
    }

    public function setProductId($productId)
    {
        if (!is_int($productId)) {
            throw new \InvalidArgumentException('Invalid id');
        }

        $this->productId = $productId;
    }

    public function setProductLength($productLength)
    {
        if (!is_int($productLength)) {
            throw new \InvalidArgumentException('Invalid length');
        }

        $this->productLength = $productLength;
    }

    public function setProductName($productName)
    {
        if (!is_scalar($productName)) {
            throw new \InvalidArgumentException('Invalid name');
        }

        $this->productName = $productName;
    }

    public function setProductPrice($productPrice)
    {
        if (!is_numeric($productPrice)) {
            throw new \InvalidArgumentException('Invalid price');
        }

        $this->productPrice = (float) $productPrice;
    }

    public function setProductWeight($productWeight)
    {
        if (!is_numeric($productWeight)) {
            throw new \InvalidArgumentException('Invalid weight');
        }

        $this->productWeight = $productWeight;
    }

    public function setProductWidth($productWidth)
    {
        if (!is_numeric($productWidth)) {
            throw new \InvalidArgumentException('Invalid width');
        }

        $this->productWidth = $productWidth;
    }
}