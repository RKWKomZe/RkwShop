<?php

namespace RKW\RkwShop\Tests\Unit\Domain\Model;

use RKW\RkwShop\Domain\Model\OrderItem;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Order item test
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 */
class OrderItemTest extends UnitTestCase {

    /**
     * @var OrderItem
     */
    protected $subject;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new OrderItem();
    }

    /**
     * @test
     */
    public function getParentCollectionReturnsInitialValueForParentCollection()
    {
        self::assertSame(
            null,
            $this->subject->getParentCollection()
        );

    }

    /**
     * @test
     */
    public function setParentCollectionForStringSetsParentCollection()
    {
        $this->subject->setParentCollection(1);

        self::assertAttributeEquals(
            1,
            'parentCollection',
            $this->subject
        );

    }

}
