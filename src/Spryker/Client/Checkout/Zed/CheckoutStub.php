<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Checkout\Zed;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\ZedRequest\ZedRequestClient;

class CheckoutStub implements CheckoutStubInterface
{
    /**
     * @var \Spryker\Client\ZedRequest\Client\ZedClient
     */
    protected $zedStub;

    /**
     * @param \Spryker\Client\ZedRequest\ZedRequestClient $zedStub
     */
    public function __construct(ZedRequestClient $zedStub)
    {
        $this->zedStub = $zedStub;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer|\Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    public function placeOrder(QuoteTransfer $quoteTransfer)
    {
        return $this->zedStub->call('/checkout/gateway/place-order', $quoteTransfer);
    }
}
