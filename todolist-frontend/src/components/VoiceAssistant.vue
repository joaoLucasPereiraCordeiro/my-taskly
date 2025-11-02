<template>
  <div>
    <!-- Botão de voz -->
    <button 
      class="custom-btn"
      @click="startListening"
      :disabled="recognizing"
    >
      <i class="fa-solid fa-microphone pe-2" style="color:#fff"></i>
      {{ recognizing ? 'Ouvindo...' : 'Falar agora' }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const emit = defineEmits(['voice-input', 'listening-status', 'creating-task'])

const recognizing = ref(false)

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition
let recognition

if (SpeechRecognition) {
  recognition = new SpeechRecognition()
  recognition.lang = 'pt-BR'
  recognition.interimResults = false
  recognition.maxAlternatives = 1
  recognition.continuous = false

  recognition.onresult = async (event) => {
    const transcript = event.results[0][0].transcript.trim()
    recognizing.value = false
    emit('listening-status', false)

    // Avisar ao App.vue que estamos criando
    emit('creating-task', true)

    try {
      const response = await axios.post('/api/voice-task', { text: transcript })
      const data = response.data

      emit('voice-input', {
        title: capitalize(data.title || ''),
        description: data.description || '',
        due_date: data.due_date || '',
        subtasks: (data.subtasks || []).map(t => ({
          id: Date.now() + Math.random(),
          title: capitalize(t),
          completed: false
        }))
      })
    } catch (error) {
      alert('❌ Não foi possível interpretar a tarefa. Tente novamente.')
    } finally {
      emit('creating-task', false)
    }
  }

  recognition.onerror = () => {
    recognizing.value = false
    emit('listening-status', false)
  }

  recognition.onend = () => {
    recognizing.value = false
    emit('listening-status', false)
  }
}

function capitalize(text) {
  if (!text) return ''
  return text.charAt(0).toUpperCase() + text.slice(1)
}

async function startListening() {
  if (!recognition) {
    alert('Seu navegador não suporta reconhecimento de voz.')
    return
  }
  try {
    await navigator.mediaDevices.getUserMedia({ audio: true })
    recognition.start()
    recognizing.value = true
    emit('listening-status', true)
  } catch (error) {
    alert('Permissão para o microfone negada ou indisponível.')
    emit('listening-status', false)
  }
}
</script>

<style scoped>
.custom-btn {
  background-color: #ff0084;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 10px 16px;
  font-weight: 500;
  transition: 0.3s ease;
}

.custom-btn:hover {
  background-color: #e60075;
}
</style>
