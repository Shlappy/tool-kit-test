<?php

namespace App\Service\Cache;

use Predis\Client;

/**
 * Обёртка поверх Predis\Client для удобства и гибкости
 */
class PredisStore implements AppCacheInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client($_ENV['REDIS_URL']);
    }

    /**
     * Возвращает значение и, если его нет в кеше, запонимает его на укзанное время
     *
     * @param string $key
     * @param int|null $seconds Время в секундах
     * @param callable $callback
     * @return mixed
     */
    public function remember(string $key, ?int $seconds, callable $callback): mixed
    {
        $value = $this->get($key);

        if (!is_null($value)) {
            return $value;
        }

        $this->put($key, $value = $callback(), $seconds ?? 1);

        return $value;
    }

    /**
     * Возвращает запись из redis
     * 
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        $value = $this->client->get($key);

        return ! is_null($value) ? $this->unserialize($value) : null;
    }

    /**
     * Сохраняет запись в redis
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $seconds Время в секундах
     * @return bool
     */
    public function put(string $key, mixed $value, ?int $seconds = null): bool
    {
        return (bool) $this->client->setex($key, (int) max(1, $seconds), $this->serialize($value));
    }

    /**
     * Сохраняет запись в redis без времени жизни записи (ttl)
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function forever(string $key, mixed $value): bool
    {
        return (bool) $this->client->set($key, $this->serialize($value));
    }

    /**
     * Удаляет запись из redis
     *
     * @param string $key
     * @return int Кол-во удалённых записей
     */
    public function delete(string $key): int
    {
        return $this->client->del($key);
    }

    /**
     * Преобразовать (сериализовать) значение перед сохранением в redis
     *
     * @param mixed $value
     * @return mixed
     */
    private function serialize(mixed $value): mixed
    {
        return is_numeric($value) && ! in_array($value, [INF, -INF]) && ! is_nan($value)
            ? $value
            : serialize($value);
    }

    /**
     * Десериализация значения из redis
     *
     * @param mixed $value
     * @return mixed
     */
    private function unserialize(mixed $value): mixed
    {
        return is_numeric($value) ? $value : unserialize($value);
    }
}