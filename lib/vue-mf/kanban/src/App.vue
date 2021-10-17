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
// import kanban fr