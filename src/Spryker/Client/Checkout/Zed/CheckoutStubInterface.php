<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Checkout\Zed;

use Generated\Shared\Transfer\QuoteTransfer;

interface CheckoutStubInterface
{

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\CheckoutResponseTransfer|\Spryker\Shared\Kernel\Transfer\TransferInterface
     */
    public function placeOrder(QuoteTransfer $quoteTransfer);

}
