<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Checkout\Form\Provider;

use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Yves\StepEngine\Dependency\Form\StepEngineFormDataProviderInterface;
use Spryker\Yves\StepEngine\Dependency\Form\SubFormInterface;
use Spryker\Yves\StepEngine\Dependency\Plugin\Form\SubFormPluginCollection;

class SubFormDataProviders implements StepEngineFormDataProviderInterface
{
    /**
     * @var \Spryker\Yves\StepEngine\Dependency\Plugin\Form\SubFormPluginCollection
     */
    protected $subFormPlugins;

    /**
     * @param \Spryker\Yves\StepEngine\Dependency\Plugin\Form\SubFormPluginCollection $subFormPlugins
     */
    public function __construct(SubFormPluginCollection $subFormPlugins)
    {
        $this->subFormPlugins = $subFormPlugins;
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $quoteTransfer
     *
     * @return \Spryker\Shared\Kernel\Transfer\AbstractTransfer
     */
    public function getData(AbstractTransfer $quoteTransfer)
    {
        foreach ($this->subFormPlugins as $subForm) {
            $quoteTransfer = $subForm->createSubFormDataProvider()->getData($quoteTransfer);
        }

        return $quoteTransfer;
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $quoteTransfer
     *
     * @return array
     */
    public function getOptions(AbstractTransfer $quoteTransfer)
    {
        $options = [];
        foreach ($this->subFormPlugins as $subForm) {
            $options = array_merge(
                $options,
                $subForm->createSubFormDataProvider()->getOptions($quoteTransfer)
            );
        }

        return [
            SubFormInterface::OPTIONS_FIELD_NAME => $options,
        ];
    }
}