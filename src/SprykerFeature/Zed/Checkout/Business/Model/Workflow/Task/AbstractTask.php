<?php

namespace SprykerFeature\Zed\Checkout\Business\Model\Workflow\Task;

use SprykerFeature\Shared\Sales\Transfer\Order;
use SprykerFeature\Zed\Checkout\Business\Model\Workflow\Context;
use SprykerFeature\Zed\Library\Workflow\Task;

abstract class AbstractTask extends Task
{
    /**
     * @param Order   $transferOrder
     * @param Context $context
     * @param array   $logContext
     */
    abstract public function __invoke(Order $transferOrder, Context $context, array $logContext);
}
