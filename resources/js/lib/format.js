const banglaDigits = '০১২৩৪৫৬৭৮৯';

export function localizeDigits(value, locale) {
    const text = String(value);

    if (locale !== 'bn') {
        return text;
    }

    return text.replace(/\d/g, (digit) => banglaDigits[Number(digit)]);
}

export function formatMoney(amount, locale = 'en') {
    const formatted = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(Number(amount));

    return localizeDigits(formatted, locale);
}

export function formatDate(value, locale = 'en') {
    const date = value instanceof Date ? value : new Date(value);
    const formatted = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Dhaka',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(date);

    return localizeDigits(formatted, locale);
}

export function formatLongDate(value, locale = 'en') {
    if (!value) {
        return '';
    }

    const date = value instanceof Date ? value : new Date(value);
    const formatted = new Intl.DateTimeFormat(locale === 'bn' ? 'bn-BD' : 'en-GB', {
        timeZone: 'Asia/Dhaka',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(date);

    return localizeDigits(formatted, locale);
}

export function formatMonthYear(value, locale = 'en') {
    if (!value) {
        return '';
    }

    const date = value instanceof Date ? value : new Date(value);
    const formatted = new Intl.DateTimeFormat(locale === 'bn' ? 'bn-BD' : 'en-GB', {
        timeZone: 'Asia/Dhaka',
        month: 'long',
        year: 'numeric',
    }).format(date);

    return localizeDigits(formatted, locale);
}

/**
 * Bangladeshi price display in lakh and crore, for example "BDT 1.25 Crore".
 */
export function formatBdt(amount, locale = 'en') {
    const value = Number(amount);

    if (!Number.isFinite(value)) {
        return '';
    }

    const trim = (text) => text.replace(/\.?0+$/, '');
    let text;

    if (value >= 10000000) {
        text = `${trim((value / 10000000).toFixed(2))} ${locale === 'bn' ? 'কোটি' : 'Crore'}`;
    } else if (value >= 100000) {
        text = `${trim((value / 100000).toFixed(2))} ${locale === 'bn' ? 'লাখ' : 'Lakh'}`;
    } else {
        text = new Intl.NumberFormat('en-US').format(Math.round(value));
    }

    return localizeDigits(`BDT ${text}`, locale);
}

export function formatNumber(value, locale = 'en') {
    return localizeDigits(new Intl.NumberFormat('en-US').format(Number(value) || 0), locale);
}
