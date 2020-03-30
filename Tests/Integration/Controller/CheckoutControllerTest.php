<?php
namespace RKW\RkwShop\Tests\Integration\Controller;

use RKW\RkwBasics\Helper\Common;
use RKW\RkwRegistration\Tools\Authentication;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwShop\Controller\CheckoutController;

use RKW\RkwShop\Domain\Repository\CartRepository;
use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Domain\Repository\FrontendUserRepository;
use RKW\RkwShop\Domain\Repository\ShippingAddressRepository;

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;

use RKW\RkwShop\Service\Checkout\CartService;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * CheckoutControllerTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CheckoutControllerTest extends FunctionalTestCase
{

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_mailer',
        'typo3conf/ext/rkw_shop',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwShop\Controller\CheckoutController
     */
    private $subject = null;

    /**
     * @var \RKW\RkwShop\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\ShippingAddressRepository
     */
    private $shippingAddressRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\CartRepository
     */
    private $cartRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     */
    private $orderItemRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\PrivacyRepository
     */
    private $privacyRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\RegistrationRepository
     */
    private $registrationRepository;

    /**
     * @var \RKW\RkwShop\Service\Checkout\CartService
     */
    private $cartService;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        /*$this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/FeUsers.xml');
        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Product.xml');
        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Order.xml');
        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/OrderItem.xml');
        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/ShippingAddress.xml');
        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Stock.xml');
*/

        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Tests/Integration/Controller/CheckoutControllerTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );


        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->subject = $this->objectManager->get(CheckoutController::class);

        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->shippingAddressRepository = $this->objectManager->get(ShippingAddressRepository::class);
        $this->cartRepository = $this->objectManager->get(CartRepository::class);
        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
        $this->orderItemRepository = $this->objectManager->get(OrderItemRepository::class);
        $this->productRepository = $this->objectManager->get(ProductRepository::class);
        $this->privacyRepository = $this->objectManager->get(PrivacyRepository::class);
        $this->registrationRepository = $this->objectManager->get(RegistrationRepository::class);
        $this->cartService = $this->objectManager->get(CartService::class);
    }

    /**
     * @test
     */
    public function showCartReturnsCart()
    {

        //  /checkout/cart

        /**
         * Scenario:
         *
         * Given I already have a cart
         * When I visit the cart page
         * Then my cart is returned to the view
         */

        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Check10.xml');

        Common::initFrontendInBackendContext();

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cart */
        $cart = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        $view = $this->getMock(ViewInterface::class);
        $view->expects($this->once())->method('assignMultiple')->with([
            'cart' => $cart,
            'checkoutPid' => 0
        ]);
        $this->inject($this->subject,'view', $view);

        $this->subject->showCartAction();

    }

    /**
     * @test
     */
    public function showMiniCartReturnsCart()
    {

        /**
         * Scenario:
         *
         * Given I already have an order in the cart
         * When I visit a page
         * Then my order is returned to the mini cart
         */

        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Check10.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cart */
        $order = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash();

        $view = $this->getMock(ViewInterface::class);
        $view->expects($this->once())->method('assignMultiple')->with([
            'cart' => $cart,
            'cartPid' => 0
        ]);
        $this->inject($this->subject,'view', $view);

        $this->subject->showMiniCartAction();

    }

    /**
     * @test
     */
    public function confirmCartReturnsOrder()
    {

        //  /checkout/confirm

        /**
         * Scenario:
         *
         * Given I am logged in
         * Given I already have a cart
         * When I visit the cart confirm page
         * Then my cart is returned to the view
         */

        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Cart $cart */
        $cart = $this->cartRepository->findByFrontendUserOrFrontendUserSessionHash($frontendUser);

        $order = $this->cartService->convertCart($cart);

        $request = $this->getMock(Request::class);

        $view = $this->getMock(ViewInterface::class);
        $view->expects($this->once())->method('assignMultiple')->with([
            'frontendUser' => $frontendUser,
            'order' => $order,
            'termsPid' => 0,
            'terms' => null,
            'privacy' => null,
        ]);
        $this->inject($this->subject,'view', $view);

        $this->subject->confirmCartAction();

    }

    /**
     * @test
     */
    public function confirmCartRedirectsToRegistrationIfNoFrontendUserIsLoggedIn()
    {

        //  /checkout/confirm

        /**
         * Scenario:
         *
         * Given I am not logged in
         * Given I already have an order in the cart
         * When I visit the cart confirm page
         * Then I am redirected to the registration page
         */

        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Check30.xml');

        Common::initFrontendInBackendContext();

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByFrontendUserSessionHash()->getFirst();

        //  this assert redirect
        $view = $this->getMock(ViewInterface::class);
        $view->expects(static::once())->method('assignMultiple')->with([
            'frontendUser' => $frontendUser,
            'order' => $order,
            'termsPid' => 0,
            'terms' => null,
            'privacy' => null,
        ]);
        $this->inject($this->subject,'view', $view);

        $this->subject->confirmCartAction();

    }

    /**
     * @test
     */
    public function reviewOrderReturnsCorrectShippingAddress()
    {

        //  /checkout/review

        /**
         * Scenario:
         *
         * Given I am logged in
         * Given I already have a cart
         * Given I do not want to use a different shipping address
         * When I visit the order review page
         * Then I the order with the same address as the frontend user address is returned
         */

        $this->importDataSet(__DIR__ . '/CheckoutControllerTest/Fixtures/Database/Check30.xml');

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser  $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        Common::initFrontendInBackendContext();
        Authentication::loginUser($frontendUser);

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $order = $this->orderRepository->findByFrontendUser($frontendUser)->getFirst();

        /*
        $result = $this->getMock(CheckoutController::class);
        $result->expects($this->at(0))
            ->method('getResult')
            ->will($this->returnValue($rawResult));
        $result->expects($this->at(1))
            ->method('getResult')
            ->will($this->returnValue(array()));
        */

        $view = $this->getMock(ViewInterface::class);
        $view->expects($this->once())->method('assignMultiple')->with([
            'frontendUser' => $frontendUser,
            'order' => $order,
            'privacy' => null,
        ]);
        $this->inject($this->subject,'view', $view);

        $this->subject->reviewOrderAction($order);

    }

    /**
     * @test
     */
    public function orderCart()
    {
        //  /checkout/order -> /checkout/finish

        //  validate order

        //  assert redirect
    }

    /**
     * @test
     */
    public function finishCart()
    {
        //  /checkout/finish
    }

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }








}