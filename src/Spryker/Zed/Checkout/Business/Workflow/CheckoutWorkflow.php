<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Checkout\Business\Workflow;

use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Propel\Runtime\Propel;
use Spryker\Zed\Checkout\Dependency\Facade\CheckoutToOmsInterface;

class CheckoutWorkflow implements CheckoutWorkflowInterface
{

    /**
     * @var \Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPreConditionInterface[]
     */
    protected $preConditionStack;

    /**
     * @var \Spryker\Zed\Checkout\Dependency\Plugin\CheckoutSaveOrderInterface[]
     */
    protected $saveOrderStack;

    /**
     * @var \Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPostSaveHookInterface[]
     */
    protected $postSaveHookStack;

    /**
     * @var \Spryker\Zed\Checkout\Dependency\Facade\CheckoutToOmsInterface
     */
    protected $omsFacade;

    /**
     * @param \Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPreConditionInterface[] $preConditionStack
     * @param \Spryker\Zed\Checkout\Dependency\Plugin\CheckoutSaveOrderInterface[] $saveOrderStack
     * @param \Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPostSaveHookInterface[] $postSaveHookStack
     * @param \Spryker\Zed\Checkout\Dependency\Facade\CheckoutToOmsInterface $omsFacade
     */
    public function __construct(
        array $preConditionStack,
        array $saveOrderStack,
        array $postSaveHookStack,
        CheckoutToOmsInterface $omsFacade
    ) {
        $this->preConditionStack = $preConditionStack;
        $this->postSaveHookStack = $postSaveHookStack;
        $this->saveOrderStack = $saveOrderStack;
        $this->omsFacade = $omsFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer
     */
    public function placeOrder(QuoteTransfer $quoteTransfer)
    {
        $checkoutResponse = $this->createCheckoutResponseTransfer();
        $checkoutResponse->setIsSuccess(false);

        $this->checkPreConditions($quoteTransfer, $checkoutResponse);

        if (!$this->hasErrors($checkoutResponse)) {
            $quoteTransfer = $this->doSaveOrder($quoteTransfer, $checkoutResponse);
            if (!$this->hasErrors($checkoutResponse)) {
                $this->triggerStateMachine($checkoutResponse);
                $this->executePostHooks($quoteTransfer, $checkoutResponse);

                $isSuccess = !$this->hasErrors($checkoutResponse);
                $checkoutResponse->setIsSuccess($isSuccess);
            }
        }

        return $checkoutResponse;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponse
     *
     * @return void
     */
    protected function checkPreConditions(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponse)
    {
        foreach ($this->preConditionStack as $preCondition) {
            $preCondition->checkCondition($quoteTransfer, $checkoutResponse);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponse
     *
     * @return bool
     */
    protected function hasErrors(CheckoutResponseTransfer $checkoutResponse)
    {
        return count($checkoutResponse->getErrors()) > 0;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponse
     *
     * @throws \Exception
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function doSaveOrder(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponse)
    {
        Propel::getConnection()->beginTransaction();

        foreach ($this->saveOrderStack as $orderSaver) {
            $orderSaver->saveOrder($quoteTransfer, $checkoutResponse);
        }

        if ($this->hasErrors($checkoutResponse)) {
            Propel::getConnection()->rollBack();
            return $quoteTransfer;
        }

        Propel::getConnection()->commit();

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return void
     */
    protected function triggerStateMachine(CheckoutResponseTransfer $checkoutResponseTransfer)
    {
        $salesOrderItemIds = [];

        foreach ($checkoutResponseTransfer->getSaveOrder()->getOrderItems() as $item) {
            $salesOrderItemIds[] = $item->getIdSalesOrderItem();
        }

        $this->omsFacade->triggerEventForNewOrderItems($salesOrderItemIds);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponse
     *
     * @return void
     */
    protected function executePostHooks(QuoteTransfer $quoteTransfer, $checkoutResponse)
    {
        foreach ($this->postSaveHookStack as $postSaveHook) {
            $postSaveHook->executeHook($quoteTransfer, $checkoutResponse);
        }
    }

    /**
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer
     */
    protected function createCheckoutResponseTransfer()
    {
        $checkoutResponseTransfer = new CheckoutResponseTransfer();
        $checkoutResponseTransfer->setSaveOrder(new SaveOrderTransfer());

        return $checkoutResponseTransfer;
    }

}
