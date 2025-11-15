<template>
  <BaseModal :title="mode === 'form' ? 'Criar Tarefa' : 'Criar por Voz'" @close="emitClose">
    <div class="d-flex flex-column gap-3">

      <!-- ===================== -->
      <!-- 📝 MODO FORMULÁRIO -->
      <!-- ===================== -->
      <template v-if="mode === 'form'">
        <!-- Título -->
        <input v-model="form.title" placeholder="Título" class="form-control" />

        <!-- Descrição -->
        <textarea v-model="form.description" placeholder="Descrição" class="form-control"></textarea>

        <!-- Subtarefas -->
        <div v-for="(subtask, index) in form.subtasks" :key="index" class="input-group input-group-sm">
          <input v-model="subtask.title" placeholder="Subtarefa" class="form-control" />
          <button class="btn btn-danger btn-sm" @click="removeSubtask(index)">Remover</button>
        </div>
        <div class="input-group input-group-sm">
          <input v-model="emptySubtaskTitle" placeholder="Adicionar Subtarefa" class="form-control" />
          <button class="btn-bottomless" @click="addSubtask">Adicionar</button>
        </div>

        <!-- Data -->
        <input type="date" v-model="form.due_date" class="form-control" />

        <!-- Loader -->
        <Loader :loading="loadingVoice" />

        <!-- Botões -->
        <div class="group-btn d-flex justify-content-end gap-2 mt-3">
          <button class="btn-bottomless" @click="switchToVoice">
            🎤 Criar por voz
          </button>
          <button class="custom-btn" @click="emitSubmit" :disabled="isSubmitting || loadingVoice">
            Criar
          </button>
          <button class="btn-bottomless" @click="emitClose">
            Cancelar
          </button>
        </div>
      </template>

      <!-- ===================== -->
      <!-- 🎙️ MODO VOZ -->
      <!-- ===================== -->
      <template v-else>
        <div class="text-center">
          <h5 class="fw-bold mb-3">Como criar por voz</h5>
          <p class="text-muted small mb-4">
            Fale algo como:<br>
            <em>"Ir ao mercado, amanhã às 18h, subtarefas arroz, tomate, carne..."</em><br><br>
            Eu vou preencher automaticamente o título, descrição, subtarefa e data pra você.
          </p>

          <div class="group-btn d-flex gap-3">

          <VoiceAssistant
            @voice-input="handleVoiceInput" 
            @listening-status="handleListening"
            @creating-task="handleCreatingTask"
          />

            <button class="btn-bottomless" @click="switchToForm">
              Voltar ao formulário
            </button>
        </div>

        </div>
      </template>
    </div>
  </BaseModal>
</template>

<script>
import BaseModal from '@/components/BaseModal.vue'
import VoiceAssistant from '@/components/VoiceAssistant.vue'
import Loader from '@/components/BaseLoader.vue'

export default {
  name: 'TaskCreate',
  components: { BaseModal, VoiceAssistant, Loader },
  props: {
    modelValue: { type: Boolean, required: true },
    newTask: { type: Object, required: true },
    isSubmitting: { type: Boolean, default: false },
  },
  emits: ['close', 'submit', 'voice-input', 'update:new-task', 'listening-status'],
  data() {
    return {
      mode: 'form', // 'form' | 'voice'
      emptySubtaskTitle: '',
      loadingVoice: false,
      initialTask: { title: '', description: '', due_date: '', subtasks: [] }
    }
  },
  computed: {
    form: {
      get() {
        if (!this.newTask.subtasks) this.newTask.subtasks = []
        return this.newTask
      },
      set(value) {
        this.$emit('update:new-task', value)
      },
    },
  },
  watch: {
    modelValue(newVal) {
      if (!newVal) this.resetForm()
    }
  },
  methods: {
    switchToVoice() {
      this.mode = 'voice'
    },
    switchToForm() {
      this.mode = 'form'
    },
    emitClose() { 
      this.resetForm()
      this.mode = 'form'
      this.$emit('close') 
    },
    emitSubmit() {
      if (this.emptySubtaskTitle.trim() !== '') {
        this.form.subtasks.push({ title: this.emptySubtaskTitle, completed: false })
        this.emptySubtaskTitle = ''
      }
      this.$emit('submit', { ...this.form })
      this.resetForm()
    },
    addSubtask() {
      if (this.emptySubtaskTitle.trim() !== '') {
        this.form.subtasks.push({ title: this.emptySubtaskTitle, completed: false })
        this.emptySubtaskTitle = ''
      }
    },
    removeSubtask(index) {
      this.form.subtasks.splice(index, 1)
    },
    handleVoiceInput(data) {
      this.form.title = data.title || ''
      this.form.description = data.description || ''
      this.form.due_date = data.due_date || ''
      this.form.subtasks = data.subtasks || []
      this.emptySubtaskTitle = ''
      this.loadingVoice = false
      this.mode = 'form'
    },
    handleListening(status) {
      this.$emit('listening-status', status)
    },
    handleCreatingTask(status) {
      this.loadingVoice = status
    },
    resetForm() {
      this.$emit('update:new-task', { ...this.initialTask })
      this.emptySubtaskTitle = ''
      this.loadingVoice = false
      this.mode = 'form'
    }
  },
}
</script>

<style scoped>
.btn-bottomless {
  padding: 6px 16px;
  border-radius: 6px;
  border: 1px solid #ccc;
  background-color: rgb(243, 240, 240);
  color: #000;
  font-weight: 500;
  cursor: pointer;
  transition: 0.2s;
}

.btn-bottomless:hover {
  background-color: #e9e9e9;
}

.custom-btn {
  background-color: #ff0084;
  color: white;
  border: none;
  padding: 6px 16px;
  border-radius: 6px;
  font-weight: 500;
}

.custom-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

@media (max-width: 430px) {
  .custom-btn, .btn-bottomless{
    width: 100%;
    height: 50%;
  }

  .group-btn{
    flex-direction: column;
  }
}

</style>
