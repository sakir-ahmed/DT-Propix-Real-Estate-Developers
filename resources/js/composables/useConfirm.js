import { reactive } from 'vue';

export const confirmState = reactive({
    open: false,
    title: '',
    message: '',
    resolve: null,
});

export function useConfirm() {
    function confirm(options) {
        confirmState.title = options.title;
        confirmState.message = options.message;
        confirmState.open = true;

        return new Promise((resolve) => {
            confirmState.resolve = resolve;
        });
    }

    function settle(accepted) {
        confirmState.open = false;
        confirmState.resolve?.(accepted);
        confirmState.resolve = null;
    }

    return { confirm, settle, state: confirmState };
}
