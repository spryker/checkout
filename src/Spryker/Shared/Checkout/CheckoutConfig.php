<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Checkout;

use Spryker\Shared\Kernel\AbstractBundleConfig;

class CheckoutConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Shipment expense type name.
     *
     * @api
     *
     * @see \Spryker\Shared\Shipment\ShipmentConfig::SHIPMENT_EXPENSE_TYPE.
     *
     * @var string
     */
    public const SHIPMENT_EXPENSE_TYPE = 'SHIPMENT_EXPENSE_TYPE';
}
