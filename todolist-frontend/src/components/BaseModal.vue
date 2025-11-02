<template>
  <div
    class="modal-overlay"
    @click.self="$emit('close')"
  >
    <div class="modal-dialog-custom" @click.stop>
      <div class="modal-content">
        <div class="modal-header d-flex justify-content-between align-items-center w-100">
          <slot name="header">
            <h5 class="modal-title mb-0 ps-3">{{ title }}</h5>
          </slot>

          <button
            type="button"
            class="btn-close-custom"
            aria-label="Fechar"
            @click="$emit('close')"
          >
            <i class="fa-solid fa-xmark" style="color: #000000;"></i>
          </button>
        </div>

        <div class="modal-content-wrapper">
          <div class="modal-content-inner" @click.stop>
            <slot />
          </div>
        </div>

        <div class="modal-footer" v-if="$slots.footer">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseModal',
  props: {
    title: String
  }
};
</script>

<style scoped>
/* Overlay do modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  z-index: 1050;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1rem;
}

/* Caixa principal do modal */
.modal-dialog-custom {
  width: 100%;
  padding: 10px;
  max-width: 600px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  position: relative;
}

/* Conteúdo interno */
.modal-content-wrapper {
  padding: 1.5rem;
}

/* Título */
.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
}

/* Botão de fechar */
.btn-close-custom {
  color: black;
  font-size: 1.7rem;
  background: transparent;
  border: none;
  font-weight: 600;
  cursor: pointer;
  padding: 0 8px;
}

/* Dropdown visível acima do modal */
.dropdown-menu {
  z-index: 1055 !important;
}
</style>
