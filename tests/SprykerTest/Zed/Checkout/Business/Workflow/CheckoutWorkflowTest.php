<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Checkout\Business\Workflow;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Spryker\Zed\Checkout\Business\Workflow\CheckoutWorkflow;
use Spryker\Zed\Checkout\Dependency\Facade\CheckoutToOmsFacadeBridge;
use Spryker\Zed\Checkout\Dependency\Plugin\CheckoutDoSaveOrderInterface;
use Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPostSaveHookInterface;
use Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPreConditionInterface;
use Spryker\Zed\Checkout\Dependency\Plugin\CheckoutSaveOrderInterface;
use Spryker\Zed\CheckoutExtension\Dependency\Plugin\CheckoutPreConditionPluginInterface;
use Spryker\Zed\Oms\Business\OmsFacade;
use SprykerTest\Zed\Checkout\Business\Fixture\MockPostHook;
use SprykerTest\Zed\Checkout\Business\Fixture\ResponseManipulatorPreCondition;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Checkout
 * @group Business
 * @group Workflow
 * @group CheckoutWorkflowTest
 * Add your own group annotations below this line
 */
class CheckoutWorkflowTest extends Unit
{
    /**
     * @return void
     */
    public function testIsPlaceableOrderResponseIsSuccessful(): void
    {
        // Arrange
        $checkoutResponse = $this->createBaseCheckoutResponse();
        $checkoutWorkflow = new CheckoutWorkflow(
            new CheckoutToOmsFacadeBridge(new OmsFacade()),
            [],
            [],
            [new MockPostHook($checkoutResponse)],
        );
        $quoteTransfer = new QuoteTransfer();

        // Act
        $checkoutResponseTransfer = $checkoutWorkflow->isPlaceableOrder($quoteTransfer);

        // Assert
        $this->assertTrue($checkoutResponseTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testIsPlaceableOrderResponseIsNotSuccessful(): void
    {
        // Arrange
        $mockBuilder = $this->getMockBuilder(CheckoutPreConditionPluginInterface::class)->getMock();

        $mockBuilder->expects($this->once())->method('checkCondition')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(CheckoutResponseTransfer::class),
        );

        $checkoutResponse = $this->createBaseCheckoutResponse();
        $checkoutWorkflow = new CheckoutWorkflow(
            new CheckoutToOmsFacadeBridge(new OmsFacade()),
            [$mockBuilder],
            [],
            [new MockPostHook($checkoutResponse)],
        );
        $quoteTransfer = new QuoteTransfer();

        // Act
        $checkoutResponseTransfer = $checkoutWorkflow->isPlaceableOrder($quoteTransfer);

        // Assert
        $this->assertFalse($checkoutResponseTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testWorkflowCallsAllPreConditions(): void
    {
        $mock1 = $this->getMockBuilder(CheckoutPreConditionInterface::class)->getMock();
        $mock2 = $this->getMockBuilder(CheckoutPreConditionInterface::class)->getMock();

        $mock1->expects($this->once())->method('checkCondition')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(CheckoutResponseTransfer::class),
        );

        $mock2->expects($this->once())->method('checkCondition')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(CheckoutResponseTransfer::class),
        );

        $checkoutWorkflow = new CheckoutWorkflow(
            new CheckoutToOmsFacadeBridge(new OmsFacade()),
            [$mock1, $mock2],
            [],
            [],
        );

        $quoteTransfer = new QuoteTransfer();

        $checkoutWorkflow->placeOrder($quoteTransfer);
    }

    /**
     * @return void
     */
    public function testWorkflowCallsAllOrderSavers(): void
    {
        $mock1 = $this->getMockBuilder(CheckoutDoSaveOrderInterface::class)->getMock();
        $mock2 = $this->getMockBuilder(CheckoutDoSaveOrderInterface::class)->getMock();

        $quoteTransfer = new QuoteTransfer();

        $mock1->expects($this->once())->method('saveOrder')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(SaveOrderTransfer::class),
        );

        $mock2->expects($this->once())->method('saveOrder')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(SaveOrderTransfer::class),
        );

        $checkoutWorkflow = new CheckoutWorkflow(
            new CheckoutToOmsFacadeBridge(new OmsFacade()),
            [],
            [$mock1, $mock2],
            [],
            [],
        );
        $checkoutWorkflow->placeOrder($quoteTransfer);
    }

    /**
     * @return void
     */
    public function testWorkflowCallsAllDeprecatedOrderSavers(): void
    {
        $mock1 = $this->getMockBuilder(CheckoutDoSaveOrderInterface::class)->getMock();
        $mock2 = $this->getMockBuilder(CheckoutSaveOrderInterface::class)->getMock();
        $mock3 = $this->getMockBuilder(CheckoutSaveOrderInterface::class)->getMock();

        $quoteTransfer = new QuoteTransfer();

        $mock1->expects($this->once())->method('saveOrder')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(SaveOrderTransfer::class),
        );

        $mock2->expects($this->once())->method('saveOrder')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(CheckoutResponseTransfer::class),
        );

        $mock3->expects($this->once())->method('saveOrder')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(CheckoutResponseTransfer::class),
        );

        $checkoutWorkflow = new CheckoutWorkflow(
            new CheckoutToOmsFacadeBridge(new OmsFacade()),
            [],
            [$mock1, $mock2, $mock3],
            [],
            [],
        );

        $checkoutWorkflow->placeOrder($quoteTransfer);
    }

    /**
     * @return void
     */
    public function testWorkflowCallsAllPostHooks(): void
    {
        $mock1 = $this->getMockBuilder(CheckoutPostSaveHookInterface::class)->getMock();
        $mock2 = $this->getMockBuilder(CheckoutPostSaveHookInterface::class)->getMock();

        $quoteTransfer = new QuoteTransfer();

        $mock1->expects($this->once())->method('executeHook')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(CheckoutResponseTransfer::class),
        );

        $mock2->expects($this->once())->method('executeHook')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(CheckoutResponseTransfer::class),
        );

        $checkoutWorkflow = new CheckoutWorkflow(
            new CheckoutToOmsFacadeBridge(new OmsFacade()),
            [],
            [],
            [$mock1, $mock2],
        );
        $checkoutWorkflow->placeOrder($quoteTransfer);
    }

    /**
     * @return void
     */
    public function testWorkflowPassesResponseOn(): void
    {
        $checkoutResponse = $this->createBaseCheckoutResponse();
        $checkoutResponse
            ->setIsExternalRedirect(true)
            ->setRedirectUrl('anUrl');

        $mock1 = new ResponseManipulatorPreCondition($checkoutResponse);
        $mock2 = $this->getMockBuilder(CheckoutDoSaveOrderInterface::class)->getMock();

        $quoteTransfer = new QuoteTransfer();

        $checkoutWorkflow = new CheckoutWorkflow(
            new CheckoutToOmsFacadeBridge(new OmsFacade()),
            [$mock1],
            [$mock2],
            [],
        );

        $mock2->expects($this->once())->method('saveOrder')->with(
            $this->isInstanceOf(QuoteTransfer::class),
            $this->isInstanceOf(SaveOrderTransfer::class),
        );

        $checkoutWorkflow->placeOrder($quoteTransfer);
    }

    /**
     * @return void
     */
    public function testPostHookResultIsReturned(): void
    {
        $checkoutResponse = $this->createBaseCheckoutResponse();

        $checkoutResponse
            ->setIsSuccess(true);

        $mock = new MockPostHook($checkoutResponse);

        $checkoutWorkflow = new CheckoutWorkflow(
            new CheckoutToOmsFacadeBridge(new OmsFacade()),
            [],
            [],
            [$mock],
        );
        $quoteTransfer = new QuoteTransfer();

        $result = $checkoutWorkflow->placeOrder($quoteTransfer);

        $this->assertTrue($result->getIsSuccess());
    }

    /**
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer
     */
    protected function createBaseCheckoutResponse(): CheckoutResponseTransfer
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();

        $checkoutResponseTransfer->setSaveOrder(new SaveOrderTransfer());

        return $checkoutResponseTransfer;
    }
}
