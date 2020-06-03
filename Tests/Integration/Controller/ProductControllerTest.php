<?php
namespace RKW\RkwShop\Tests\Integration\Controller;

use RKW\RkwBasics\Helper\Common;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwRegistration\Tools\Authentication;
use RKW\RkwShop\Service\Checkout\CartService;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use RKW\RkwShop\Controller\ProductController;
use RKW\RkwShop\Domain\Repository\CartRepository;
use RKW\RkwShop\Domain\Repository\OrderRepository;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use RKW\RkwShop\Domain\Repository\OrderItemRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwShop\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\PrivacyRepository;
use RKW\RkwShop\Domain\Repository\ShippingAddressRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use RKW\RkwRegistration\Domain\Repository\RegistrationRepository;
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
 * ProductControllerTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwShop
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProductControllerTest extends FunctionalTestCase
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
     * @var \RKW\RkwShop\Controller\ProductController
     */
    private $subject = null;

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

        /*$this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/BeUsers.xml');
        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/FeUsers.xml');
        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Pages.xml');
        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Product.xml');
        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Order.xml');
        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/OrderItem.xml');
        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/ShippingAddress.xml');
        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Stock.xml');
*/

        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Tests/Integration/Controller/ProductControllerTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );


        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->subject = $this->objectManager->get(ProductController::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);
    }

    /**
     * @test
     */
    public function showProductReturnsProduct()
    {

        /**
         * Scenario:
         *
         * Given I already have a product
         * When I visit the product page
         * Then the product is returned to the view
         */

        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findAll()->getFirst();

        $view = $this->getMock(ViewInterface::class);
        $view->expects($this->once())->method('assignMultiple')->with([
            'product' => $product,
            'cartPid' => 0,
        ]);
        $this->inject($this->subject,'view', $view);

        $this->subject->showAction($product);

    }

    /**
     * @test
     */
    public function showProductReturnsProductToViewWithSku()
    {

        /**
         * Scenario:
         *
         * Given I already have a product
         * When I visit the product list page
         * Then the product is returned to the view
         * Then the product's sku is shown on the html output
         */

        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Check10.xml');

        $response = $this->getFrontendResponse(1);

        $this->assertContains("Hallo Welt!", $response->getContent());
        $this->assertContains("9999", $response->getContent());

    }

    /**
     * @test
     */
    public function showProductWithGivenEditionReturnsProductToViewWithEdition()
    {

        /**
         * Scenario:
         *
         * Given I already have a product
         * When I visit the product list page
         * Then the product is returned to the view
         * Then the product's edition is shown on the html output
         */

        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Check20.xml');

        $response = $this->getFrontendResponse(1);

        $this->assertContains("Hallo Welt!", $response->getContent());
        $this->assertContains("2. überarbeitete Auflage", $response->getContent());

    }

    /**
     * @test
     */
    public function showProductWithNoRemainingStockReturnsToViewWithNotDeliverableMessage()
    {

        /**
         * Scenario:
         *
         * Given I already have a product
         * Given this product has a stock of 5
         * Given there is an order containing this product with the amount of 5
         * Given there is no additional stock
         * When I visit the product list page
         * Then the product is returned to the view
         * Then the output shows "undeliverable"
         */

        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Check30.xml');

        $response = $this->getFrontendResponse(1);

        $this->assertContains("Leider derzeit vergriffen.", $response->getContent());

    }

    /**
     * @test
     */
    public function showProductBundleWithSubItemWithNoRemainingStockReturnsToViewWithNotDeliverableMessage()
    {

        /**
         * Scenario:
         *
         * Given I already have a product
         * Given this product is of type "ProductBundle"
         * Given it contains a first product of type "Product"
         * Given this first product has a stock of 10
         * Given it contains a second product of type "Product"
         * Given this second product has a stock of 5
         * Given there is an order containing this second product with the amount of 5
         * Given there is no additional stock
         * When I visit the product list page
         * Then the product of type "ProductBundle" is returned to the view
         * Then the output of this product bundle shows "status--warn"
         */

        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Check40.xml');

        $response = $this->getFrontendResponse(1);

        $this->assertContains("order-list__productbundle order-list__status order-list__status--warn", $response->getContent());

    }

    /**
     * @test
     */
    public function showProductBundleWithSubItemWithRemainingStockReturnsToViewWithoutNotDeliverableMessage()
    {

        /**
         * Scenario:
         *
         * Given I already have a product
         * Given this product is of type "ProductBundle"
         * Given it contains a first product of type "Product"
         * Given this first product has a stock of 10
         * Given it contains a second product of type "Product"
         * Given this second product has a stock of 5
         * Given there is an order containing this second product with the amount of 1
         * Given there is no additional stock
         * When I visit the product list page
         * Then the product of type "ProductBundle" is returned to the view
         * Then the output of this product bundle does not show "status--warn"
         */

        $this->importDataSet(__DIR__ . '/ProductControllerTest/Fixtures/Database/Check50.xml');

        $response = $this->getFrontendResponse(1);

        $this->assertNotContains("order-list__productbundle order-list__status order-list__status--warn", $response->getContent());

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}