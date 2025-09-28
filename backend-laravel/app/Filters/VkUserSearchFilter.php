<?php
declare(strict_types=1);

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
            'fields' => 'photo_200,city,country,online,screen_name,is_closed',
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
     * Устанавливает фильтр по полу.
     *
     * @param int|null $sex Пол пользователя (1 - женщина, 2 - мужчина, null - любой)
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setSex(?int $sex): self
    {
        if ($sex !== null) {
            $this->filters['sex'] = $sex;
        }

        return $this;
    }

    /**
     * Устанавливает фильтр по возрасту от
     *
     * @param int|null $ageFrom Минимальный возраст
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setAgeFrom(?int $ageFrom): self
    {
        if ($ageFrom !== null) {
            $this->filters['age_from'] = $ageFrom;
        }

        return $this;
    }

    /**
     * Устанавливает фильтр по возрасту до
     *
     * @param int|null $ageTo Максимальный возраст
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setAgeTo(?int $ageTo): self
    {
        if ($ageTo !== null) {
            $this->filters['age_to'] = $ageTo;
        }

        return $this;
    }

    /**
     * Устанавливает фильтр по онлайн статусу.
     *
     * @param bool $onlineOnly Показывать только онлайн пользователей
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setOnlineOnly(bool $onlineOnly = true): self
    {
        if ($onlineOnly) {
            $this->filters['online'] = 1;
        }

        return $this;
    }


    /**
     * Устанавливает фильтр по наличию фото через API параметр.
     *
     * @param bool|null $hasPhoto Показывать только пользователей с фото (null - любой)
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setHasPhoto(?bool $hasPhoto): self
    {
        if ($hasPhoto !== null) {
            $this->filters['has_photo'] = $hasPhoto ? 1 : 0;
        }

        return $this;
    }

    /**
     * Устанавливает сортировку результатов через API параметр.
     *
     * @param int|null $sort Сортировка (0 - по популярности, 1 - по регистрации, null - по умолчанию)
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setSort(?int $sort): self
    {
        if ($sort !== null) {
            $this->filters['sort'] = $sort;
        }

        return $this;
    }

    /**
     * Устанавливает фильтр по последнему посещению (поддерживается VK API).
     *
     * @param int|null $lastSeen Максимальное количество дней с последнего посещения
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setLastSeen(?int $lastSeen): self
    {
        if ($lastSeen !== null) {
            $this->filters['last_seen'] = $lastSeen;
        }

        return $this;
    }

    /**
     * Устанавливает фильтр по статусу дружбы (поддерживается VK API).
     *
     * @param bool|null $isFriend Есть ли в друзьях у текущего пользователя
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setIsFriend(?bool $isFriend): self
    {
        if ($isFriend !== null) {
            $this->filters['is_friend'] = $isFriend ? 1 : 0;
        }

        return $this;
    }

    /**
     * Устанавливает фильтры для дополнительной обработки через users.get.
     * Эти фильтры будут применены после получения результатов от API через дополнительный запрос.
     *
     * @param int|null $minFriends Минимальное количество друзей
     * @param int|null $maxFriends Максимальное количество друзей
     * @param int|null $minFollowers Минимальное количество подписчиков
     * @param int|null $maxFollowers Максимальное количество подписчиков
     * @return VkUserSearchFilter Возвращает текущий экземпляр для цепочки вызовов
     */
    public function setExtendedFilters(
        ?int $minFriends = null,
        ?int $maxFriends = null,
        ?int $minFollowers = null,
        ?int $maxFollowers = null
    ): self {
        $extendedFilters = [];

        if ($minFriends !== null) {
            $extendedFilters['min_friends'] = $minFriends;
        }
        if ($maxFriends !== null) {
            $extendedFilters['max_friends'] = $maxFriends;
        }
        if ($minFollowers !== null) {
            $extendedFilters['min_followers'] = $minFollowers;
        }
        if ($maxFollowers !== null) {
            $extendedFilters['max_followers'] = $maxFollowers;
        }

        if (!empty($extendedFilters)) {
            $this->filters['extended_filters'] = $extendedFilters;
        }

        return $this;
    }

    /**
     * Получает расширенные фильтры для дополнительной обработки.
     *
     * @return array Массив расширенных фильтров
     */
    public function getExtendedFilters(): array
    {
        return $this->filters['extended_filters'] ?? [];
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
