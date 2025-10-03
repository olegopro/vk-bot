/**
 * Фильтрует объект от свойств с null и undefined
 * @example filterNullableValues({ a: 1, b: null, c: false }) // { a: 1, c: false }
 */
export const filterNullableValues = <T extends object>(obj: T): Partial<T> => {
  return Object.fromEntries(
    // Пропускаем ключ в деструктуризации, так как фильтруем только по значению
    Object.entries(obj).filter(([, value]) => value !== null && value !== undefined)
  ) as Partial<T>
}
