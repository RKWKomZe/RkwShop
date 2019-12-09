<?php
namespace RKW\RkwShop\Tests\Integration\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;

use RKW\RkwShop\Domain\Repository\StockRepository;
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
 * StockRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwMailer
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StockRepositoryTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
        'typo3conf/ext/rkw_shop',

    ];
    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwShop\Domain\Repository\StockRepository
     */
    private $subject = null;

    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;

    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/StockRepositoryTest/Fixtures/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Tests/Functional/Domain/Repository/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(StockRepository::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);

    }


    //=============================================
    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \Exception
     */
    public function getStockSumByProductAndPreOrderExcludesDeletedStocks()
    {
        /**
         * Scenario:
         *
         * Given a product with two stocks
         * Given one of the stocks is a normal stock
         * Given one of the stocks is deleted
         * When I fetch the stock sum of that product
         * Then the deleted stock is excluded from the calculation
         */
        $this->importDataSet(__DIR__ . '/StockRepositoryTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        $result = $this->subject->getStockSumByProductAndPreOrder($product);
        self::assertEquals($result, 50);

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \Exception
     */
    public function getStockSumByProductAndPreOrderExcludesPreOrderStocksInTheFuture()
    {
        /**
         * Scenario:
         *
         * Given a product with three stocks
         * Given one of the stocks is a normal stock
         * Given one of the stocks is a pre-order-stock in the past
         * Given one of the stocks is a pre-order-stock in the future
         * When I fetch the stock sum of that product
         * Then the pre-order-stock in the past is included in the calculation
         * Then the pre-order-stock in the future is excluded from the calculation
         */
        $this->importDataSet(__DIR__ . '/StockRepositoryTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        $result = $this->subject->getStockSumByProductAndPreOrder($product);
        self::assertEquals($result, 70);

    }

    /**
     * @test
     * @throws \TYPO3\CMS\Core\Type\Exception\InvalidEnumerationValueException
     * @throws \Exception
     */
    public function getStockSumByProductAndPreOrderIncludesPreOrderStocksInTheFuture()
    {
        /**
         * Scenario:
         *
         * Given a product with three stocks
         * Given one of the stocks is a normal stock
         * Given one of the stocks is a pre-order-stock in the past
         * Given one of the stocks is a pre-order-stock in the future
         * When I fetch the stock sum of that product
         * Then only the pre-order-stock in the future is included in the calculation
         */
        $this->importDataSet(__DIR__ . '/StockRepositoryTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid(1);

        $result = $this->subject->getStockSumByProductAndPreOrder($product, true);
        self::assertEquals($result, 30);

    }


    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}