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
