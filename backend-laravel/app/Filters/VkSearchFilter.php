<?php

namespace App\Filters;

/**
 * Класс фильтра для поиска пользователей VK.
 *
 * Этот класс предоставляет fluent interface для построения параметров поиска,
 * которые будут использоваться при вызове метода users.search API ВКонтакте.
 */
class VkSearchFilter
{
    /**
     * Массив параметров фильтрации для API запроса.
     *
     * @var array
     */
    private array $filters = [];

    /**
     * Устанавливает строку поискового запроса.
     *
     * @param string|null $query Строка для поиска пользователей (например, "Иван Петров")
     * @return self Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setQuery(?string $query): self
    {
        if ($query !== null) {
            $this->filters['q'] = trim($query);
        }

        return $this;
    }

    /**
     * Устанавливает ID города для фильтрации результатов поиска.
     *
     * @param int $cityId ID города для поиска
     * @return self Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setCity(int $cityId): self
    {
        $this->filters['city'] = $cityId;
        return $this;
    }

    /**
     * Преобразует параметры фильтра в массив для API запроса.
     *
     * Добавляет обязательные параметры запроса:
     * - fields: дополнительные поля профилей пользователей
     * - count: количество возвращаемых пользователей
     *
     * @return array Массив параметров для API запроса
     */
    public function toArray(): array
    {
        return array_merge(
            $this->filters,
            [
                'fields' => 'photo_200,city,country,online, screen_name',
                'count'  => 10
            ]
        );
    }
}
