<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Functional\Spryker\Zed\Checkout\Dependency;

use Generated\Shared\Transfer\CheckoutRequestTransfer;
use Generated\Shared\Transfer\OrderTransfer;
use Spryker\Zed\Checkout\Dependency\Plugin\CheckoutOrderHydrationInterface;

class MockOmsOrderHydrator implements CheckoutOrderHydrationInterface
{

    /**
     * @param \Generated\Shared\Transfer\OrderTransfer $orderTransfer
     * @param \Generated\Shared\Transfer\CheckoutRequestTransfer $checkoutRequest
     *
     * @return void
     */
    public function hydrateOrder(OrderTransfer $orderTransfer, CheckoutRequestTransfer $checkoutRequest)
    {
        $orderTransfer->setProcess('Prepayment01');
    }

}
