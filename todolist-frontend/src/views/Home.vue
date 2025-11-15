<template>
  <div>
    <TaskList :tasks="tasks" />
  </div>
</template>

<script>
import TaskList from '../components/TaskList.vue';
import { mapState } from 'vuex';

export default {
  name: 'HomeView',
  components: { TaskList },
  computed: {
    ...mapState(['tasks']),
  }
}
</script>
