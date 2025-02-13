<?php

namespace App\Abstractions\Common;

/**
 * Класс для перечислений, предоставляющий
 * основной необходимый функционал для консистентности,
 * если не хватает функционала обычных перечислений (enum).
 *
 * Если нужно исключить какую-нибудь константу из списка,
 * нужно добавить ей #[NotEnum] в описание.
 */
abstract class Enumeration
{
    /**
     * Для кеширования уже полученных с помощью рефлексии перечислений
     *
     * @var array
     */
    private static array $resolvedEnums = [];

    private function __construct() {}

    /**
     * Получить по id.
     * Работает, если константа является ассоциативным массивом.
     *
     * @param int $id
     * @return array
     */
    public static function getByArrId(int $id): array
    {
        return static::getByArrKey('id', $id);
    }

    /**
     * Получить по slug.
     * Работает, если константа является ассоциативным массивом.
     *
     * @param string $slug
     * @return array
     */
    public static function getByArrSlug(string $slug): array
    {
        return static::getByArrKey('slug', $slug);
    }

    /**
     * Получить по переданному ключу и значению.
     * Работает, если константа является ассоциативным массивом.
     *
     * @param string $key
     * @param mixed $value
     * @return array
     *
     * @throws \Throwable
     */
    public static function getByArrKey(string $key, $value): array
    {
        $allEnums = static::getAll();

        $arrayKey = array_search($value, array_column($allEnums, $key));

        if (false === $arrayKey) {
            throw new \RuntimeException('Такого значения не существует.');
        }

        return array_values($allEnums)[$arrayKey];
    }

    /**
     * Получить по переданному ключу и значению.
     * Работает, если константа является ассоциативным массивом.
     * Если нет такого значения, возвращается null.
     *
     * @param string $key
     * @param mixed $value
     * @return array|null
     */
    public static function getByArrKeyOrNull(string $key, $value): ?array
    {
        if (in_array($value, [null, ''], true)) return null;

        $allEnums = static::getAll();

        $arrayKey = array_search($value, array_column($allEnums, $key));

        return array_values($allEnums)[$arrayKey] ?? null;
    }

    /**
     * Получить список всех id
     *
     * @return array
     */
    public static function getAllIds(): array
    {
        return array_column(static::getAll(), 'id');
    }

    /**
     * Получить все значения
     *
     * @return array
     */
    public static function getAll(): array
    {
        $class = static::class;

        // Если в кеше уже есть нужные перечисления, то используем их
        if (static::$resolvedEnums[$class] ?? null) {
            return static::$resolvedEnums[$class];
        }

        $reflection = new \ReflectionClass(static::class);
        $reflectionConstants = $reflection->getReflectionConstants();
        $constants = [];

        // Получаем только те константы, у которых нет #[NotEnum]
        foreach ($reflectionConstants as $key => $constant) {
            if ($constant->getDocComment() && strpos($constant->getDocComment(), '#[NotEnum]') !== false) {
                continue;
            }

            $constants[$constant->getName()] = $constant->getValue();
        }

        // Кешируем полученные значения
        static::$resolvedEnums[$class] = $constants;

        return $constants;
    }
}
