<?php declare(strict_types=1);

namespace SBSEDV\Component\Cache\InMemory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use SBSEDV\Component\Cache\InMemory\Item;
use SBSEDV\Component\Cache\InMemory\MemoryCacheItemPool;

class MemoryCacheItemPoolTest extends TestCase
{
    private MemoryCacheItemPool $pool;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->pool = new MemoryCacheItemPool();
    }

    /**
     * Test $pool->save().
     */
    public function saveItem($key, $value)
    {
        $item = $this->pool->getItem($key);
        $item->set($value);
        $this->assertTrue($this->pool->save($item));
    }

    /**
     * Test $pool->getItem() with non existant key.
     */
    public function testGetNotExistingItem()
    {
        $item = $this->pool->getItem('key');

        $this->assertInstanceOf(Item::class, $item);
        $this->assertNull($item->get());
        $this->assertFalse($item->isHit());
    }

    /**
     * Test $pool->getItem() with existant key.
     */
    public function testGetsExistingItem()
    {
        $key = 'key';
        $value = 'value';

        $this->saveItem($key, $value);

        $item = $this->pool->getItem($key);

        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals($value, $item->get());
        $this->assertTrue($item->isHit());
    }

    /**
     * Test $pool->getItems().
     */
    public function testGetItems()
    {
        $keys = ['key1', 'key2'];
        $items = $this->pool->getItems($keys);

        $this->assertEquals($keys, array_keys(iterator_to_array($items)));
        $this->assertContainsOnlyInstancesOf(Item::class, $items);
    }

    /**
     * Test $pool->hasItem().
     */
    public function testHasItem()
    {
        $existingKey = 'existing-key';
        $this->saveItem($existingKey, 'value');

        $this->assertTrue($this->pool->hasItem($existingKey));
        $this->assertFalse($this->pool->hasItem('not-existing-key'));
    }

    /**
     * Test $pool->clear().
     */
    public function testClear()
    {
        $key = 'key';
        $this->saveItem($key, 'value');

        $this->assertTrue($this->pool->hasItem($key));
        $this->assertTrue($this->pool->clear());
        $this->assertFalse($this->pool->hasItem($key));
    }

    /**
     * Test $pool->deleteItem().
     */
    public function testDeleteItem()
    {
        $key = 'key';
        $this->saveItem($key, 'value');

        $this->assertTrue($this->pool->deleteItem($key));
        $this->assertFalse($this->pool->hasItem($key));
    }

    /**
     * Test $pool->deleteItems().
     */
    public function testDeleteItems()
    {
        $keys = ['key1', 'key2'];

        foreach ($keys as $key) {
            $this->saveItem($key, 'value');
        }

        $this->assertTrue($this->pool->deleteItems($keys));

        foreach ($keys as $key) {
            $this->assertFalse($this->pool->hasItem($key));
        }
    }

    /**
     * Test $pool->deleteItems() with invalid keys.
     */
    public function testDeleteItemsWithInvalidKey()
    {
        $keys = ['key1', '{key2}', 'key3'];
        $value = 'value';
        $this->saveItem($keys[0], $value);
        $this->saveItem($keys[2], $value);

        try {
            $this->pool->deleteItems($keys);
        } catch (InvalidArgumentException) {
            // continue execution
        }

        $this->assertTrue($this->pool->hasItem($keys[0]));
        $this->assertTrue($this->pool->hasItem($keys[2]));
    }

    /**
     * Test $pool->saveItem().
     */
    public function testSaveItem()
    {
        $key = 'key';
        $this->saveItem($key, 'value');

        $this->assertTrue($this->pool->hasItem($key));
    }

    /**
     * Test $pool->saveDeferred().
     */
    public function testSaveDeferredItem()
    {
        $item = $this->pool->getItem('key');
        $this->assertTrue($this->pool->saveDeferred($item));
    }

    /**
     * Test $pool->commit().
     */
    public function testCommitDeferredItems()
    {
        $keys = ['key', 'key2'];

        foreach ($keys as $key) {
            $item = $this->pool->getItem($key);
            $item->set('value');
            $this->pool->saveDeferred($item);
        }

        $this->assertTrue($this->pool->commit());

        foreach ($keys as $key) {
            $this->assertTrue($this->pool->hasItem($key));
        }
    }

    /**
     * @dataProvider invalidKeys
     *
     * Test $pool->getItem() with invalid keys.
     */
    public function testGetItemWithInvalidKeys($key)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->getItem($key);
    }

    /**
     * @dataProvider invalidKeys
     *
     * Test $pool->getItems() with invalid keys.
     */
    public function testGetItemsWithInvalidKeys($key)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->getItems([$key]);
    }

    /**
     * @dataProvider invalidKeys
     *
     * Test $pool->hasItem() with invalid keys.
     */
    public function testHasItemWithInvalidKeys($key)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->hasItem($key);
    }

    /**
     * @dataProvider invalidKeys
     *
     * Test $pool->deleteItem() with invalid keys.
     */
    public function testDeleteItemWithInvalidKeys($key)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->deleteItem($key);
    }

    /**
     * @dataProvider invalidKeys
     *
     * Test $pool->deleteItems() with invalid keys.
     */
    public function testDeleteItemsWithInvalidKeys($key)
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->deleteItems([$key]);
    }

    /**
     * Dataprovider with invalid keys.
     */
    public function invalidKeys()
    {
        // keys must be strings and not contain the following characters
        // "{", "}", "(", ")", "/", "\", "@", ":"

        return [
            ['{'],
            ['}'],
            ['('],
            [')'],
            ['/'],
            ['\\'],
            ['@'],
            [':'],
        ];
    }
}
