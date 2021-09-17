<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Checkout\Quote;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\QuoteValidationResponseTransfer;

class QuoteProceedCheckoutChecker implements QuoteProceedCheckoutCheckerInterface
{
    /**
     * @var array<\Spryker\Client\CheckoutExtension\Dependency\Plugin\CheckoutPreCheckPluginInterface>
     */
    protected $quoteProceedCheckoutCheckPlugins;

    /**
     * @param array<\Spryker\Client\CheckoutExtension\Dependency\Plugin\CheckoutPreCheckPluginInterface> $quoteProccedCheckoutCheckPlugins
     */
    public function __construct(array $quoteProccedCheckoutCheckPlugins)
    {
        $this->quoteProceedCheckoutCheckPlugins = $quoteProccedCheckoutCheckPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteValidationResponseTransfer
     */
    public function isQuoteApplicableForCheckout(QuoteTransfer $quoteTransfer): QuoteValidationResponseTransfer
    {
        foreach ($this->quoteProceedCheckoutCheckPlugins as $quoteProccedCheckoutCheckPlugin) {
            $quoteValidationResponseTransfer = $quoteProccedCheckoutCheckPlugin->isValid($quoteTransfer);

            if (!$quoteValidationResponseTransfer->getIsSuccessful()) {
                return $quoteValidationResponseTransfer;
            }
        }

        return (new QuoteValidationResponseTransfer())
            ->setIsSuccessful(true);
    }
}
