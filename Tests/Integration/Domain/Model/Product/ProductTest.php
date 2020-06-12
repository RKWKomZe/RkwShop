<?php

namespace RKW\RkwShop\Tests\Integration\Model\Product;

use RKW\RkwShop\Domain\Model\Product;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwShop\Domain\Model\ProductBundle;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
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
 * ProductTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProductTest extends FunctionalTestCase
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

        $this->importDataSet(__DIR__ . '/ProductTest/Fixtures/Database/Global.xml');
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

    }

    /**
     * @test
     */
    public function getParentProductReturnsCorrectProductBundle()
    {

        /**
         * Scenario:
         *
         * Given a product has a parent product
         * Given the parent product exists
         * When I want to get the product's parents
         * Then I get the product's parent object
         * Then the product's parent object is a ProductBundle
         */

        $this->importDataSet(__DIR__ . '/ProductTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $childProduct */
        $childProduct = $this->productRepository->findByUid(2);

        static::assertInstanceOf(Product::class, $childProduct);

        static::assertEquals(1, $childProduct->getParentProducts()->count());

        /** @var \RKW\RkwShop\Domain\Model\Product $parentProduct */
        $parentProduct = $this->productRepository->findByUid(1);

        static::assertSame($parentProduct, $childProduct->getParentProducts()->current());
        static::assertInstanceOf(ProductBundle::class, $parentProduct);

    }

    //=============================================

}