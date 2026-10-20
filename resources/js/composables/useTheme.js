import { ref } from 'vue';

const dark = ref(false);

function apply(next) {
    dark.value = next;

    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.toggle('dark', next);
    localStorage.setItem('theme', next ? 'dark' : 'light');
}

export function useTheme() {
    function init() {
        if (typeof localStorage === 'undefined') {
            return;
        }

        apply(localStorage.getItem('theme') === 'dark');
    }

    function toggle() {
        apply(!dark.value);
    }

    return { dark, init, toggle, apply };
}
