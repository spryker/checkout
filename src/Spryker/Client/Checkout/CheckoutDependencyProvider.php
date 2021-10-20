<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Checkout;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;

class CheckoutDependencyProvider extends AbstractDependencyProvider
{
    /**
     * @var string
     */
    public const SERVICE_ZED = 'zed service';

    /**
     * @var string
     */
    public const PLUGINS_CHECKOUT_PRE_CHECK = 'PLUGINS_CHECKOUT_PRE_CHECK';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container)
    {
        $container->set(static::SERVICE_ZED, function (Container $container) {
            return $container->getLocator()->zedRequest()->client();
        });

        $container = $this->addCheckoutPreCheckPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addCheckoutPreCheckPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CHECKOUT_PRE_CHECK, function () {
            return $this->getCheckoutPreCheckPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Client\CheckoutExtension\Dependency\Plugin\CheckoutPreCheckPluginInterface>
     */
    protected function getCheckoutPreCheckPlugins(): array
    {
        return [];
    }
}
