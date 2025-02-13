<?php

namespace App\Service\Cache;

interface AppCacheInterface
{
    /**
     * Возвращает значение и, если его нет в кеше, запонимает его на укзанное время
     *
     * @param string $key
     * @param int|null $seconds Время в секундах
     * @param callable $callback
     * @return mixed
     */
    public function remember(string $key, ?int $seconds, callable $callback): mixed;

    /**
     * Возвращает запись из redis
     * 
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * Сохраняет запись в redis
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $seconds Время в секундах
     * @return bool
     */
    public function put(string $key, mixed $value, ?int $seconds = null): bool;

    /**
     * Сохраняет запись в redis без времени жизни записи (ttl)
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function forever(string $key, mixed $value): bool;

    /**
     * Удаляет запись из redis
     *
     * @param string $key
     * @return int Кол-во удалённых записей
     */
    public function delete(string $key): int;
}