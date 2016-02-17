<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Checkout;

use Spryker\Zed\Checkout\Dependency\Facade\CheckoutToOmsBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPostSaveHookInterface;
use Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPreConditionInterface;
use Spryker\Zed\Checkout\Dependency\Plugin\CheckoutSaveOrderInterface;

class CheckoutDependencyProvider extends AbstractBundleDependencyProvider
{

    const CHECKOUT_PRE_CONDITIONS = 'checkout_pre_conditions';
    const CHECKOUT_POST_HOOKS = 'checkout_post_hooks';
    const CHECKOUT_ORDER_SAVERS = 'checkout_order_savers';

    const FACADE_OMS = 'oms facade';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container[self::CHECKOUT_PRE_CONDITIONS] = function (Container $container) {
            return $this->getCheckoutPreConditions($container);
        };

        $container[self::CHECKOUT_ORDER_SAVERS] = function (Container $container) {
            return $this->getCheckoutOrderSavers($container);
        };

        $container[self::CHECKOUT_POST_HOOKS] = function (Container $container) {
            return $this->getCheckoutPostHooks($container);
        };

        $container[self::FACADE_OMS] = function (Container $container) {
            return new CheckoutToOmsBridge($container->getLocator()->oms()->facade());
        };

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPreConditionInterface[]
     */
    protected function getCheckoutPreConditions(Container $container)
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Checkout\Dependency\Plugin\CheckoutSaveOrderInterface[]
     */
    protected function getCheckoutOrderSavers(Container $container)
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Checkout\Dependency\Plugin\CheckoutPostSaveHookInterface[]
     */
    protected function getCheckoutPostHooks(Container $container)
    {
        return [];
    }

}
