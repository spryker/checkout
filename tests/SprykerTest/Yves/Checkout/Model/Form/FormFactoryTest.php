<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Yves\Checkout\Model\Form;

use PHPUnit_Framework_TestCase;
use Spryker\Yves\Checkout\CheckoutDependencyProvider;
use Spryker\Yves\Checkout\Form\FormFactory;
use Spryker\Yves\Kernel\Container;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Yves
 * @group Checkout
 * @group Model
 * @group Form
 * @group FormFactoryTest
 * Add your own group annotations below this line
 */
class FormFactoryTest extends PHPUnit_Framework_TestCase
{

    const SUB_FORMS = 'forms';

    /**
     * @return void
     */
    public function testCreatePaymentMethodSubForms()
    {
        $container = new Container();
        $container[CheckoutDependencyProvider::PAYMENT_SUB_FORMS] = self::SUB_FORMS;

        $formFactory = new FormFactory();
        $formFactory->setContainer($container);

        $this->assertSame(self::SUB_FORMS, $formFactory->createPaymentMethodSubForms());
    }

}
