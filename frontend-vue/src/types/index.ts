/**
 * Общие типы для проекта
 */

// Тип для пропсов модального окна, где значения могут быть только строками или числами
export type ModalProps = Record<string, string | number>

// Тип для значений, которые могут быть null
export type Nullable<T> = T | null

// Тип для значений, которые могут быть undefined
export type Optional<T> = T | undefined
