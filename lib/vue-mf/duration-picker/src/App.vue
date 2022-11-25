
<script>
export default {
    name: 'App'
}
</script>
<script setup>
import { provide } from 'vue'
import DurationPicker from './components/DurationPicker.vue'
import createStore from './store';

const props = defineProps({
    durationData: {
        type: Object,
        default: () => {
            return {
                "inputId": null,
                "inputName": "ins_",
                "draft": null,
                "value": 0,
                "units": [
                    "hours",
                    "minutes",
                    "seconds"
                ],
                "chronometer": 1
            }
        }
    }
})

provide('store', createStore());
</script>

<template>
    <DurationPicker :durationData="durationData" />
</template>