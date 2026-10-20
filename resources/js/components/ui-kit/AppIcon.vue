<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
    faArrowDown,
    faArrowUp,
    faBell,
    faBuilding,
    faChartLine,
    faCircleCheck,
    faCircleInfo,
    faCircleXmark,
    faClock,
    faDoorOpen,
    faDownload,
    faEllipsis,
    faFilter,
    faFloppyDisk,
    faLanguage,
    faLock,
    faMagnifyingGlass,
    faMoon,
    faPenToSquare,
    faPhone,
    faPlus,
    faSun,
    faTrash,
    faTriangleExclamation,
    faUserPlus,
    faUsers,
    faXmark,
} from '@fortawesome/free-solid-svg-icons';

const icons = {
    plus: faPlus,
    'pen-to-square': faPenToSquare,
    trash: faTrash,
    'floppy-disk': faFloppyDisk,
    xmark: faXmark,
    'magnifying-glass': faMagnifyingGlass,
    filter: faFilter,
    download: faDownload,
    bell: faBell,
    'circle-check': faCircleCheck,
    'circle-xmark': faCircleXmark,
    'triangle-exclamation': faTriangleExclamation,
    'circle-info': faCircleInfo,
    'arrow-up': faArrowUp,
    'arrow-down': faArrowDown,
    'chart-line': faChartLine,
    building: faBuilding,
    'door-open': faDoorOpen,
    'user-plus': faUserPlus,
    users: faUsers,
    phone: faPhone,
    lock: faLock,
    clock: faClock,
    ellipsis: faEllipsis,
    moon: faMoon,
    sun: faSun,
    language: faLanguage,
};

defineProps({
    name: { type: String, required: true },
    label: { type: String, default: '' },
});
</script>

<template>
    <FontAwesomeIcon
        :icon="icons[name] ?? icons['circle-info']"
        :aria-hidden="label ? undefined : true"
        :aria-label="label || undefined"
        class="inline-block size-3.5 shrink-0"
    />
</template>
