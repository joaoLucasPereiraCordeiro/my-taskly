<template>
  <div class="robo-container" v-if="falaVisivel">
    <div class="robo-message">
      <p>{{ fala }}</p>
      <button class="close-btn" @click="fecharMensagem">×</button>
    </div>

    <div class="robo-avatar"><img src="/img/robo.png" alt="robo"></div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "RoboAssistant",
  data() {
    return {
      fala: "",
      falaVisivel: false,
      timeoutMensagem: null
    };
  },
  methods: {
    async carregarDados() {
      try {
        const response = await axios.get("/api/assistant/message");
        this.fala = response.data.message;
        this.mostrarMensagem();
      } catch (error) {
        console.error("Erro ao carregar fala do assistente:", error);
        this.fala = "Erro ao buscar dados, tente novamente mais tarde.";
        this.mostrarMensagem();
      }
    },
    mostrarMensagem() {
      this.falaVisivel = true;

      // Garante que o timeout seja limpo antes de criar outro
      if (this.timeoutMensagem) {
        clearTimeout(this.timeoutMensagem);
      }

      // Esconde após 10 segundos
      this.timeoutMensagem = setTimeout(() => {
        this.falaVisivel = false;
      }, 10000);
    },
    fecharMensagem() {
      this.falaVisivel = false;
      if (this.timeoutMensagem) {
        clearTimeout(this.timeoutMensagem);
      }
    }
  },
  mounted() {
    // Carrega apenas uma vez ao entrar
    this.carregarDados();
  },
  beforeUnmount() {
    // Limpa o timeout ao desmontar
    if (this.timeoutMensagem) clearTimeout(this.timeoutMensagem);
  }
};
</script>

<style scoped>
.robo-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 9999;
}

.robo-avatar {
  width: 70px;
  height: 70px;
  background: #ff0084;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  color: white;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.robo-avatar img {
  width: 50px;
}

.robo-message {
  background: white;
  padding: 12px 16px;
  border-radius: 12px;
  max-width: 280px;
  font-size: 14px;
  color: #333;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  position: relative;
}

.robo-message::after {
  content: "";
  position: absolute;
  right: -10px;
  bottom: 20px;
  border-width: 8px;
  border-style: solid;
  border-color: transparent transparent transparent white;
}

.close-btn {
  position: absolute;
  top: 6px;
  right: 6px;
  border: none;
  background: transparent;
  font-size: 16px;
  cursor: pointer;
  color: #666;
}

.close-btn:hover {
  color: #ff0084;
}
</style>
