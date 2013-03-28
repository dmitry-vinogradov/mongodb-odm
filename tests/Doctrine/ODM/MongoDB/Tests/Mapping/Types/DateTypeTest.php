<?php

namespace Doctrine\ODM\MongoDB\Tests\Mapping\Types;

use Doctrine\ODM\MongoDB\Mapping\Types\DateType;

class DateTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertToDatabaseValue()
    {
        $type = new DateType();

        $this->assertNull($type->convertToDatabaseValue(null), 'null is not converted');

        $mongoDate = new \MongoDate();
        $this->assertSame($mongoDate, $type->convertToDatabaseValue($mongoDate), 'MongoDate objects are not converted');

        $yesterday = strtotime('yesterday');
        $mongoDate = new \MongoDate($yesterday);
        $dateTime = new \DateTime('@' . $yesterday);
        $this->assertEquals($mongoDate, $type->convertToDatabaseValue($dateTime), 'DateTime objects are converted to MongoDate objects');
        $this->assertEquals($mongoDate, $type->convertToDatabaseValue($yesterday), 'Numeric timestamps are converted to MongoDate objects');
        $this->assertEquals($mongoDate, $type->convertToDatabaseValue(date('c', $yesterday)), 'String dates are converted to MongoDate objects');
    }

    /**
     * @dataProvider provideInvalidDateValues
     * @expectedException InvalidArgumentException
     */
    public function testConvertToDatabaseValueWithInvalidValues($value)
    {
        $type = new DateType();
        $type->convertToDatabaseValue($value);
    }

    public function provideInvalidDateValues()
    {
        return array(
            'array'  => array(array()),
            'string' => array('whatever'),
            'bool'   => array(false),
            'object' => array(new \stdClass()),
        );
    }

    /**
     * @dataProvider provideDatabaseToPHPValues
     */
    public function testConvertToPHPValue($input, $output)
    {
        $type = new DateType();

        $return = $type->convertToPHPValue($input);

        $this->assertInstanceOf('DateTime', $return);
        $this->assertTimestampEquals($output, $return);
    }

    public function testConvertToPHPValueDoesNotConvertNull()
    {
        $type = new DateType();

        $this->assertNull($type->convertToPHPValue(null));
    }

    /**
     * @dataProvider provideDatabaseToPHPValues
     */
    public function testClosureToPHP($input, $output)
    {
        $type = new DateType();
        $return = null;

        call_user_func(function($value) use ($type, &$return) {
            eval($type->closureToPHP());
        }, $input);

        $this->assertInstanceOf('DateTime', $return);
        $this->assertTimestampEquals($output, $return);
    }

    public function provideDatabaseToPHPValues()
    {
        $yesterday = strtotime('yesterday');
        $mongoDate = new \MongoDate($yesterday);
        $dateTime = new \DateTime('@' . $yesterday);

        return array(
            array($mongoDate, $dateTime),
            array($yesterday, $dateTime),
            array(date('c', $yesterday), $dateTime),
        );
    }

    private function assertTimestampEquals(\DateTime $expected, \DateTime $actual)
    {
        $this->assertEquals($expected->getTimestamp(), $actual->getTimestamp());
    }
}
