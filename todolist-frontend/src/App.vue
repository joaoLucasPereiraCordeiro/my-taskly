<template>
  <div>
    <!-- Loading spinner global -->
    <div v-if="isLoading" class="d-flex justify-content-center align-items-center min-vh-100">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
      </div>
    </div>

    <!-- Conteúdo principal -->
    <div v-else class="d-flex min-vh-100">
      <!-- NAVBAR DESKTOP -->
      <aside
        v-if="isAuthenticated && !isAdmin"
        class="p-4 position-fixed top-0 start-0 h-100 d-none d-md-flex flex-column justify-content-between border-secondary"
        style="width: 16rem;"
      >
        <div>
          <h2 class="h5 fw-bold mb-5 text-black">
            My Task<span class="highlight-cut">ly</span>
          </h2>
          <ul class="list-unstyled">
            <router-link to="/home" class="text-decoration-none fw-medium">
              <li class="custom-btn mb-3">
                <i class="fa-solid fa-calendar me-2"></i>Entrada
              </li>
            </router-link>
            <router-link to="/calendario" class="text-decoration-none fw-medium">
              <li class="custom-btn mb-3">
                <i class="fa-solid fa-calendar me-2"></i>Calendário
              </li>
            </router-link>
            <router-link to="/dashboard" class="text-decoration-none fw-medium">
              <li class="custom-btn mb-3">
                <i class="fas fa-chart-line me-2"></i>Dashboard
              </li>
            </router-link>
          </ul>
        </div>
        <div class="pt-5 d-flex flex-column">
          <li class="custom-btn mb-3">
            <a href="#" @click.prevent="handleLogout" class="text-decoration-none fw-bold">
              <i class="fas fa-right-from-bracket"></i> Sair
            </a>
          </li>
        </div>
      </aside>

      <!-- BOTÃO HAMBURGER MOBILE -->
      <button
        v-if="isAuthenticated && !isAdmin"
        class="mobile-hamburger d-md-none fs-4"
        type="button"
        @click="mobileMenuOpen = !mobileMenuOpen"
      >
        <i class="fa-solid fa-bars"></i>
      </button>

      <!-- MENU MOBILE -->
      <transition name="slide">
        <div v-if="mobileMenuOpen" class="mobile-menu d-flex flex-column">
          <h5 class="fw-bold mb-4">My Task<span class="highlight-cut">ly</span></h5>
          <ul class="list-unstyled flex-grow-1">
            <router-link to="/home" class="text-decoration-none fw-medium" @click="closeMobileMenu">
              <li class="custom-btn mb-3">
                <i class="fa-solid fa-calendar me-2"></i>Entrada
              </li>
            </router-link>
            <router-link to="/calendario" class="text-decoration-none fw-medium" @click="closeMobileMenu">
              <li class="custom-btn mb-3">
                <i class="fa-solid fa-calendar me-2"></i>Calendário
              </li>
            </router-link>
            <router-link to="/dashboard" class="text-decoration-none fw-medium" @click="closeMobileMenu">
              <li class="custom-btn mb-3">
                <i class="fas fa-chart-line me-2"></i>Dashboard
              </li>
            </router-link>
          </ul>
          <div>
            <a href="#" @click.prevent="handleLogout" class="text-decoration-none fw-bold">
              <li class="custom-btn mb-3">
                <i class="fas fa-right-from-bracket"></i> Sair
              </li>
            </a>
          </div>
        </div>
      </transition>

      <!-- CONTEÚDO PRINCIPAL -->
      <main
        class="flex-grow-1 overflow-auto bg-light"
        :style="{ marginLeft: isAuthenticated && !isAdmin ? '16rem' : '0' }"
        @click="closeMobileMenu"
      >
        <router-view :tasks="tasksFromStore" />
      </main>
    </div>

    <!-- Robo -->
    <RoboAssistant v-if="isAuthenticated && !isAdmin" />
  </div>
</template>

<script>
import { mapGetters, mapActions, mapState } from "vuex";
import RoboAssistant from "@/components/RoboAssistant.vue";

export default {
  components: { RoboAssistant },
  data() {
    return {
      mobileMenuOpen: false
    };
  },
  computed: {
    ...mapState(['tasks']),
    ...mapGetters(["isAuthenticated", "isLoading", "user"]),
    isAdmin() {
      return this.user && this.user.role === "admin";
    },
    tasksFromStore() {
      return this.tasks;
    }
  },
  methods: {
    ...mapActions(["checkAuth", "logout", "fetchTasks"]),

    async handleLogout() {
      await this.logout();
      this.$router.push("/login");
      this.mobileMenuOpen = false;
    },

    closeMobileMenu() {
      this.mobileMenuOpen = false;
    }
  },
  async created() {
    await this.checkAuth();
    if (this.isAuthenticated) {
      await this.fetchTasks(); // chamado APENAS uma vez
    }
  }
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Pacifico&display=swap');

/* RESPONSIVIDADE */
@media (max-width: 768px) {
  aside {
    display: none !important;
  }
  main {
    margin-left: 0 !important;
  }
}

/* ASIDE DESKTOP */
aside {
  background-color: #fff;
}

/* BOTÃO HAMBURGER */
.mobile-hamburger {
  position: fixed;
  top: 12px;
  left: 12px;
  z-index: 1060;
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: #111;
  font-size: 20px;
  padding: 0;
  box-shadow: none;
}

/* MENU MOBILE */
.mobile-menu {
  width: 290px;
  background: #fff;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  height: 100%;
  box-shadow: 2px 0 8px rgba(0,0,0,0.2);
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1060;
}

/* CUSTOM BTN */
.custom-btn {
  border-radius: 6px;
  cursor: pointer;
  padding: 5px 10px;
  transition: background-color 0.4s ease, color 0.4s ease, padding 0.3s ease;
  list-style: none;
  color: #000;
}

.custom-btn:hover {
  background-color: #ff0084;
  color: #fff;
  padding-left: 15px;
  font-weight: 500;
}

.custom-btn a {
  color: #000;
  transition: color 0.3s ease;
}

.custom-btn:hover a {
  color: #fff;
}

/* TÍTULO MY TASKLY */
div h2 {
  font-family: "Lexend", sans-serif;
}

.highlight-cut {
  position: relative;
  display: inline-block;
  z-index: 1;
}

.highlight-cut::before {
  content: "";
  position: absolute;
  top: 50%;
  left: 0;
  width: 27px;
  height: 50%;
  background-color: #ff0084;
  transform: translateY(-50%);
  z-index: -1;
  border-radius: 3px;
}

/* TRANSIÇÃO DO MENU MOBILE */
.slide-enter-active, .slide-leave-active {
  transition: all 0.3s ease;
}
.slide-enter-from, .slide-leave-to {
  opacity: 0;
  transform: translateX(-100%);
}
.slide-enter-to, .slide-leave-from {
  opacity: 1;
  transform: translateX(0);
}
</style>
