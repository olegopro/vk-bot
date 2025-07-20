/**
 * Общие типы для проекта
 */

import { Component } from 'vue'

// Расширенный тип для пропсов модального окна
export type ModalProps = Record<string, string | number>

// Тип для извлечения props из Vue компонента для SFC с <script setup>
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type ComponentProps<T extends Component> = T extends new (...args: any) => { $props: infer P } ? P : never

// Тип для значений, которые могут быть null
export type Nullable<T> = T | null

// Тип для значений, которые могут быть undefined
export type Optional<T> = T | undefined
