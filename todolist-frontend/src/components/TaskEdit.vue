<template>
  <BaseModal :title="'Editar Tarefa'" @close="$emit('close')">
    <!-- Corpo do modal com o formulário -->
    <form @submit.prevent="submitEdit">
      <!-- Título -->
      <div class="mb-3">
        <label class="form-label">Título</label>
        <input v-model="localTask.title" type="text" class="form-control" />
      </div>

      <!-- Descrição -->
      <div class="mb-3">
        <label class="form-label">Descrição</label>
        <textarea v-model="localTask.description" class="form-control"></textarea>
      </div>

      <!-- Subtarefas -->
      <div class="mb-3 d-flex flex-column">
        <label class="form-label">Subtarefas</label>
        <div
          v-for="(subtask, index) in localTask.subtasks"
          :key="index"
          class="d-flex align-items-center gap-2 mb-2"
        >
          <input
            v-model="subtask.title"
            type="text"
            class="form-control flex-grow-1"
            placeholder="Título da subtarefa"
          />
          <button
            @click.prevent="removeSubtask(index)"
            type="button"
            class="btn btn-sm"
          >
            <i class="fa-solid fa-trash" style="color: #000000;"></i>
          </button>
        </div>

        <button
          @click.prevent="addSubtask"
          type="button"
          class="btn-bottomless"
          style="width: 12rem;"
        >
          Adicionar Subtarefa
        </button>
      </div>

      <!-- Prazo -->
      <div class="mb-3">
        <label class="form-label">Prazo</label>
        <input v-model="localTask.due_date" type="date" class="form-control" />
      </div>

      <!-- Botões -->
      <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" @click="$emit('close')" class="btn-bottomless">
          Cancelar
        </button>
        <button type="submit" class="custom-btn">Salvar</button>
      </div>
    </form>
  </BaseModal>
</template>

<script>
import BaseModal from '@/components/BaseModal.vue'

export default {
  name: 'TaskEdit',
  components: { BaseModal },
  props: {
    modelValue: Object,
  },
  data() {
    return {
      localTask: JSON.parse(JSON.stringify(this.modelValue || {})),
    }
  },
  watch: {
    modelValue(newVal) {
      this.localTask = JSON.parse(JSON.stringify(newVal || {}))
    },
  },
  methods: {
    submitEdit() {
      this.$emit('submit', this.localTask)
    },
    addSubtask() {
      if (!this.localTask.subtasks) this.localTask.subtasks = []
      this.localTask.subtasks.push({ title: '', completed: false })
    },
    removeSubtask(index) {
      this.localTask.subtasks.splice(index, 1)
    },
  },
}
</script>

<style scoped>
.custom-btn {
  padding: 6px 16px;
  border-radius: 6px;
  background-color: #ff0084;
  color: #fff;
  border: none;
  font-weight: 500;
}

.btn-bottomless {
  padding: 6px 16px;
  border-radius: 6px;
  border: 1px solid #ccc;
  background-color: rgb(243, 240, 240);
  color: #000;
  font-weight: 500;
  cursor: pointer;
}

input,
input:focus,
textarea,
textarea:focus,
select,
select:focus {
  outline: none !important;
  box-shadow: none !important;
  border-color: #ced4da !important;
  resize: none !important;
}
</style>
