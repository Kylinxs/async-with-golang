<template>
    <div class="card">
        <div class="card-body">
            <h5 v-if="title" class="card-title">{{title}}</h5>
            <p class="card-text">
                <slot />
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Card',
    props: {
        title: {
            type: String,
            required: false,
            default: '',
        }
    }
}
</script>

<style scoped>

</style>
