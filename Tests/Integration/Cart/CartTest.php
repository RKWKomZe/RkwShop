<?php

namespace RKW\RkwShop\Tests\Integration\Cart;

use RKW\RkwShop\Cart\Cart;
use TYPO3\CMS\Extbase\Mvc\Request;
use RKW\RkwShop\Domain\Model\Order;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
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
 * CartTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CartTest extends FunctionalTestCase
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
     * @var \RKW\RkwShop\Cart\Cart
     */
    private $subject = null;

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

        /*$this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/FeUsers.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Product.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Order.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/OrderItem.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/ShippingAddress.xml');
        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Stock.xml');
*/

        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Tests/Functional/Orders/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );


        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->subject = $this->objectManager->get(Cart::class);

        $this->orderRepository = $this->objectManager->get(OrderRepository::class);
        $this->orderItemRepository = $this->objectManager->get(OrderItemRepository::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);

    }

    /**
     * @test
     */
    public function initializeCreatesANewOrderForSessionUserIfAnOrderDoesNotAlreadyExist ()
    {

        /**
         * Scenario:
         *
         * Given a guest adds a product to the cart
         * Given he has not created a cart yet
         * When I initialize a cart
         * Then a new cart is created
         */

        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Check10.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderBefore = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(0, count($orderBefore));

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        $this->subject->initialize($product, $amount = 1);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderAfter = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderAfter));
        static::assertEquals('1', $orderAfter->getFirst()->getUid());

    }

    /**
     * @test
     */
    public function initializeUpdatesOrderForSessionUserIfAnOrderDoesAlreadyExist ()
    {

        /**
         * Scenario:
         *
         * Given a guest adds a product to the cart
         * Given he created a cart before
         * When I initialize a cart
         * Then the existing cart is updated
         */

        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Check20.xml');

        $_COOKIE[FrontendUserAuthentication::getCookieName()] = '12345678';

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderBefore = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderBefore));
        static::assertEquals('1', $orderBefore->getFirst()->getUid());

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $selectedProduct = $this->productRepository->findByUid(2);

        $this->subject->initialize($selectedProduct, $amount = 1);

        /** @var \RKW\RkwShop\Domain\Model\Order $order */
        $orderAfter = $this->orderRepository->findByFrontendUserSessionHash();

        static::assertEquals(1, count($orderAfter));
        static::assertEquals('1', $orderAfter->getFirst()->getUid());
        static::assertEquals(2, count($orderAfter->getFirst()->getOrderItem()));
//        static::assertEquals($selectedProduct, $orderAfter->getFirst()->getOrderItem()->rewind()->getProduct());

    }

    /**
     * @test
     * @throws \RKW\RkwShop\Exception
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     */
    public function createCartChecksForOrderItems ()
    {

        /**
         * Scenario:
         *
         * Given a product is added to the cart
         * Given the amount is 0
         * When I create a cart
         * Then an error is thrown
         */

        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        static::expectException(\RKW\RkwShop\Exception::class);
        static::expectExceptionMessage('orderManager.error.noOrderItem');

        $this->subject->initialize($product, $amount = 0);

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