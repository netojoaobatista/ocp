<?php
namespace Neto\Commerce\Shipping;

use Neto\Commerce\ShoppingCart;

class ECT implements ShippingMethod
{
    const ENDPOINT = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx';
    const SEDEX = 40010;

    private $weight = 0;
    private $height = 0;
    private $width = 0;
    private $length = 0;

    private function calcPackageDimensions(ShoppingCart $shoppingCart)
    {
        foreach ($shoppingCart as $productId => $item) {
            $quantity = $shoppingCart->getItemQuantity($productId);

            $this->weight += $quantity * $item->getProductWeight();
            $this->height += $quantity * $item->getProductHeight();

            $currentWidth = $item->getProductWidth();
            $currentLength = $item->getProductLength();

            if ($currentWidth > $this->width) {
                $this->width = $currentWidth;
            }

            if ($currentLength > $this->length) {
                $this->length = $currentLength;
            }
        }
    }

    public function createSoapClient()
    {
        return new \SoapClient(static::ENDPOINT. '?wsdl',
                               array('trace' => true,
                                     'exceptions' => true,
                                     'style' => SOAP_DOCUMENT,
                                     'use' => SOAP_LITERAL,
                                     'soap_version' => SOAP_1_1,
                                     'encoding' => 'UTF-8'));
    }

    public function getShippingAmount(ShoppingCart $shoppingCart,
                                      $shippingFrom,
                                      $shippingTo)
    {
        $this->calcPackageDimensions($shoppingCart);

        $request = new \stdClass();
        $request->nCdEmpresa = '';
        $request->sDsSenha = '';
        $request->sCepOrigem = $shippingFrom;
        $request->sCepDestino = $shippingTo;
        $request->nVlPeso = $this->weight;
        $request->nCdFormato = 1;
        $request->nVlComprimento = $this->length;
        $request->nVlAltura = $this->height;
        $request->nVlLargura = $this->width;
        $request->sCdMaoPropria = 'n';
        $request->nVlValorDeclarado = 0;
        $request->sCdAvisoRecebimento = 'n';
        $request->nCdServico = ECT::SEDEX;
        $request->nVlDiametro = 0;

        $response = $this->createSoapClient()->CalcPrecoPrazo($request);
        $cServico = $response->CalcPrecoPrazoResult->Servicos->cServico;

        if (isset($cServico->Erro) && $cServico->Erro != 0) {
            throw new \RuntimeException($cServico->MsgErro, $cServico->Erro);
        }

        return $cServico->Valor;
    }
}