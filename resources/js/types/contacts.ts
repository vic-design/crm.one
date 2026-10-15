// Единый источник истины для типов контактов

export type ContactType = 'phone' | 'telegram' | 'whatsapp' | 'skype' | 'other';

export interface ContactTypeInfo {
    value: ContactType;
    label: string;
    placeholder: string;
    needsDescription: boolean;
}

export const CONTACT_TYPES: ContactTypeInfo[] = [
    {
        value: 'phone',
        label: 'Телефон',
        placeholder: '+7 (999) 000-00-00',
        needsDescription: false
    },
    {
        value: 'telegram',
        label: 'Telegram',
        placeholder: '@username',
        needsDescription: false
    },
    {
        value: 'whatsapp',
        label: 'WhatsApp',
        placeholder: '+7 (999) 000-00-00',
        needsDescription: false
    },
    {
        value: 'skype',
        label: 'Skype',
        placeholder: 'username',
        needsDescription: false
    },
    {
        value: 'other',
        label: 'Другое',
        placeholder: 'Значение',
        needsDescription: true
    },
];

export interface Contact {
    id?: number;
    type: ContactType;
    description: string;
    value: string;
}

export function getContactTypeInfo(type: ContactType): ContactTypeInfo {
    return CONTACT_TYPES.find(t => t.value === type) || CONTACT_TYPES[0];
}

export function createEmptyContact(): Contact {
    return {
        type: 'phone',
        description: '',
        value: '',
    };
}
