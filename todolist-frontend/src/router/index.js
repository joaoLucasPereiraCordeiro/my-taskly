import { createRouter, createWebHistory } from "vue-router";
import Home from "@/views/Home.vue";
import Calendar from "@/views/Calendar.vue";
import Dashboard from "@/views/Dashboard.vue";
import Login from "@/views/Login.vue";
import Register from "@/views/Register.vue";
import Admin from "@/views/Admin.vue"; // Nova view para admin
import store from "@/services/store"; 

const routes = [
  { path: "/", redirect: "/login" },
  { path: "/login", name: "Login", component: Login },
  { path: "/register", name: "Register", component: Register },

  // ROTAS PROTEGIDAS
  {
    path: "/home",
    name: "Home",
    component: Home,
    meta: { requiresAuth: true, role: "user" },
  },
  {
    path: "/calendario",
    name: "Calendar",
    component: Calendar,
    meta: { requiresAuth: true, role: "user" },
  },
  {
    path: "/dashboard",
    name: "Dashboard",
    component: Dashboard,
    meta: { requiresAuth: true, role: "user" },
  },

  // ROTA ADMIN
  {
    path: "/admin",
    name: "Admin",
    component: Admin,
    meta: { requiresAuth: true, role: "admin" },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = store.getters.isAuthenticated;
  const user = store.getters.user;

  // Se a rota exige login e não está autenticado → manda pro login
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next("/login");
  }

  // Se já está logado e tenta acessar /login ou /register → redireciona pela role
  if (isAuthenticated && (to.path === "/login" || to.path === "/register")) {
    if (user?.role === "admin") {
      return next("/admin");
    } else {
      return next("/home");
    }
  }

  // Verifica role apenas se a rota exigir uma
  if (to.meta.role) {
    // caso o user ainda não tenha sido carregado do store → deixa passar
    if (!user) {
      return next();
    }

    // se carregou e não tem a role correta → redireciona
    if (user.role !== to.meta.role) {
      return next(user.role === "admin" ? "/admin" : "/home");
    }
  }

  next();
});


export default router;
