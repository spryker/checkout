<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Yves\Checkout\Model\Process\Steps;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Spryker\Client\Checkout\CheckoutClientInterface;
use Spryker\Yves\Checkout\Process\Steps\AbstractPlaceOrderStep;
use SprykerTest\Yves\Checkout\TestableAbstractPlaceOrderStep;
use Symfony\Component\HttpFoundation\Request;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Yves
 * @group Checkout
 * @group Model
 * @group Process
 * @group Steps
 * @group AbstractPlaceOrderStepTest
 * Add your own group annotations below this line
 */
class AbstractPlaceOrderStepTest extends Unit
{
    /**
     * @var string
     */
    public const ORDER_REFERENCE = 'order reference';

    /**
     * @var string
     */
    public const ESCAPE_ROUTE = 'escapeRoute';

    /**
     * @var string
     */
    public const STEP_ROUTE = 'stepRoute';

    /**
     * @var int
     */
    public const ERROR_CODE_123 = 123;

    /**
     * @var string
     */
    public const ESCAPE_ROUTE_123 = 'escapeRoute123';

    /**
     * @var string
     */
    public const EXTERNAL_REDIRECT_URL = 'externalRedirectUrl';

    public function testRequireInputReturnFalse(): void
    {
        $checkoutClientMock = $this->getCheckoutClientMock();
        $abstractPlaceOrderStepMock = $this->getAbstractPlaceOrderStep($checkoutClientMock);

        $this->assertFalse($abstractPlaceOrderStepMock->requireInput(new QuoteTransfer()));
    }

    public function testExecuteShouldSetExternalRedirectUrlIfResponseContainsOne(): void
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $checkoutResponseTransfer->setIsExternalRedirect(true);
        $checkoutResponseTransfer->setRedirectUrl(static::EXTERNAL_REDIRECT_URL);

        $checkoutClientMock = $this->getCheckoutClientMock();
        $checkoutClientMock->method('placeOrder')->willReturn($checkoutResponseTransfer);
        $abstractPlaceOrderStepMock = $this->getAbstractPlaceOrderStep($checkoutClientMock);

        $abstractPlaceOrderStepMock->execute($this->getRequest(), new QuoteTransfer());
        $this->assertSame(static::EXTERNAL_REDIRECT_URL, $abstractPlaceOrderStepMock->getExternalRedirectUrl());
    }

    public function testExecuteShouldSetOrderReferenceIfResponseContainsOne(): void
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $saveOrderTransfer = new SaveOrderTransfer();
        $saveOrderTransfer->setOrderReference(static::ORDER_REFERENCE);
        $checkoutResponseTransfer->setSaveOrder($saveOrderTransfer);

        $checkoutClientMock = $this->getCheckoutClientMock();
        $checkoutClientMock->method('placeOrder')->willReturn($checkoutResponseTransfer);
        $abstractPlaceOrderStepMock = $this->getAbstractPlaceOrderStep($checkoutClientMock);

        $quoteTransfer = new QuoteTransfer();
        $abstractPlaceOrderStepMock->execute($this->getRequest(), $quoteTransfer);
        $this->assertSame(static::ORDER_REFERENCE, $quoteTransfer->getOrderReference());
    }

    public function testPostConditionReturnTrueWhenOrderReferenceGivenAndResponseIsSuccessful(): void
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $checkoutResponseTransfer->setIsSuccess(true);

        $checkoutClientMock = $this->getCheckoutClientMock();
        $checkoutClientMock->method('placeOrder')->willReturn($checkoutResponseTransfer);

        $abstractPlaceOrderStepMock = $this->getAbstractPlaceOrderStep($checkoutClientMock);

        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer->setOrderReference(static::ORDER_REFERENCE);
        $abstractPlaceOrderStepMock->execute($this->getRequest(), $quoteTransfer);

        $this->assertTrue($abstractPlaceOrderStepMock->postCondition($quoteTransfer));
    }

    public function testPostConditionReturnFalseWhenNoOrderReferenceGiven(): void
    {
        $abstractPlaceOrderStepMock = $this->getAbstractPlaceOrderStep(
            $this->getCheckoutClientMock(),
        );

        $this->assertFalse($abstractPlaceOrderStepMock->postCondition(new QuoteTransfer()));
    }

    public function testPostConditionReturnFalseWhenOrderReferenceGivenAndResponseIsNotSuccessful(): void
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $checkoutResponseTransfer->setIsSuccess(false);

        $checkoutClientMock = $this->getCheckoutClientMock();
        $checkoutClientMock->method('placeOrder')->willReturn($checkoutResponseTransfer);

        $abstractPlaceOrderStepMock = $this->getAbstractPlaceOrderStep($checkoutClientMock);

        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer->setOrderReference(static::ORDER_REFERENCE);
        $abstractPlaceOrderStepMock->execute($this->getRequest(), $quoteTransfer);

        $this->assertFalse($abstractPlaceOrderStepMock->postCondition($quoteTransfer));
    }

    public function testPostConditionDoesNotChangeEscapeRouteIfResponseFalseAndNoErrorCodeMatches(): void
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $checkoutResponseTransfer->setIsSuccess(false);

        $checkoutClientMock = $this->getCheckoutClientMock();
        $checkoutClientMock->method('placeOrder')->willReturn($checkoutResponseTransfer);

        $abstractPlaceOrderStepMock = $this->getAbstractPlaceOrderStep($checkoutClientMock);

        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer->setOrderReference(static::ORDER_REFERENCE);
        $abstractPlaceOrderStepMock->execute($this->getRequest(), $quoteTransfer);

        $this->assertFalse($abstractPlaceOrderStepMock->postCondition($quoteTransfer));

        $this->assertSame(static::ESCAPE_ROUTE, $abstractPlaceOrderStepMock->getEscapeRoute());
    }

    public function testPostConditionChangeErrorRouteIfResponseFalseAndErrorCodeMatches(): void
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $checkoutResponseTransfer->setIsSuccess(false);
        $checkoutErrorTransfer = new CheckoutErrorTransfer();
        $checkoutErrorTransfer->setErrorCode(static::ERROR_CODE_123);
        $checkoutResponseTransfer->addError($checkoutErrorTransfer);

        $checkoutClientMock = $this->getCheckoutClientMock();
        $checkoutClientMock->method('placeOrder')->willReturn($checkoutResponseTransfer);

        $abstractPlaceOrderStepMock = $this->getAbstractPlaceOrderStep($checkoutClientMock);

        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer->setOrderReference(static::ORDER_REFERENCE);
        $abstractPlaceOrderStepMock->execute($this->getRequest(), $quoteTransfer);

        $this->assertFalse($abstractPlaceOrderStepMock->postCondition($quoteTransfer));

        $this->assertSame(static::ESCAPE_ROUTE_123, $abstractPlaceOrderStepMock->getPostConditionErrorRoute());
    }

    /**
     * @param \Spryker\Client\Checkout\CheckoutClientInterface $checkoutClient
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Yves\Checkout\Process\Steps\AbstractPlaceOrderStep
     */
    protected function getAbstractPlaceOrderStep(CheckoutClientInterface $checkoutClient): AbstractPlaceOrderStep
    {
        $errorCodeToEscapeRouteMatching = [
            static::ERROR_CODE_123 => static::ESCAPE_ROUTE_123,
        ];
        // Create partial mock that only overrides setCheckoutErrorMessages so execute() uses real implementation
        $abstractPlaceOrderStepMock = $this->getMockBuilder(TestableAbstractPlaceOrderStep::class)
            ->setConstructorArgs([$checkoutClient, static::STEP_ROUTE, static::ESCAPE_ROUTE, $errorCodeToEscapeRouteMatching])
            ->onlyMethods(['setCheckoutErrorMessages'])
            ->getMock();

        return $abstractPlaceOrderStepMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\Checkout\CheckoutClientInterface
     */
    private function getCheckoutClientMock(): CheckoutClientInterface
    {
        return $this->getMockBuilder(CheckoutClientInterface::class)->getMock();
    }

    protected function getRequest(): Request
    {
        return Request::create('foo');
    }
}
