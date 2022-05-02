<template>
    <button type="button" :class="['btn', `btn-${variant}`, {'btn-sm': sm}]">
        <slot />
    </button>
</template>

<script>
export default {
    name: 'Button',
    props: {
        variant: {
            type: String,
            required: false,
            default: 'primary',
        },
        sm: {
            type: Boolean
        }
    }
}
</script>

<style scoped>

</style>
