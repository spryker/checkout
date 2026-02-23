<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Yves\Checkout;

use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Yves\Checkout\Process\Steps\AbstractPlaceOrderStep;

class TestableAbstractPlaceOrderStep extends AbstractPlaceOrderStep
{
    /**
     * Implement abstract method with a no-op for testing
     *
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return void
     */
    protected function setCheckoutErrorMessages(CheckoutResponseTransfer $checkoutResponseTransfer)
    {
        // no-op for tests
    }

    /**
     * Implement preCondition required by StepInterface for concrete class
     *
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $dataTransfer
     *
     * @return bool
     */
    public function preCondition(AbstractTransfer $dataTransfer)
    {
        // Default to true so tests can focus on execute/postCondition behavior
        return true;
    }
}
