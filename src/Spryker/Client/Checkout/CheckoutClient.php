<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Checkout;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\Kernel\AbstractClient;

/**
 * @method \Spryker\Client\Checkout\CheckoutFactory getFactory()
 */
class CheckoutClient extends AbstractClient implements CheckoutClientInterface
{
    /**
     * Places the order
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer
     */
    public function placeOrder(QuoteTransfer $quoteTransfer)
    {
        return $this->getZedStub()->placeOrder($quoteTransfer);
    }

    /**
     * @return \Spryker\Client\Checkout\Zed\CheckoutStub
     */
    protected function getZedStub()
    {
        return $this->getFactory()->createZedStub();
    }
}
