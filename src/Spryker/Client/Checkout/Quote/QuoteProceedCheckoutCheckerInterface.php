<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Checkout\Quote;

use Generated\Shared\Transfer\CanProceedCheckoutResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;

interface QuoteProceedCheckoutCheckerInterface
{
    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\CanProceedCheckoutResponseTransfer
     */
    public function isQuoteApplicableForCheckout(QuoteTransfer $quoteTransfer): CanProceedCheckoutResponseTransfer;
}