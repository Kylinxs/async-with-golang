
<script>
export default {
    name: 'KanbanColumn'
}
</script>
<script setup>
import { ref, computed } from 'vue'
import { Field } from 'vee-validate'
import { useToast } from "vue-toastification"
import { Dropdown } from '@vue-mf/styleguide'
import ButtonAddCard from './Buttons/ButtonAddCard.vue'
import store from '../store'

const props = defineProps({
    title: {
        type: String,
        default: ''
    },
    limit: {
        type: Number
    },
    total: {
        type: Number
    },
    colId: {
        type: Number
    },
    rowValue: [Number, String],
    columnValue: [Number, String],
    cellId: {
        type: Number
    },
    rowIndex: {
        type: Number
    },
    colIndex: {
        type: Number
    }
});

const showEditField = ref(false)
const toast = useToast()

const isLimitExceeded = computed(() => {
    if (props.limit) {
        return props.total > props.limit
    } else {
        return false
    }
})

const handleTitleBlur = event => {
    showEditField.value = false

    if (event.target.value.length < 1) {
        toast.error(`This field must be at least 1 character`)
        return
    }

    store.dispatch('editColumnField', {
        id: props.colId,
        field: 'title',
        data: event.target.value
    })
}

const handleColorChange = (colorValue) => {
    store.dispatch('editColumnField', {
        id: props.colId,
        field: 'color',
        data: colorValue
    })
}

const handleEditClick = event => {
    showEditField.value = true
}
</script>

<template>
    <div class="kanban-column" :class="{'border border-danger': isLimitExceeded}">
        <!-- <div class="kanban-column-header mb-2" :style="{'background-color': store.getters.getColColor(props.colId)}"> -->
        <div class="kanban-column-header">
            <h6 class="d-flex justify-content-center align-items-center mb-0" :class="{'drag-handle-cell': false}">
                <span v-if="!showEditField" class="mr-2">{{ title }}</span>
                <Field
                    class="flex-grow-1 mr-1"
                    v-if="showEditField"
                    v-focus
                    v-autosize
                    as="textarea"
                    rows="1"
                    :value="title"
                    @blur="handleTitleBlur"
                    name="rowTitle"
                    type="text"
                    :rules="{ minLength: 1 }"
                />
            </h6>
            <span class="rounded p-1" :class="{'bg-danger text-light': isLimitExceeded}">{{ total }}{{ limit ? `/${limit}` : '' }}</span>
            <Dropdown v-if="false && rowIndex === 0" class="d-inline-block ml-2" variant="default" sm>
                <template v-slot:dropdown-button>
                    <i class="fas fa-ellipsis-h"></i>
                </template>
                <template v-slot:dropdown-menu>
                    <div class="px-2">
                        <span class="dropdown-item-text">List actions</span>
                        <div class="dropdown-divider"></div>
                        <ColorPicker :pureColor="store.getters.getColColor(props.colId)" useType="pure" format="hex" @pureColorChange="handleColorChange" />
                    </div>
                </template>
            </Dropdown>
        </div>
        <div class="kanban-column-body d-flex flex-column flex-grow-1">
            <div class="flex-grow-1">
                <slot />
            </div>
            <div v-if="colIndex === 0">
                <ButtonAddCard :cellId="cellId" :rowValue="rowValue" :columnValue="columnValue"></ButtonAddCard>
            </div>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.kanban-column {
    display: flex;
    flex-direction: column;
    min-width: 18rem;
    width: 18rem;
    flex-grow: 1;
    // padding: 10px;
    margin: 0 1px;
    // background-color: rgba(236, 239, 255, 0.5);
    // border-radius: 6px;

    &:first-child {
        margin-left: 0;
    }
    &:last-child {
        margin-right: 0;
    }

    .kanban-column-header {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 5px 5px 5px 10px;
        border-bottom: 1px solid #fff;
        // border-radius: 6px;
        background: rgba(219, 218, 241, 0.65);

        h6 span {
            font-size: 1.05rem;
        }
    }

    .kanban-column-body {
        background-color: rgba(246, 251, 255, 0.7);
    }
}
</style>