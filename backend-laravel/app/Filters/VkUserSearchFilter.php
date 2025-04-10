<?php

namespace App\Filters;

/**
 * Класс фильтра для поиска пользователей VK.
 *
 * Этот класс предоставляет fluent interface для построения параметров поиска,
 * которые будут использоваться при вызове метода users.search API ВКонтакте.
 */
class VkUserSearchFilter
{
    /**
     * Массив параметров фильтрации.
     *
     * @var array
     */
    protected array $filters = [];

    /**
     * Конструктор класса.
     * Устанавливает значения по умолчанию для фильтров.
     */
    public function __construct()
    {
        $this->setDefaults();
    }

    /**
     * Устанавливает значения по умолчанию для фильтров.
     *
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setDefaults(): self
    {
        $this->filters = [
            'fields' => 'photo_200,city,country,online,screen_name',
            'count'  => 10
        ];

        return $this;
    }

    /**
     * Устанавливает строку поискового запроса.
     *
     * @param string|null $query Строка для поиска пользователей (например, "Иван Петров")
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setQuery(?string $query): self
    {
        $this->filters['q'] = $query;
        return $this;
    }

    /**
     * Устанавливает ID города для фильтрации результатов поиска.
     *
     * @param int $cityId ID города для поиска
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setCity(int $cityId): self
    {
        $this->filters['city'] = $cityId;
        return $this;
    }

    /**
     * Устанавливает количество возвращаемых пользователей.
     *
     * @param int $count Количество пользователей для возврата
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setCount(int $count): self
    {
        $this->filters['count'] = $count;
        return $this;
    }

    /**
     * Добавляет произвольный фильтр к параметрам поиска.
     *
     * @param string $key Ключ фильтра
     * @param mixed $value Значение фильтра
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function addFilter(string $key, $value): self
    {
        $this->filters[$key] = $value;
        return $this;
    }

    /**
     * Возвращает массив параметров для API запроса.
     *
     * @return array Массив параметров для API запроса
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
