<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Checkout\Quote;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\QuoteValidationResponseTransfer;

interface QuoteProceedCheckoutCheckerInterface
{
    public function isQuoteApplicableForCheckout(QuoteTransfer $quoteTransfer): QuoteValidationResponseTransfer;
}
