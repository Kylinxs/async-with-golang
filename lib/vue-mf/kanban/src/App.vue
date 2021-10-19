<script>
export default {
    name: 'App'
}
</script>
<script setup>
import { ref, onBeforeMount } from 'vue'
// import { Sidebar } from '@vue-mf/styleguide'
import KanbanBoard from './components/KanbanBoard.vue'
// import Sidemenu from './components/Sidemenu/Sidemenu.vue'
import store from './store'
// import kanban from './api/kanban'

const props = defineProps({
    customProps: {
        type: Object
    }
})

const boardId = ref(props.customProps.kanbanData.trackerId)
const loading = ref(false)

onBeforeMount(() => {
    //console.log(props.customProps.kanbanData)
    loading.value = false
    // Removes duplicate ids
    const cardsAllIds = props.customProps.kanbanData.cards.map(card => card.id)
    props.customProps.kanbanData.cards = [...new Set(cardsAllIds)].map(id => {
        return props.customProps.kanbanData.cards.find(card => card.id === id)
    })
    store.dispatch('initBoard', props.customProps.kanbanData)
    store.dispatch('setUser', props.customProps.kanbanData.user)
})

const setBoardId = (id) => {
    boardId.value = id
}
</script>

<template>
    <!-- <Sidebar>
        <template v-slot:sidebar-content>
            <Sidemenu @boardId="setBoardId"/>
        </template>
        <KanbanBoard :id="boardId" />
    </Sidebar> -->
    <KanbanBoard :id="boardId" :loading="loading" />
</template>
