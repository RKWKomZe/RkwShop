<?php

namespace RKW\RkwShop\Tests\Unit\Domain\Model;

use RKW\RkwShop\Domain\Model\Product;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 */
class TestCase extends UnitTestCase {

    /**
     * @var Product
     */
    protected $subject;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new Product();
    }

    /**
     * @test
     */
    public function getSkuReturnsInitialValueForSku()
    {
        self::assertSame(
            '',
            $this->subject->getSku()
        );

    }

    /**
     * @test
     */
    public function setSkuForStringSetsSku()
    {
        $this->subject->setSku('SKU');

        self::assertAttributeEquals(
            'SKU',
            'sku',
            $this->subject
        );

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}
