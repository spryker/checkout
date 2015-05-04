<?php
namespace SprykerFeature\Zed\Checkout\Business\Model\Workflow\Task\Propel;

use Generated\Shared\Transfer\SalesOrderTransfer;
use SprykerFeature\Zed\Checkout\Business\Model\Workflow\Context;
use SprykerFeature\Zed\Checkout\Business\Model\Workflow\Task\AbstractTask;

/**
 * Class CommitTransaction
 * @package SprykerFeature\Zed\Checkout\Business\Model\Workflow\Task\Propel
 */
class CommitTransaction extends AbstractTask
{
    /**
     * @param Order   $transferOrder
     * @param Context $context
     * @param array   $logContext
     */
    public function __invoke(Order $transferOrder, Context $context, array $logContext)
    {
        $connection = \Propel\Runtime\Propel::getConnection();
        $connection->commit();
    }
}
