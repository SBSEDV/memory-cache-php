<?php declare(strict_types=1);

namespace SBSEDV\Component\Cache\InMemory;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class MemoryCacheItemPool implements CacheItemPoolInterface
{
    /** @var CacheItemInterface[] */
    private $items;

    /** @var CacheItemInterface[] */
    private $deferredItems;

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        return current($this->getItems([$key]));
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->hasItem($key) ? clone $this->items[$key] : new Item($key);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        $this->assertKeyIsValid($key);

        return isset($this->items[$key]) && $this->items[$key]->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->items = [];
        $this->deferredItems = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        return $this->deleteItems([$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        array_walk($keys, [$this, 'assertKeyIsValid']);

        foreach ($keys as $key) {
            unset($this->items[$key]);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        $this->items[$item->getKey()] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferredItems[$item->getKey()] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        foreach ($this->deferredItems as $item) {
            $this->save($item);
        }

        $this->deferredItems = [];

        return true;
    }

    /**
     * Asserts that the given key is valid.
     *
     * @throws InvalidArgumentException If the key is invalid.
     */
    private function assertKeyIsValid($key)
    {
        $invalidCharacters = '{}()/\\\\@:';

        if (!is_string($key) || preg_match("#[$invalidCharacters]#", $key)) {
            $message = sprintf('The cache item key is not valid: %s', var_export($key, true));
            throw new InvalidArgumentException($message);
        }
    }
}
