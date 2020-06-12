<?php

namespace RKW\RkwShop\Tests\Unit\Domain\Model;

use RKW\RkwShop\Domain\Model\ProductType;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Product type test
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 */
class ProductTypeTest extends UnitTestCase {

    /**
     * @var ProductType
     */
    protected $subject;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new ProductType();
    }

    /**
     * @test
     */
    public function getTitleReturnsInitialValueForTitle()
    {
        self::assertSame(
            null,
            $this->subject->getTitle()
        );

    }

    /**
     * @test
     */
    public function setTitleForStringSetsTitle()
    {
        $this->subject->setTitle('Title');

        self::assertAttributeEquals(
            'Title',
            'title',
            $this->subject
        );

    }

    /**
     * @test
     */
    public function getDescriptionReturnsInitialValueForDescription()
    {
        self::assertSame(
            null,
            $this->subject->getDescription()
        );

    }

    /**
     * @test
     */
    public function setDescriptionForStringSetsDescription()
    {
        $this->subject->setDescription('Description');

        self::assertAttributeEquals(
            'Description',
            'description',
            $this->subject
        );

    }

    /**
     * @test
     */
    public function getModelReturnsInitialValueForModel()
    {
        self::assertSame(
            null,
            $this->subject->getModel()
        );

    }

    /**
     * @test
     */
    public function setModelForStringSetsModel()
    {
        $this->subject->setModel('Model');

        self::assertAttributeEquals(
            'Model',
            'model',
            $this->subject
        );

    }

    /**
     * @test
     */
    public function getIsCollectionReturnsInitialValueForBoolean()
    {
        self::assertSame(
            false,
            $this->subject->getIsCollection()
        );

    }

    /**
     * @test
     */
    public function setIsCollectionForBooleanSetsIsCollection()
    {
        $this->subject->setIsCollection(true);

        self::assertAttributeEquals(
            true,
            'isCollection',
            $this->subject
        );

    }

}
