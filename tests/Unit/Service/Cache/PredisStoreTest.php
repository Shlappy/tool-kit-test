<?php

namespace App\Tests\Unit\Service\Cache;

use App\Service\Cache\AppCacheInterface;
use App\Service\Cache\PredisStore;
use App\Tests\UnitTestCase;

final class PredisStoreTest extends UnitTestCase
{
    private AppCacheInterface $cache;

    /**
     * This method is called before each test.
     *
     * @codeCoverageIgnore
     */
    protected function setUp(): void
    {
        $this->cache = new PredisStore;
    }

    /**
     * Методы put и гет работают и возвращают верные значения
     */
    public function testPutAndGetWork(): void
    {
        $key = 'key_for_test_1';
        $value = 'value_for_test_1';

        $putResult = $this->cache->put($key, $value, 300);

        $this->assertTrue($putResult);
        $this->assertEquals($this->cache->get($key), $value);
    }

    /**
     * Метод delete работает корректно
     */
    public function testDeleteWorks(): void
    {
        $key = 'key_for_test_2';
        $value = 'value_for_test_2';

        $putResult = $this->cache->put($key, $value, 300);
        $this->assertTrue($putResult);

        // Значение поместилось в redis
        $this->assertEquals($this->cache->get($key), $value);

        $this->cache->delete($key);

        // Теперь значения в redis больше нет, должен вернутся null
        $this->assertNull($this->cache->get($key));
    }

    /**
     * Метод remember работает корректно
     */
    public function testRememberWorks(): void
    {
        $key = 'key_for_test_3';
        $value = 'value_for_test_3';

        // Удаляем ключ, чтобы его не было в redis перед проверкой
        $this->cache->delete($key);

        // Сколько раз выполнен псевдо-расчет внутри метода remember (в нашем случае простой возврат $value)
        $hitTime = 0;

        $callback = function () use ($key, $value, &$hitTime) {
            return $this->cache->remember($key, 300, function () use ($value, &$hitTime) {
                $hitTime++;

                return $value;
            });
        };

        // Вызываем 3 раза
        $callback();
        $callback();
        $result = $callback();

        // 1. Возвращенный результат корректный
        $this->assertEquals($result, $value);

        // 2. Псведо вычисление внутри метода remember было всего 1 раз,
        // учитывая, что метод был выполнен 3 раза
        $this->assertEquals(1, $hitTime);
    }
}