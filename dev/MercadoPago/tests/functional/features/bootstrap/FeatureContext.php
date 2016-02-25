<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Bex\Behat\Magento2InitExtension\Fixtures\BaseMinkFixture;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Bex\Behat\Magento2InitExtension\Fixtures\MagentoConfigManager;

/**
 * Behat test suite context.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FeatureContext
    extends MercadoPagoFixture
    implements Context, SnippetAcceptingContext
{

    protected $_configManager;

    protected function getConfigManager()
    {
        if (empty($this->_configManager)) {
            $this->_configManager = new MagentoConfigManager();
        }

        return $this->_configManager;
    }

    /**
     * @param $cssClass
     *
     * @return \Behat\Mink\Element\NodeElement|mixed|null
     * @throws ElementNotFoundException
     */
    public function findElement($cssClass)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', $cssClass);
        if (null === $element) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'Element', 'css', $cssClass);
        }

        return $element;
    }

    /**
     * @Then i revert configs
     */
    public function iRevertConfigs()
    {
        $this->getConfigManager()->revertAllConfig();
    }

    /*********************************************************FEATURE FUNCTIONS**************************************/

    /**
     * @Given User :arg1 :arg2 exists
     */
    public function userExists($arg1, $arg2)
    {
        $customer = $this->getMagentoObject('Magento\Customer\Model\Customer');
        $storeManager = $this->getMagentoObject(Magento\Store\Model\StoreManagerInterface::class);
        $store = $storeManager->getWebsite()->getDefaultStore();
        $websiteId = $store->getWebsiteId();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($arg1);

        if (!$customer->getId()) {
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname('John')
                ->setLastname('Doe')
                ->setEmail($arg1)
                ->setPassword($arg2);

            $customer->save();
        }

    }

    /**
     * @Given I am logged in as :arg1 :arg2
     */
    public function iAmLoggedInAs($arg1, $arg2)
    {
        $session = $this->getSession();
        $session->visit($this->locatePath('customer/account/login'));

        $login = $session->getPage()->find('css', '#email');
        $pwd = $session->getPage()->find('css', '#pass');
        $submit = $session->getPage()->find('css', '#send2');
        if ($login && $pwd && $submit) {
            $email = $arg1;
            $password = $arg2;
            $login->setValue($email);
            $pwd->setValue($password);
            $submit->click();
            $this->findElement('div .welcome');
        }
    }

    /**
     * @Given I empty cart
     */
    public function iEmptyCart()
    {
        $this->iAmOnPage('checkout/cart/');
        $removeButton = $this->getSession()->getPage()->find('css', '.action-delete');
        if ($removeButton) {
            $removeButton->press();
            $this->iEmptyCart();
        }
    }

    /**
     * @When I am on page :arg1
     */
    public function iAmOnPage($arg1)
    {
        $this->getSession()->visit($this->locatePath($arg1));
    }

    /**
     * @Given I press :cssClass element
     */
    public function iPressElement($cssClass)
    {
        $this->getSession()->wait(10000);
        $button = $this->findElement($cssClass);
        $button->press();
    }

    /**
     * @When I select shipping method :arg1
     */
    public function iSelectShippingMethod($method)
    {
        $page = $this->getSession()->getPage();
        $page->fillField('shipping_method', $method);
    }

    /**
     * @Then I should see MercadoPago Standard available
     */
    public function iShouldSeeMercadopagoStandardAvailable()
    {
        $this->getSession()->wait(10000);
        $this->findElement('#mercadopago_standard');
    }

    /**
     * @When I fill the shipping address
     */
    public function iFillTheShippingAddress()
    {
        $page = $this->getSession()->getPage();

        try {
            $page->find('css', '[name="city"]');
        } catch (ElementNotFoundException $e) {
            return;
        }
        $page->fillField('street[0]', 'Street 123');
        $page->fillField('city', 'City');
        $page->selectFieldOption('country_id', 'AR');
        $page->fillField('postcode', '7000');
        $page->fillField('telephone', '123456');
    }

    /**
     * @Given Setting Config :arg1 is :arg2
     */
    public function settingConfig($arg1, $arg2)
    {
        $this->getConfigManager()->changeConfigs([['path' => $arg1, 'value' => $arg2, 'scope_type' => 'default', 'scope_code' => null]]);
    }


    /**
     * @Then I should not see MercadoPago Standard available
     *
     */
    public function iShouldNotSeeMercadopagoStandardAvailable()
    {
        $this->getSession()->wait(10000);
        if ($this->getSession()->getPage()->find('css', '#mercadopago_standard')) {
            throw new ExpectationException('I saw payment method available', $this->getSession()->getDriver());
        }

        return;
    }

    /**
     * @Given I configure mercadopago standard
     */
    public function iConfigureMercadopagoStandard()
    {
        $configs = [
            ['path' => 'payment/mercadopago/country', 'value' => 'mla', 'scope_type' => 'default', 'scope_code' => null],
            ['path' => 'payment/mercadopago_standard/active', 'value' => '1', 'scope_type' => 'default', 'scope_code' => null],
            ['path' => 'payment/mercadopago_standard/client_id', 'value' => '446950613712741', 'scope_type' => 'default', 'scope_code' => null],
            ['path' => 'payment/mercadopago_standard/client_secret', 'value' => '0WX05P8jtYqCtiQs6TH1d9SyOJ04nhEv', 'scope_type' => 'default', 'scope_code' => null]
        ];
        $this->getConfigManager()->changeConfigs($configs);

    }


}
