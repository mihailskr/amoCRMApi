<?php

declare(strict_types=1);

namespace Integration\Wrapper;

use Memcached;

class MemcachedWrapper
{
    private memcached $memcached;
    /** Время жизни кэша*/
    private const ONE_HOUR = 3600;

    public function __construct()
    {
        $this->memcached = new Memcached();
        $this->memcached->addServer(getenv('MEMCACHED_HOST'), (int)getenv('MEMCACHED_PORT'));
    }

    /**
     * Метод создания хеш ключа по агоритму sha256
     * @param $query
     * @return string
     */
    public function buildHashKey($query): string
    {
        return hash('sha256', http_build_query($query));
    }

    /**
     * Метод создания/добавленея ключа и установка данных в кэш
     * @param $key
     * @param $value
     * @param int $expiration
     * @return bool
     */
    public function set($key, $value, int $expiration = self::ONE_HOUR): bool
    {
        return $this->memcached->set($key, $value, $expiration);
    }

    /**
     * @return Memcached
     */
    public function getMemcached(): Memcached
    {
        return $this->memcached;
    }
}
