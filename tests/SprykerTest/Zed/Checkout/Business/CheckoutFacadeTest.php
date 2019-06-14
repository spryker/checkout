<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Checkout\Business;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\AddressBuilder;
use Generated\Shared\DataBuilder\CurrencyBuilder;
use Generated\Shared\DataBuilder\CustomerBuilder;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\DataBuilder\PaymentBuilder;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\DataBuilder\ShipmentBuilder;
use Generated\Shared\DataBuilder\StoreBuilder;
use Generated\Shared\DataBuilder\TotalsBuilder;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StockProductTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Orm\Zed\Country\Persistence\SpyCountry;
use Orm\Zed\Customer\Persistence\SpyCustomerQuery;
use Orm\Zed\Product\Persistence\SpyProduct;
use Orm\Zed\Product\Persistence\SpyProductAbstract;
use Orm\Zed\Sales\Persistence\SpySalesOrderItemQuery;
use Orm\Zed\Stock\Persistence\SpyStock;
use Orm\Zed\Stock\Persistence\SpyStockProduct;
use Orm\Zed\Stock\Persistence\SpyStockQuery;
use Spryker\Zed\Availability\Communication\Plugin\ProductsAvailableCheckoutPreConditionPlugin;
use Spryker\Zed\Checkout\CheckoutConfig;
use Spryker\Zed\Checkout\CheckoutDependencyProvider;
use Spryker\Zed\Customer\Business\CustomerBusinessFactory;
use Spryker\Zed\Customer\Business\CustomerFacade;
use Spryker\Zed\Customer\Communication\Plugin\CustomerPreConditionCheckerPlugin;
use Spryker\Zed\Customer\Communication\Plugin\OrderCustomerSavePlugin;
use Spryker\Zed\Customer\CustomerDependencyProvider;
use Spryker\Zed\Customer\Dependency\Facade\CustomerToMailInterface;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Oms\OmsConfig;
use Spryker\Zed\Sales\Business\SalesBusinessFactory;
use Spryker\Zed\Sales\Business\SalesFacade;
use Spryker\Zed\Sales\Communication\Plugin\SalesOrderSaverPlugin;
use SprykerTest\Shared\Sales\Helper\Config\TesterSalesConfig;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Zed
 * @group Checkout
 * @group Business
 * @group Facade
 * @group CheckoutFacadeTest
 * Add your own group annotations below this line
 */
class CheckoutFacadeTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\Checkout\CheckoutBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->tester->setDependency(CheckoutDependencyProvider::CHECKOUT_PRE_CONDITIONS, [
            new CustomerPreConditionCheckerPlugin(),
            new ProductsAvailableCheckoutPreConditionPlugin(),
        ]);

        $this->tester->setDependency(CheckoutDependencyProvider::CHECKOUT_ORDER_SAVERS, [
            $this->createSalesOrderSaverPlugin(),
            $this->createCustomerOrderSavePlugin(),
        ]);
    }

    /**
     * @return void
     */
    public function testCheckoutSuccessfully()
    {
        $product = $this->tester->haveProduct();
        $this->tester->haveProductInStock([StockProductTransfer::SKU => $product->getSku()]);

        $quoteTransfer = (new QuoteBuilder())
            ->withItem([ItemTransfer::SKU => $product->getSku(), ItemTransfer::UNIT_PRICE => 1])
            ->withStore([StoreTransfer::NAME => 'DE'])
            ->withCustomer()
            ->withTotals()
            ->withCurrency()
            ->withShippingAddress()
            ->withBillingAddress()
            ->build();

        $result = $this->tester->getFacade()->placeOrder($quoteTransfer);

        $this->assertTrue($result->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testCheckoutSuccessfullyWithSplitDelivery()
    {
        $product = $this->tester->haveProduct();
        $this->tester->haveProductInStock([StockProductTransfer::SKU => $product->getSku(), StockProductTransfer::QUANTITY => 3]);
        $email = 'frodo.baggins@gmail.com';
        $itemBuilder = (new ItemBuilder([ItemTransfer::SKU => $product->getSku(), ItemTransfer::UNIT_PRICE => 1]))->withShipment(
            (new ShipmentBuilder())->withShippingAddress([AddressTransfer::EMAIL => $email])
        );

        $quoteTransfer = (new QuoteBuilder())
            ->withItem($itemBuilder)
            ->withStore([StoreTransfer::NAME => 'DE'])
            ->withCustomer([CustomerTransfer::EMAIL => $email])
            ->withTotals()
            ->withCurrency()
            ->withBillingAddress([AddressTransfer::EMAIL => $email])
            ->build();

        $result = $this->checkoutFacade->placeOrder($quoteTransfer);

        $this->assertTrue($result->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testCheckoutResponseContainsErrorIfCustomerAlreadyRegistered()
    {
        $this->tester->haveCustomer([CustomerTransfer::EMAIL => 'max@mustermann.de']);
        $product = $this->tester->haveProduct();
        $this->tester->haveProductInStock([StockProductTransfer::SKU => $product->getSku()]);
        $customer = $this->tester->haveCustomer();

        $quoteTransfer = (new QuoteBuilder([CustomerTransfer::EMAIL => 'max@mustermann.de']))
            ->withItem($this->createItemWithShipment([ItemTransfer::SKU => $product->getSku()], $customer))
            ->withStore([StoreTransfer::NAME => 'DE'])
            ->withCustomer()
            ->withTotals()
            ->withCurrency()
            ->withBillingAddress()
            ->build();

        $result = $this->tester->getFacade()->placeOrder($quoteTransfer);

        $this->assertFalse($result->getIsSuccess());
        $this->assertEquals(1, count($result->getErrors()));
        $this->assertEquals(CheckoutConfig::ERROR_CODE_CUSTOMER_ALREADY_REGISTERED, $result->getErrors()[0]->getErrorCode());
    }

    /**
     * @return void
     */
    public function testCheckoutCreatesOrderItems()
    {
        $product1 = $this->tester->haveProduct();
        $this->tester->haveProductInStock([StockProductTransfer::SKU => $product1->getSku()]);
        $product2 = $this->tester->haveProduct();
        $this->tester->haveProductInStock([StockProductTransfer::SKU => $product2->getSku()]);

        $quoteTransfer = (new QuoteBuilder())
            ->withItem([ItemTransfer::SKU => $product1->getSku(), ItemTransfer::UNIT_PRICE => 1])
            ->withAnotherItem([ItemTransfer::SKU => $product2->getSku(), ItemTransfer::UNIT_PRICE => 1])
            ->withStore([StoreTransfer::NAME => 'DE'])
            ->withCustomer()
            ->withTotals()
            ->withCurrency()
            ->withShippingAddress()
            ->withBillingAddress()
            ->build();

        $result = $this->tester->getFacade()->placeOrder($quoteTransfer);

        $this->assertTrue($result->getIsSuccess());
        $this->assertEquals(0, count($result->getErrors()));

        $salesFacade = $this->tester->getLocator()->sales()->facade();
        $order = $salesFacade->getOrderByIdSalesOrder($result->getSaveOrder()->getIdSalesOrder());
        $this->assertEquals(2, $order->getItems()->count());
        $this->assertEquals($product1->getSku(), $order->getItems()[0]->getSku());
        $this->assertEquals($product2->getSku(), $order->getItems()[1]->getSku());
    }

    /**
     * @return void
     */
    public function testCheckoutCreatesOrderItemsWithSplitDelivery()
    {
        $product1 = $this->tester->haveProduct();
        $this->tester->haveProductInStock([StockProductTransfer::SKU => $product1->getSku()]);
        $product2 = $this->tester->haveProduct();
        $this->tester->haveProductInStock([StockProductTransfer::SKU => $product2->getSku()]);
        $customer = $this->tester->haveCustomer();

        $quoteTransfer = (new QuoteBuilder())
            ->withItem($this->createItemWithShipment([ItemTransfer::SKU => $product1->getSku(), ItemTransfer::UNIT_PRICE => 1], $customer))
            ->withAnotherItem($this->createItemWithShipment([ItemTransfer::SKU => $product2->getSku(), ItemTransfer::UNIT_PRICE => 1], $customer))
            ->withStore([StoreTransfer::NAME => 'DE'])
            ->withCustomer([CustomerTransfer::ID_CUSTOMER => $customer->getIdCustomer()])
            ->withTotals()
            ->withCurrency()
            ->withBillingAddress()
            ->build();

        $result = $this->checkoutFacade->placeOrder($quoteTransfer);

        $this->assertTrue($result->getIsSuccess());
        $this->assertEquals(0, count($result->getErrors()));

        $salesFacade = $this->tester->getLocator()->sales()->facade();
        $order = $salesFacade->getOrderByIdSalesOrder($result->getSaveOrder()->getIdSalesOrder());
        $this->assertEquals(2, $order->getItems()->count());
        $this->assertEquals($product1->getSku(), $order->getItems()[0]->getSku());
        $this->assertEquals($product2->getSku(), $order->getItems()[1]->getSku());
    }

    /**
     * @todo move this code to customer checkout connector, registration can only happen if we have
     * already installed customer bundle
     *
     * @return void
     */
    public function testRegistrationIsTriggeredOnNewNonGuestCustomer()
    {
        $quoteTransfer = $this->getBaseQuoteTransfer();

        $result = $this->tester->getFacade()->placeOrder($quoteTransfer);

        $this->assertTrue($result->getIsSuccess());
        $this->assertEquals(0, count($result->getErrors()));

        $customerQuery = SpyCustomerQuery::create()->filterByEmail($quoteTransfer->getCustomer()->getEmail());
        $this->assertEquals(1, $customerQuery->count());
    }

    /**
     * @todo move this code to customer checkout connector, registration can only happen if we have
     * already installed customer bundle
     *
     * @return void
     */
    public function testRegistrationDoesNotCreateACustomerIfGuest()
    {
        $quoteTransfer = $this->getBaseQuoteTransfer();
        $quoteTransfer->getCustomer()->setIsGuest(true);

        $result = $this->tester->getFacade()->placeOrder($quoteTransfer);

        $this->assertTrue($result->getIsSuccess());
        $this->assertEquals(0, count($result->getErrors()));

        $customerQuery = SpyCustomerQuery::create()->filterByEmail($quoteTransfer->getCustomer()->getEmail());
        $this->assertEquals(0, $customerQuery->count());
    }

    /**
     * @return void
     */
    public function testCheckoutResponseContainsErrorIfStockNotSufficient()
    {
        $quoteTransfer = $this->getBaseQuoteTransfer();
        $productAbstract1 = new SpyProductAbstract();
        $productAbstract1
            ->setSku('AOSB1339')
            ->setAttributes('{}');
        $productConcrete1 = new SpyProduct();
        $productConcrete1
            ->setSku('OSB1339')
            ->setAttributes('{}')
            ->setSpyProductAbstract($productAbstract1)
            ->save();

        $stock = new SpyStock();
        $stock
            ->setName('Stock2');

        $stock1 = new SpyStockProduct();
        $stock1
            ->setQuantity(1)
            ->setStock($stock)
            ->setSpyProduct($productConcrete1)
            ->save();

        $item = new ItemTransfer();
        $item
            ->setSku('OSB1339')
            ->setQuantity(2)
            ->setUnitPrice(3000)
            ->setUnitGrossPrice(3000)
            ->setSumGrossPrice(6000);

        $quoteTransfer->addItem($item);

        $result = $this->tester->getFacade()->placeOrder($quoteTransfer);

        $this->assertFalse($result->getIsSuccess());
        $this->assertEquals(1, count($result->getErrors()));
        $this->assertEquals(CheckoutConfig::ERROR_CODE_PRODUCT_UNAVAILABLE, $result->getErrors()[0]->getErrorCode());
    }

    /**
     * @return void
     */
    public function testCheckoutTriggersStateMachine()
    {
        $quoteTransfer = $this->getBaseQuoteTransfer();

        $this->tester->getFacade()->placeOrder($quoteTransfer);

        $omsConfig = new OmsConfig();

        $orderItem1 = SpySalesOrderItemQuery::create()
            ->filterBySku('OSB1337')
            ->findOne();
        $orderItem2 = SpySalesOrderItemQuery::create()
            ->filterBySku('OSB1338')
            ->findOne();

        $this->assertNotNull($orderItem1);
        $this->assertNotNull($orderItem2);

        $this->assertEquals($omsConfig->getInitialStatus(), $orderItem1->getState()->getName());
        $this->assertEquals($omsConfig->getInitialStatus(), $orderItem2->getState()->getName());
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function getBaseQuoteTransfer(): QuoteTransfer
    {
        $storeTransfer = (new StoreBuilder())->seed([
            StoreTransfer::NAME => 'DE',
        ])->build();
        $currencyTransfer = (new CurrencyBuilder())->seed([
            CurrencyTransfer::CODE => 'EUR',
        ])->build();
        $quoteTransfer = (new QuoteBuilder())->build();
        $quoteTransfer->setStore($storeTransfer);
        $quoteTransfer->setCurrency($currencyTransfer);

        $country = new SpyCountry();
        $country
            ->setIso2Code('xi')
            ->save();

        $productAbstract1 = new SpyProductAbstract();
        $productAbstract1
            ->setSku('AOSB1337')
            ->setAttributes('{}');
        $productConcrete1 = new SpyProduct();
        $productConcrete1
            ->setSku('OSB1337')
            ->setAttributes('{}')
            ->setSpyProductAbstract($productAbstract1)
            ->save();

        $productAbstract2 = new SpyProductAbstract();
        $productAbstract2
            ->setSku('AOSB1338')
            ->setAttributes('{}');
        $productConcrete2 = new SpyProduct();
        $productConcrete2
            ->setSku('OSB1338')
            ->setSpyProductAbstract($productAbstract2)
            ->setAttributes('{}')
            ->save();

        $stock = (new SpyStockQuery())
            ->filterByName('Warehouse1')
            ->findOneOrCreate();

        $stock->save();

        $stock1 = new SpyStockProduct();
        $stock1
            ->setQuantity(3)
            ->setStock($stock)
            ->setSpyProduct($productConcrete1)
            ->save();

        $stock2 = new SpyStockProduct();
        $stock2
            ->setQuantity(3)
            ->setStock($stock)
            ->setSpyProduct($productConcrete2)
            ->save();

        $shippingAddressBuilder = (new AddressBuilder([
            AddressTransfer::ADDRESS2 => '84',
            AddressTransfer::ZIP_CODE => '12346',
            AddressTransfer::EMAIL => 'max@mustermann.de',
            AddressTransfer::CITY => 'Entenhausen2',
        ]));

        $shipmentBuilder = (new ShipmentBuilder())
            ->withShippingAddress($shippingAddressBuilder);

        $item1 = (new ItemBuilder())
            ->seed([
                ItemTransfer::UNIT_PRICE => 4000,
                ItemTransfer::SKU => 'OSB1337',
                ItemTransfer::QUANTITY => 1,
                ItemTransfer::UNIT_GROSS_PRICE => 3000,
                ItemTransfer::SUM_GROSS_PRICE => 3000,
                ItemTransfer::NAME => 'Product1',
            ])
            ->withShipment($shipmentBuilder)
            ->build();

        $item2 = (new ItemBuilder())
            ->seed([
                ItemTransfer::UNIT_PRICE => 4000,
                ItemTransfer::SKU => 'OSB1338',
                ItemTransfer::QUANTITY => 1,
                ItemTransfer::UNIT_GROSS_PRICE => 4000,
                ItemTransfer::SUM_GROSS_PRICE => 4000,
                ItemTransfer::NAME => 'Product2',
            ])
            ->withShipment($shipmentBuilder)
            ->build();

        $quoteTransfer->addItem($item1);
        $quoteTransfer->addItem($item2);

        $totals = (new TotalsBuilder())->seed([
            TotalsTransfer::GRAND_TOTAL => 1000,
            TotalsTransfer::SUBTOTAL => 500,
        ])->build();

        $quoteTransfer->setTotals($totals);

        $billingAddress = (new AddressBuilder())->seed([
            AddressTransfer::ISO2_CODE => 'xi',
            AddressTransfer::EMAIL => 'max@mustermann.de',
        ])->build();

        $quoteTransfer->setBillingAddress($billingAddress);
        $customerTransfer = (new CustomerBuilder())->seed([
            CustomerTransfer::IS_GUEST => false,
            CustomerTransfer::EMAIL => $billingAddress->getEmail(),
        ])->build();

        $quoteTransfer->setCustomer($customerTransfer);

        $paymentTransfer = (new PaymentBuilder())->seed([
            PaymentTransfer::PAYMENT_SELECTION => 'no_payment',
        ])->build();

        $quoteTransfer->setPayment($paymentTransfer);

        return $quoteTransfer;
    }

    /**
     * @return \Spryker\Zed\Sales\Communication\Plugin\SalesOrderSaverPlugin
     */
    protected function createSalesOrderSaverPlugin()
    {
        $salesOrderSaverPlugin = new SalesOrderSaverPlugin();
        $salesOrderSaverPlugin->setFacade($this->createSalesFacadeMock());

        return $salesOrderSaverPlugin;
    }

    /**
     * @return \Spryker\Zed\Customer\Communication\Plugin\OrderCustomerSavePlugin
     */
    protected function createCustomerOrderSavePlugin()
    {
        $container = new Container();
        $customerDependencyProvider = new CustomerDependencyProvider();
        $customerDependencyProvider->provideBusinessLayerDependencies($container);
        $container[CustomerDependencyProvider::FACADE_MAIL] = $this->getMockBuilder(CustomerToMailInterface::class)->getMock();

        $customerFactory = new CustomerBusinessFactory();
        $customerFactory->setContainer($container);

        $customerFacade = new CustomerFacade();
        $customerFacade->setFactory($customerFactory);

        $customerOrderSavePlugin = new OrderCustomerSavePlugin();
        $customerOrderSavePlugin->setFacade($customerFacade);

        return $customerOrderSavePlugin;
    }

    /**
     * @param string $testStateMachineProcessName
     *
     * @return \Spryker\Zed\Sales\Business\SalesFacade
     */
    protected function createSalesFacadeMock($testStateMachineProcessName = 'Test01')
    {
        $this->tester->configureTestStateMachine([$testStateMachineProcessName]);

        $salesConfig = new TesterSalesConfig();
        $salesConfig->setStateMachineProcessName($testStateMachineProcessName);

        $salesBusinessFactory = new SalesBusinessFactory();
        $salesBusinessFactory->setConfig($salesConfig);

        $salesFacade = new SalesFacade();
        $salesFacade->setFactory($salesBusinessFactory);

        return $salesFacade;
    }

    /**
     * @return \Generated\Shared\Transfer\AddressTransfer
     */
    protected function createAddressTransfer(): AddressTransfer
    {
        return (new AddressBuilder())->build();
    }

    /**
     * @param array $seed
     * @param \Generated\Shared\Transfer\CustomerTransfer|null $customer
     *
     * @return \Generated\Shared\DataBuilder\ItemBuilder
     */
    protected function createItemWithShipment(array $seed, ?CustomerTransfer $customer = null)
    {
        $address = (new AddressBuilder([AddressTransfer::EMAIL => $customer->getEmail()]));

        return (new ItemBuilder($seed))->withShipment(
            (new ShipmentBuilder())->withShippingAddress($address)
        );
    }
}
