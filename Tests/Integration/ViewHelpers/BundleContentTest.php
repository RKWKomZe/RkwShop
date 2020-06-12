<?php


namespace RKW\RkwShop\Tests\Unit\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use RKW\RkwShop\Domain\Model\ProductBundle;
use RKW\RkwShop\ViewHelpers\BundleContentViewHelper;
use RKW\RkwShop\Domain\Repository\ProductRepository;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * BundleContentTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 */
class BundleContentTest extends FunctionalTestCase
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
     * @var BundleContentViewHelper
     */
    protected $subject;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager;

    /**
     * @var \RKW\RkwShop\Domain\Repository\ProductRepository
     */
    private $productRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/BundleContentTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_basics/Configuration/TypoScript/setup.txt',
                'EXT:rkw_mailer/Configuration/TypoScript/setup.txt',
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Configuration/TypoScript/setup.txt',
                'EXT:rkw_shop/Tests/Integration/ViewHelpers/BundleContentTest/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        $this->subject = new BundleContentViewHelper();

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->productRepository = $this->objectManager->get(ProductRepository::class);

    }

    /**
     * @test
     */
    public function renderBundleContentReturnsProductListIfCurrentProductBelongsToAProductCollection()
    {

        /**
         * Scenario:
         *
         * Given a product is part of a product collection
         * When I render the BundleContent
         * Then all products belonging to that collection are returned.
         */

        $this->importDataSet(__DIR__ . '/BundleContentTest/Fixtures/Database/Check10.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid($firstChildUid = 2);

        $results = $this->subject->render($product);

        static::assertEquals(2, count($results[$parentUid = 1]));

    }

    /**
     * @test
     */
    public function renderBundleContentReturnsCompleteProductListIfCurrentProductBelongsToMultipleProductCollections()
    {

        /**
         * Scenario:
         *
         * Given a product is part of a product collection
         * Given this product is part of a second product collection
         * When I render the BundleContent
         * Then both product collections containing all products belonging to each collection are returned.
         */

        $this->importDataSet(__DIR__ . '/BundleContentTest/Fixtures/Database/Check20.xml');

        /** @var \RKW\RkwShop\Domain\Model\Product $product */
        $product = $this->productRepository->findByUid($firstChildUid = 3);

        $results = $this->subject->render($product);

        static::assertEquals(2, count($results[$parentUid = 1]));
        static::assertEquals(2, count($results[$parentUid = 2]));

    }

}
