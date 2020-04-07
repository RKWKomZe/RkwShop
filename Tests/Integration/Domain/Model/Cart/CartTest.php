<?php

namespace RKW\RkwShop\Tests\Integration\Model\Cart;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwShop\Domain\Model\Cart;
use RKW\RkwShop\Domain\Repository\CartRepository;
use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\CartRepository
     */
    private $cartRepository;

    /**
     * @var \RKW\RkwShop\Domain\Repository\OrderItemRepository
     */
    private $orderItemRepository;

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

        /*$this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/FeUsers.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Product.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Order.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/OrderItem.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/ShippingAddress.xml');
        $this->importDataSet(__DIR__ . '/CartServiceTest/Fixtures/Database/Stock.xml');
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

        $this->productRepository = $this->objectManager->get(ProductRepository::class);
        $this->cartRepository = $this->objectManager->get(CartRepository::class);
        $this->orderItemRepository = $this->objectManager->get(OrderItemRepository::class);

    }

    /**
     * @test
     */
    public function containsProductReturnsCorrespondingOrderItemIfProductIsAlreadyContainedInCart()
    {

        /**
         * Scenario:
         *
         * Given my cart does already exist
         * Given my cart contains 1 order item
         * When I want to check, if an order item of the same product does already exist in the cart
         * Then the check should return the corresponding existing order item
         */

        $this->importDataSet(__DIR__ . '/CartTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\Cart $cart */
        $cart = $this->cartRepository->findByUid(1);

        /** @var \RKW\RkwShop\Domain\Model\OrderItem $existingOrderItem */
        $existingOrderItem = $this->orderItemRepository->findByUid(1);

        static::assertSame($existingOrderItem, $cart->containsProduct($product));

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