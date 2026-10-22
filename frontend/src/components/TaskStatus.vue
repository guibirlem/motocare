<script setup>defineProps({ busy: Boolean, error: String, notice: String })</script>
<template><p v-if="busy" role="status">Carregando…</p><p v-if="error" role="alert" class="error-notice">{{ error }}</p><p v-if="notice" role="status" class="demo-notice">{{ notice }}</p></template>
