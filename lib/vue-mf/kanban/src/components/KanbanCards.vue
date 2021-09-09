
<script>
export default {
    name: 'KanbanCards'
}
</script>
<script setup>
import { ref, computed } from 'vue'
import { Button } from '@vue-mf/styleguide'
import KanbanCard from './KanbanCard.vue'
import FormEditField from './Forms/FormEditField.vue'
import draggable from 'vuedraggable/src/vuedraggable'
import { useToast } from 'vue-toastification'
import kanban from '../api/kanban'
import store from '../store'
import defineAbilityFor from '../auth/defineAbility'
import { subject } from "@casl/ability"

const props = defineProps({
    cardIds: {
        type: Array,
        default() {
            return []
        }
    },
    rowId: {
        type: Number
    },
    rowValue: [Number, String],
    columnValue: [Number, String],
    cellId: {
        type: Number
    }
})

const emit = defineEmits(['editCard'])

const toast = useToast()
const dragging = ref(false)

const getCards = computed(() => store.getters.getCards(props.cardIds))
const getTrackerItemEditLink = computed(() => id => `tiki-tracker-update_item?trackerId=${store.getters.getTrackerId}&itemId=${id}`)
const getTrackerItemLink = computed(() => id => `tiki-view_tracker_item.php?itemId=${id}`)


const startDragging = () => dragging.value = true
const endDragging = () => setTimeout(() => dragging.value = false, 0)

const checkMove = (event) => {
    const rules = store.getters.getRules;
    //console.log(rules);
    const ability = defineAbilityFor(rules);
    //console.log(ability);
    const id = parseInt(event.draggedContext.element.id);
    //const rule = ability.relevantRuleFor('update', subject("Tracker_Item", { itemId: id }), store.getters.getXaxisField);
    //console.log(rule);
    const canUpdate = ability.can('update', subject('Tracker_Item', { itemId: id }), store.getters.getXaxisField)
    //console.log("canUpdate?", canUpdate)
    return canUpdate;
}

const handleEditCard = element => {
    if (!dragging.value) emit('editCard', element)
}

const handleChange = (event) => {
    if (event.moved) {
        store.dispatch('moveCard', {
            oldIndex: event.moved.oldIndex,
            newIndex: event.moved.newIndex,
            element: event.moved.element,
            rowId: props.rowId,
            cellId: props.cellId
        })
        let sortOrder = store.getters.getCard(event.moved.element.id).sortOrder
        setItem(event.moved.element.id, sortOrder)
    } else if (event.added) {
        store.dispatch('addCard', {
            newIndex: event.added.newIndex,
            element: event.added.element,
            rowId: props.rowId,
            cellId: props.cellId
        })
        let sortOrder = store.getters.getCard(event.added.element.id).sortOrder
        setItem(event.added.element.id, sortOrder)
    } else if (event.removed) {
        store.dispatch('removeCard', {
            oldIndex: event.removed.oldIndex,
            element: event.removed.element,
            rowId: props.rowId,
            cellId: props.cellId
        })
    }
    // setItems(store.getters.getCell(props.cellId).cards)
}

const setItems = ids => {
    ids.forEach((id, index) => {
        setItem(id, index)
    })
}

const setItem = (itemId, newIndex) => {
    kanban.setItem(
        { trackerId: store.getters.getTrackerId, itemId: itemId },
        { fields: {
                [store.getters.getSwimlaneField]: props.rowValue,
                [store.getters.getXaxisField]: props.columnValue,
                [store.getters.getYaxisField]: newIndex
            },
        }
    )
    .then(res => {
        // toast.success(`Success! Item moved.`)
    })
    .catch(err => {
        if (!err.response) return
        const { code, errortitle, message } = err.response.data
        const msg = `Code: ${code} - ${message}`
        toast.error(msg)
    })
}
</script>

<template>
    <draggable
        :list="getCards"
        group="cards"
        item-key="id"
        class="container-cards"
        chosenClass="chosen-card"
        ghostClass="ghost-card"
        dragClass="dragging-card"
        filter="textarea"
        :preventOnFilter="false"
        @change="handleChange"
        @start="startDragging"
        @end="endDragging"
        :move="checkMove"
        :fallbackTolerance="3"
        :forceFallback="true"
        :animation="150"
    >
        <template #item="{ element }">
            <KanbanCard>
                <div v-if="false">
                    <span class="badge badge-light">{{ element.sortOrder }}</span>
                </div>
                <template v-slot:menu>
                    <div class="card-menu">
                        <a class="p-1 mr-3" :href="getTrackerItemLink(element.id)" target="_blank"><i class="fas fa-link"></i></a>
                        <a class="p-1 mr-1" :href="getTrackerItemEditLink(element.id)" target="_blank"><i class="fas fa-edit"></i></a>
                        <!-- <Button class="d-inline-block" variant="default" sm @click="handleEditCard(element)"> -->
                    </div>
                </template>
                <template v-slot:title>
                    <FormEditField :title="element.title" :id="element.id"></FormEditField>
                </template>
                <template v-slot:text>
                    <div v-if="element.description" class="mt-1">
                        {{ element.description.substring(0, 115) }}
                    </div>
                </template>
                <div v-if="false" class="d-flex justify-content-end">
                    <div class="kanban-avatar">
                        <span>NA</span>
                        <!-- <img src="..." class="rounded-circle" alt="..."> -->
                    </div>
                </div>
            </KanbanCard>
        </template>
    </draggable>
</template>

<style lang="scss" scoped>
.container-cards {
    flex-grow: 1;
    position: relative;
    max-height: 800px;
    padding: 10px;
}

.dragging-card {
    opacity: 1 !important;

    &.kanban-card {
        transform: rotate(4deg);
    }
}

.card-menu {
    a {
        background-color: rgba(255, 255, 255, 0.75);
        color: #383838;
        font-size: 1.5rem;

        &:hover {
            color: #007bff;
        }
    }
}

:deep(.kanban-card) {
    cursor: pointer;
}

.ghost-card {
    position: relative;

    &.kanban-card {
        &::after {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            background-color: #e8e9f3;
            border-radius: 8px;
        }
    }
}
</style>