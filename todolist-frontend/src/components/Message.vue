<template>
    <BaseModal v-if="visible" @close="close" title="Mensagem">
      <p>{{ message }}</p>
    </BaseModal>
  </template>
  
  <script>
  import BaseModal from '@/components/BaseModal.vue';

export default {
  name: 'Message',
  components: { BaseModal },
    props: {
      message: String,
    },
    data() {
      return {
        visible: false,
        timer: null,
      }
    },
    watch: {
  message(newVal) {
    this.visible = !!newVal;
  }
},

    methods: {
      close() {
        this.visible = false
        this.$emit('update:message', '')  // para limpar no pai
      }
    },
    beforeUnmount() {
      if (this.timer) clearTimeout(this.timer)
    }
  }
  </script>
  