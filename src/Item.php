<?php declare(strict_types=1);

namespace SBSEDV\Component\Cache\InMemory;

use Psr\Cache\CacheItemInterface;

final class Item implements CacheItemInterface
{
    private mixed $value;
    private ?\DateTimeInterface $expiration = null;
    private bool $isHit = false;

    /**
     * @param string $key The key for the current cache item.
     */
    public function __construct(
        private string $key
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get(): mixed
    {
        return $this->isHit() ? $this->value : null;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit(): bool
    {
        if (!$this->isHit) {
            return false;
        }

        if ($this->expiration === null) {
            return true;
        }

        return $this->currentTime()->getTimestamp() < $this->expiration->getTimestamp();
    }

    /**
     * {@inheritdoc}
     */
    public function set(mixed $value): static
    {
        $this->isHit = true;
        $this->value = (is_object($value) ? clone $value : $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->expiration = $expiration;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter(int | \DateInterval | null $time): static
    {
        if (is_int($time)) {
            $this->expiration = $this->currentTime()->add(new \DateInterval("PT{$time}S"));
        } elseif ($time instanceof \DateInterval) {
            $this->expiration = $this->currentTime()->add($time);
        } else {
            $this->expiration = $time;
        }

        return $this;
    }

    private function currentTime(): \DateTime
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
