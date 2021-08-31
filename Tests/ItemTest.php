<?php declare(strict_types=1);

namespace SBSEDV\Component\Cache\InMemory\Tests;

use PHPUnit\Framework\TestCase;
use SBSEDV\Component\Cache\InMemory\Item;

class ItemTest extends TestCase
{
    /**
     * Test $item->get().
     */
    public function testGetValue()
    {
        $item = $this->getItem('item');

        $this->assertNull($item->get());
        $this->assertFalse($item->isHit());

        $value = 'value';
        $item->set($value);

        $this->assertEquals($value, $item->get());
    }

    /**
     * Test $item->set().
     *
     * @dataProvider validValues
     */
    public function testSetValue($value)
    {
        $item = $this->getItem('key');
        $item->set($value);

        $this->assertEquals($value, $item->get());
    }

    /**
     * Test $item->isHit().
     */
    public function testIsHit()
    {
        $item = $this->getItem('key');

        $this->assertFalse($item->isHit());

        $item->set('value');

        $this->assertTrue($item->isHit());
    }

    /**
     * Test $item->getKey().
     */
    public function testGetsKey()
    {
        $key = 'item';

        $this->assertEquals($key, $this->getItem($key)->getKey());
    }

    /**
     * Test $item->expiresAt().
     */
    public function testExpiresAt()
    {
        $item = $this->getItem('key');
        $item->set('value');
        $item->expiresAt(new \DateTime('+1 hour'));

        $this->assertTrue($item->isHit());

        $item->expiresAt(new \DateTime('yesterday'));

        $this->assertFalse($item->isHit());

        $item->expiresAt(null);

        $this->assertTrue($item->isHit());
    }

    /**
     * Test $item->expiresAfter().
     */
    public function testExpiresAfter()
    {
        $item = $this->getItem('key');
        $item->set('value');
        $item->expiresAfter(60);

        $this->assertTrue($item->isHit());

        $item->expiresAfter(0);

        $this->assertFalse($item->isHit());

        $item->expiresAfter(new \DateInterval('PT1M'));

        $this->assertTrue($item->isHit());

        $item->expiresAfter(null);

        $this->assertTrue($item->isHit());
    }

    /**
     * Dataprovider with valid values.
     */
    public function validValues()
    {
        return [
            // integer
            [1],
            // float
            [1.1],
            // bool
            [true],
            // null
            [null],
            // string
            ['value'],
            // array
            [['value']],
            // object
            [new \DateTime()],
        ];
    }

    private function getItem(string $key)
    {
        return new Item($key);
    }
}
