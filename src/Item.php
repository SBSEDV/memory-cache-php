<?php declare(strict_types=1);

namespace SBSEDV\Component\Cache\InMemory;

use Psr\Cache\CacheItemInterface;

final class Item implements CacheItemInterface
{
    /** @var string */
    private $key;

    /** @var mixed */
    private $value;

    /** @var \DateTime|null */
    private $expiration;

    /** @var bool */
    private $isHit = false;

    /**
     * @param string $key The key for the current cache item.
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->isHit() ? $this->value : null;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
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
    public function set($value)
    {
        $this->isHit = true;
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration)
    {
        if (null === $expiration || $expiration instanceof \DateTimeInterface) {
            $this->expiration = $expiration;

            return $this;
        }

        $error = sprintf(
            'Argument 1 passed to %s::expiresAt() must implement interface DateTimeInterface, %s given',
            self::class,
            gettype($expiration)
        );

        throw new \TypeError($error);
    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time)
    {
        if (is_int($time)) {
            $this->expiration = $this->currentTime()->add(new \DateInterval("PT{$time}S"));
        } elseif ($time instanceof \DateInterval) {
            $this->expiration = $this->currentTime()->add($time);
        } elseif ($time === null) {
            $this->expiration = $time;
        } else {
            $message = 'Argument 1 passed to %s::expiresAfter() must be an '.
                       'instance of DateInterval or of the type integer, %s given';

            throw new \TypeError(sprintf($message, self::class, gettype($time)));
        }

        return $this;
    }

    private function currentTime()
    {
        return new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
