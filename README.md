# Refatoração O.C.P. - Shipping

> Software entities (classes, modules, functions, etc.) should be open for extension, but closed for modification.

Muitos já devem ter ouvido falar sobre Open/Closed Principle. O que muitos não entendem, é o que, de fato, significa ter entidades de software abertas para extensão, mas fechadas para edição. Acredito que, talvez, o motivo para grande a maioria das confusões em relação a esse princípio, se dê por conta de uma falha na exemplificação de um caso de uso real ou da ausência das consequências causadas pela violação.

O.C.P. se trata, na verdade, de evolução. Assim como pessoas evoluem e mudam conforme o tempo, o software também muda. Além disso, da mesma forma como acontece com as pessoas, que evoluem para a vida adulta, sem apagar sua infância, um software também deve evoluir, sem que suas entidades sejam modificadas para isso.

Se há algo que **não vai mudar** durante o ciclo de vida de um software, <u>é o fato de que todo software muda durante seu ciclo de vida</u>. Isso significa que, quanto mais velho é um software, mais entidades dependem umas das outras, consequentemente, mais entidades serão afetadas pela mudança.

Modificar uma entidade de software significa que uma série de reações em cadeia acontecerão. Se você tem uma boa cobertura de testes em seu software, já deve ter se deparado com esse tipo de situação, onde a simples edição de uma entidade, ocasiona a falha de vários testes, de entidades muitas vezes não diretamente relacionadas, mas que dependem de uma dependência direta da entidade modificada.

Se é fato que o software muda, e O.C.P. estabelece que as entidades de software devem ser fechadas para modificação, como é, então, um software conforme O.C.P.?

## O caso de uso - Calculando preço do frete e prazo de entrega
Vamos imaginar que tenhamos um E-Commerce que utiliza um serviço, inicialmente o da E.C.T. (Correios), para calcular o custo do frete para entregar uma encomenda em um endereço especificado pelo cliente.

### O código original

```php
<?php
namespace Neto\Commerce;

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

    public function getShippingAmount($shippingFrom, $shippingTo)
    {
        if ($this->count() == 0) {
            return 0;
        }

        $weight = 0;
        $height = 0;
        $width = 0;
        $length = 0;

        foreach ($this->items as $productId => $item) {
            $weight += $this->quantities[$productId] * $item->getProductWeight();
            $height += $this->quantities[$productId] * $item->getProductHeight();

            $currentWidth = $item->getProductWidth();
            $currentLength = $item->getProductLength();

            if ($currentWidth > $width) {
                $width = $currentWidth;
            }

            if ($currentLength > $length) {
                $length = $currentLength;
            }
        }

        $ect = new \Neto\Commerce\Shipping\ECT();

        return $ect->getShippingAmount($shippingFrom,
                                       $shippingTo,
                                       $weight,
                                       $height,
                                       $width,
                                       $length);
    }
}
```

### O problema
Observando o método `ShoppingCart::getShippingAmount()`, veremos facilmente alguns problemas:

1. Existe um acoplamento muito alto com o participante new `\Neto\Commerce\Shipping\ECT`.
2. A estratégia para o cálculo das dimensões da embalagem é muito frágil.

De fato, se observarmos atentamente, o acoplamento com o participante `ECT` é tão alto, que dificulta até os testes. Com o conhecimento específico sobre como mensurar o tamanho da embalagem e o participante ECT, o participante `ShoppingCart` toma decisões que tornarão a manutenção desse código extremamente difícil. De fato, se a empresa passar a fazer entregar com outro prestador de serviços, teremos que editar esse código.

O problema vai ficando maior, conforme novos prestadores de serviço de entrega vão sendo agregados à carteira de prestadores de serviço da empresa. Por exemplo, imaginem que, por uma questão de mercado, a loja passe a enviar os produtos com, além da ECT, também com a TRANSFOLHA ou outros serviços de logística. Se esse código não for refatorado rapidamente, sua rigidez pode causar edições semelhantes a:

```php
public function getShippingAmount($shippingWith, $shippingFrom, $shippingTo)
{
    if ($this->count() == 0) {
        return 0;
    }

    switch ($shippingWith) {
        case 'ECT':
            $weight = 0;
            $height = 0;
            $width = 0;
            $length = 0;

            foreach ($this->items as $productId => $item) {
                $weight += $this->quantities[$productId] * $item->getProductWeight();
                $height += $this->quantities[$productId] * $item->getProductHeight();

                $currentWidth = $item->getProductWidth();
                $currentLength = $item->getProductLength();

                if ($currentWidth > $width) {
                    $width = $currentWidth;
                }

                if ($currentLength > $length) {
                    $length = $currentLength;
                }
            }

            $ect = new \Neto\Commerce\Shipping\ECT();

            return $ect->getShippingAmount($shippingFrom,
                                           $shippingTo,
                                           $weight,
                                           $height,
                                           $width,
                                           $length);
        case 'TransFolha':
            //código para calcular frete com a TransFolha
        case 'FreteFácil':
            //código para calcular frete com PayPal Frete Fácil
        case 'UPS':
            //código para calcular frete com UPS
    }
}
```

Isso é péssimo, primeiro pois a cada edição, um bug em potencial é adicionado ao código. Segundo, pois a dificuldade em testar o código nos deixa no escuro sobre os possíveis bugs. Além disso, a cada nova adição de um prestador de serviço, o método `ShoppingCart::getShippingAmount()` assume a responsabilidade sobre a lógica do cálculo das dimensões da embalagem e frete.

Como o princípio de design O.C.P. nos diz que ninguém deve ter autorização para editar o código, precisamos garantir que esse código não precise ser editado. Isso é até muito simples de se fazer. De fato, a simples refatoração para permitir que o código seja testável, já vai eliminar a necessidade de se editar esse código no futuro. Quando digo sobre permitir que o código seja testável, estou dizendo que precisamos, de alguma forma, poder ter um `Mock` para o participante responsável pelo cálculo do frete.

### O teste:

```php
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
}
```

Como podemos ver, não existe um teste para o cálculo do frete. Mesmo porque, daquela forma, não há como testá-lo. Vamos começar a refatoração, garantindo que ele seja testável. Isso será feito através da injeção da dependência:

```php
<?php
namespace Neto\Commerce;

class ShoppingCartTest extends \PHPUnit_Framework_TestCase
{
    //...

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
}
```

A partir de agora, já começamos a ter o código testável. Com a inversão da dependência, nosso ShoppingCart não será mais responsável nem pelo cálculo das dimensões da embalagem, nem pela criação da instância da ECT. O interessante dessa primeira refatoração, é que o teste vai passar, mesmo não tendo refatorado ainda o código do ShoppingCart. Para finalizar o carrinho, tudo o que precisamos é verificar se o carrinho está fazendo a chamada corretamente:

```php
<?php
namespace Neto\Commerce;

class ShoppingCartTest extends \PHPUnit_Framework_TestCase
{
    //...

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
```

Vamos refatorar o carrinho agora:

```php
<?php
namespace Neto\Commerce;

use Neto\Commerce\Shipping\ShippingMethod;

class ShoppingCart implements \Countable, \IteratorAggregate
{
    //...

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
```

Como podemos ver, o código ficou muito mais elegante agora. Não existe conhecimento específico nenhum. Tudo o que o método `ShoppingCart::getShippingAmount()` faz, é delegar o cálculo para um outro participante. Ainda, para evitar qualquer tipo de acoplamento, o carrinho passa a si mesmo, para o participante `ShippingMethod`, para que as decisões sobre como calcular o frete sejam feitas exclusivamente pelo novo participante. Com o teste passando, podemos criar a interface `ShippingMethod`:

```php
<?php
namespace Neto\Commerce\Shipping;

use Neto\Commerce\ShoppingCart;

interface ShippingMethod
{
    public function getShippingAmount(ShoppingCart $shoppingCart,
                                      $shippingFrom,
                                      $shippingTo);
}
```

Agora, podemos implementar o participante ECT, ou qualquer um outro:

```php
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
```

## Tudo se resume em abstração

É claro que haverão casos que não serão tão simples como esse do exemplo, mas de forma geral, [Abstração](http://thegodclass.tumblr.com/post/9665624509/abstracao) é a chave para solucionar muitos dos problemas de design que possamos ter. Excesso de conhecimento causa um acoplamento alto. Quanto maior o acoplamento, maior é a dificuldade em se testar o código. Quanto maior a dificuldade em se testar o código, maior a probabilidade de não se testar o código e, consequentemente, maior a probabilidade de bugs.

Outro ponto importante, é que apesar do artigo se tratar de O.C.P., acabamos abordando S.R.P. ao remover a responsabilidade da estratégia de cálculo da embalagem, do participante `ShoppingCart`. Abordamos também um outro princípio importante, D.I.P., que além de garantir que o código fosse testável, ainda eliminou, pelo menos para esse caso, a necessidade de edição do `ShoppingCart`.

Entre todas as dicas dadas aqui, existe uma que deve sempre ser colocada no topo de qualquer lista de prioridades: **Garanta que seu código seja testável**, isso facilitará muito qualquer refatoração que seja necessária.
