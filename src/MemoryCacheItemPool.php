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
    public function getItem(string $key): CacheItemInterface
    {
        /** @var \ArrayIterator $items */
        $items = $this->getItems([$key]);

        return $items->current();
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = []): iterable
    {
        $items = new \ArrayIterator();

        foreach ($keys as $key) {
            $items[$key] = $this->hasItem($key) ? clone $this->items[$key] : new Item($key);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem(string $key): bool
    {
        $this->assertKeyIsValid($key);

        return isset($this->items[$key]) && $this->items[$key]->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->items = [];
        $this->deferredItems = [];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem(string $key): bool
    {
        return $this->deleteItems([$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): bool
    {
        \array_walk($keys, [$this, 'assertKeyIsValid']);

        foreach ($keys as $key) {
            unset($this->items[$key]);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $this->items[$item->getKey()] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferredItems[$item->getKey()] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
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
    private function assertKeyIsValid(string $key)
    {
        $invalidCharacters = '{}()/\\\\@:';

        if (\preg_match("#[$invalidCharacters]#", $key)) {
            $message = \sprintf('The cache item key is not valid: %s', \var_export($key, true));
            throw new InvalidArgumentException($message);
        }
    }
}
