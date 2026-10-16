import { LogChange } from '@/types/activity-log';
import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

/**
 * Форматирует дату в читаемый русский формат
 */
export function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Получение человекочитаемого названия типа лога
 */
export function getLogTypeLabel(logName: string): string {
    const map: Record<string, string> = {
        'default': 'Система',
        'users': 'Пользователи',
        'profiles': 'Профили',
        'contacts': 'Контакты',
        'roles': 'Роли',
    };
    return map[logName] || logName;
}

/**
 * Получение цвета бейджа в зависимости от типа лога
 */
export function getLogTypeColor(logName: string): string {
    const map: Record<string, string> = {
        'default': 'bg-zinc-700 text-zinc-200',
        'users': 'bg-blue-600/20 text-blue-300 border-blue-500/30',
        'profiles': 'bg-purple-600/20 text-purple-300 border-purple-500/30',
        'contacts': 'bg-green-600/20 text-green-300 border-green-500/30',
        'roles': 'bg-orange-600/20 text-orange-300 border-orange-500/30',
    };
    return map[logName] || 'bg-zinc-700 text-zinc-200';
}

/**
 * Форматирует любое значение для отображения в логах
 */
export function formatLogValue(value: any): string {
    if (value === null) return '<null>';
    if (value === undefined) return '<undefined>';
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value);
}

/**
 * Приводит данные лога (Spatie или ручные) к единому виду: массив изменений.
 * Поддерживает:
 *  - Spatie: { attributes: {...}, old: {...} }
 *  - Ручной: { old: [...], new: [...] }
 *  - Плоский: { bio: "new value" }
 */
export function getLogChanges(log: any): LogChange[] {
    const source = log?.attribute_changes ?? log?.properties;
    if (!source || typeof source !== 'object') return [];

    // Определяем, есть ли вложенная структура
    const hasNested = 'attributes' in source || 'new' in source || 'old' in source;

    const newValues = hasNested
        ? (source.attributes ?? source.new ?? {})
        : source;
    const oldValues = hasNested ? (source.old ?? {}) : {};

    const keys = new Set([...Object.keys(oldValues), ...Object.keys(newValues)]);
    const changes: LogChange[] = [];

    keys.forEach((key) => {
        const oldValue = oldValues[key];
        const newValue = newValues[key];

        // Пропускаем, если значение не изменилось
        if (JSON.stringify(oldValue) === JSON.stringify(newValue)) return;

        changes.push({ key, oldValue, newValue });
    });

    return changes;
}
