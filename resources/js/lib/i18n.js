import { usePage } from '@inertiajs/vue3';

export function useI18n() {
    const page = usePage();

    function t(key, replace = {}) {
        const [group, ...rest] = key.split('.');
        let value = page.props.translations?.[group];

        for (const part of rest) {
            value = value?.[part];
        }

        if (typeof value !== 'string') {
            return key;
        }

        return Object.entries(replace).reduce(
            (line, [name, replacement]) => line.replaceAll(`:${name}`, String(replacement)),
            value,
        );
    }

    return {
        t,
        locale: () => page.props.locale ?? 'en',
    };
}
