<?php
namespace Neto\Commerce\Shipping;

use Neto\Commerce\Product;
use Neto\Commerce\ShoppingCart;

class ECTTest extends \PHPUnit_Framework_TestCase
{
    const SHIPPING_FROM = '14400000';
    const SHIPPING_TO = '01000000';

    private $weight = 0;
    private $height = 0;
    private $width = 0;
    private $length = 0;

    private function createEct(\SoapClient $soapClient)
    {
        $ect = $this->getMock('\Neto\Commerce\Shipping\ECT',
                              array('createSoapClient'));

        $ect->expects($this->any())
            ->method('createSoapClient')
            ->will($this->returnValue($soapClient));

        return $ect;
    }

    private function createExpectedResponse()
    {
        $response = new \stdClass();
        $response->CalcPrecoPrazoResult = new \stdClass();
        $response->CalcPrecoPrazoResult->Servicos = new \stdClass();
        $response->CalcPrecoPrazoResult->Servicos->cServico = new \stdClass();

        $cServico = $response->CalcPrecoPrazoResult->Servicos->cServico;
        $cServico->Codigo = ECT::SEDEX;
        $cServico->Valor = 123;
        $cServico->ValorMaoPropria = 0;
        $cServico->ValorAvisoRecebimento = 0;
        $cServico->EntregaDomiciliar = 'S';
        $cServico->ValorValorDeclarado = 0;
        $cServico->EntregaSabado = 'S';
        $cServico->Erro = 0;
        $cServico->MsgErro = null;

        return $response;
    }

    private function createShoppingCart()
    {
        $shoppingCart = new ShoppingCart();
        $shoppingCart->addItem(new Product(123, 'item 1', 100, 1, 2, 15, 30));
        $shoppingCart->addItem(new Product(456, 'item 2', 100, 1, 2, 15, 30));
        $shoppingCart->addItem(new Product(789, 'item 3', 100, 1, 2, 15, 30));

        $this->weight = 3;
        $this->height = 6;
        $this->width = 15;
        $this->length = 30;

        return $shoppingCart;
    }

    private function createSoapClient()
    {
        $soapClient = $this->getMockBuilder('\SoapClient')
                           ->setMethods(array('CalcPrecoPrazo'))
                           ->disableOriginalConstructor()
                           ->getMock();

        return $soapClient;
    }

    /**
     * @testdox ECT::createSoapClient() will return an instance of \SoapClient
     */
    public function testCreateSoapClientWillReturnAnInstanceOfSoapClient()
    {
        $ect = new ECT();

        $this->assertInstanceOf('\SoapClient', $ect->createSoapClient());
    }

    /**
     * @testdox ECT::getShippingAmount() will call the SOAP method CalcPrecoPrazo.
     */
    public function testGetShippingAmountWillCallTheSoapMethodCalcPrecoPrazo()
    {
        $shippingFrom = self::SHIPPING_FROM;
        $shippingTo = self::SHIPPING_TO;

        $soapClient = $this->createSoapClient();
        $soapClient->expects($this->at(0))
                   ->method('CalcPrecoPrazo')
                   ->will($this->returnValue($this->createExpectedResponse()));

        $ect = $this->createEct($soapClient);
        $ect->getShippingAmount($this->createShoppingCart(),
                                $shippingFrom,
                                $shippingTo);
    }

    /**
     * @testdox ECT::getShippingAmount() will send the expected Soap Request to ECT service.
     */
    public function testGetShippingAmountWillSendASoapRequestToECTWebservice()
    {
        $shippingFrom = self::SHIPPING_FROM;
        $shippingTo = self::SHIPPING_TO;

        $shoppingCart = $this->createShoppingCart();

        $expectedRequest = new \stdClass();
        $expectedRequest->nCdEmpresa = '';
        $expectedRequest->sDsSenha = '';
        $expectedRequest->sCepOrigem = $shippingFrom;
        $expectedRequest->sCepDestino = $shippingTo;
        $expectedRequest->nVlPeso = $this->weight;
        $expectedRequest->nCdFormato = 1;
        $expectedRequest->nVlComprimento = $this->length;
        $expectedRequest->nVlAltura = $this->height;
        $expectedRequest->nVlLargura = $this->width;
        $expectedRequest->sCdMaoPropria = 'n';
        $expectedRequest->nVlValorDeclarado = 0;
        $expectedRequest->sCdAvisoRecebimento = 'n';
        $expectedRequest->nCdServico = ECT::SEDEX;
        $expectedRequest->nVlDiametro = 0;
        
        $soapClient = $this->createSoapClient();
        $soapClient->expects($this->at(0))
                   ->method('CalcPrecoPrazo')
                   ->with($expectedRequest)
                   ->will($this->returnValue($this->createExpectedResponse()));

        $ect = $this->createEct($soapClient);

        $ect->getShippingAmount($shoppingCart, $shippingFrom, $shippingTo);
    }

    /**
     * @testdox ECT::getShippingAmount() will return the calculated shipping amount.
     */
    public function testGetShippingAmountWillReturnTheCalculatedShippingAmount()
    {
        $shippingFrom = self::SHIPPING_FROM;
        $shippingTo = self::SHIPPING_TO;
        $expectedResponse = $this->createExpectedResponse();
        $cServico = $expectedResponse->CalcPrecoPrazoResult->Servicos->cServico;

        $soapClient = $this->createSoapClient();
        $soapClient->expects($this->at(0))
                   ->method('CalcPrecoPrazo')
                   ->will($this->returnValue($expectedResponse));

        $ect = $this->createEct($soapClient);

        $this->assertEquals($cServico->Valor,
                            $ect->getShippingAmount($this->createShoppingCart(),
                                                    $shippingFrom,
                                                    $shippingTo));
    }

    /**
     * @testdox ECT::getShippingAmount() will throw an Exception in case of errors.
     * @expectedException \RuntimeException
     * @expectedExceptionMessage An error message
     * @expectedExceptionCode 1
     */
    public function testGetShippingAmountWillThrowAnExceptionInCaseOfErrors()
    {
        $shippingFrom = self::SHIPPING_FROM;
        $shippingTo = self::SHIPPING_TO;
        $expectedResponse = $this->createExpectedResponse();

        $cServico = $expectedResponse->CalcPrecoPrazoResult->Servicos->cServico;
        $cServico->Erro = 1;
        $cServico->MsgErro = 'An error message';

        $soapClient = $this->createSoapClient();
        $soapClient->expects($this->at(0))
                   ->method('CalcPrecoPrazo')
                   ->will($this->returnValue($expectedResponse));

        $ect = $this->createEct($soapClient);

        $this->assertEquals($cServico->Valor,
                            $ect->getShippingAmount($this->createShoppingCart(),
                                                    $shippingFrom,
                                                    $shippingTo));
    }
}