export function formatDate(value: string | null, locale = 'pt-PT'): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: 'Europe/Lisbon',
    }).format(new Date(value));
}

export function formatDateTime(value: string | null, locale = 'pt-PT'): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'Europe/Lisbon',
    }).format(new Date(value));
}

export function toDateInputValue(value: string | null): string {
    if (!value) {
        return '';
    }

    return value.slice(0,10);
}
