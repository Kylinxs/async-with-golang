
<template>
    <div>
        <select @change="_onChange" :value="value">
            <option value="1">true</option>
            <option value="0">false</option>
        </select>
    </div>
</template>

<script>
    export default {
        name: 'bool-argument',
        methods: {
            _onChange({target: {value: newValue}})
            {
                this.$emit('change', newValue);
            },
        },
        props: {
            value: {
                type: null,
                required: true,
            },
        }
    }
</script>