<template>
  <div class="container-notes bg-white rounded shadow-sm p-4">
    <h5 class="mb-3">Caderno de Anotações</h5>

    <!-- Campo para nova anotação -->
    <div class="input-group w-100">
      <textarea
        v-model="newNote"
        class="form-control"
        placeholder="Escreva sua anotação..."
        rows="0"
      ></textarea>
      <button class="custom-btn" @click="addNote">Adicionar</button>
    </div>

    <!-- Lista de anotações -->
    <div v-if="notes.length" class="notes-list hide-scrollbar">
      <div
        v-for="(note, index) in notes"
        :key="index"
        class="list-group-item d-flex justify-content-between align-items-start mb-2"
      >
        <!-- Exibição da anotação -->
        <div v-if="!note.editing" class="text-start flex-grow-1 me-2">
          <span v-html="note.text.replace(/\n/g, '<br>')"></span>
        </div>

        <!-- Edição da anotação -->
        <div v-else class="flex-grow-1 me-2">
          <textarea
            v-model="note.text"
            class="form-control"
            rows="3"
            @keyup.enter="saveEdit(note)"
          ></textarea>
        </div>

        <!-- Botões -->
        <div class="btn-group">
          <button
            v-if="!note.editing"
            class="btn border-0"
            @click="note.editing = true"
          >
            <i class="fa-solid fa-pen-to-square" style="color: #000000;"></i>
          </button>

          <button
            v-else
            class="btn border-0"
            @click="saveEdit(note)"
          >
            Salvar
          </button>

          <button
            class="btn border-0"
            @click="deleteNote(index)"
          >
            <i class="fa-solid fa-trash" style="color: #000000;"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Mensagem caso não tenha anotações -->
    <p v-else class="text-muted text-center">Nenhuma anotação ainda...</p>
  </div>
</template>

<script>
export default {
  name: "NotesPad",
  data() {
    return {
      newNote: "",
      notes: JSON.parse(localStorage.getItem("notes") || "[]"),
    };
  },
  methods: {
    addNote() {
      if (!this.newNote.trim()) return;
      this.notes.push({ text: this.newNote.trim(), editing: false });
      this.newNote = "";
      this.saveNotes();
    },
    saveEdit(note) {
      note.editing = false;
      this.saveNotes();
    },
    deleteNote(index) {
      this.notes.splice(index, 1);
      this.saveNotes();
    },
    saveNotes() {
      localStorage.setItem("notes", JSON.stringify(this.notes));
    },
  },
};
</script>

<style scoped>
.container-notes {
  width: 100%;
  max-height: 700px;
  text-align: center;
  display: flex;
  flex-direction: column;
}

.list-group-item {
  border: 1px solid #eee;
  border-radius: 6px;
  padding: 10px;
}

.list-group-item button {
  cursor: pointer;
}

.notes-list {
  flex-grow: 1;
  overflow-y: auto;
  max-height: 250px; /* altura rolável */
  padding: 20px 0;
}

.hide-scrollbar {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE 10+ */
}

.hide-scrollbar::-webkit-scrollbar {
  display: none; /* Chrome, Safari */
}

.custom-btn {
  padding: 6px 16px;
  border-radius: 6px;
  background-color: #ff0084;
  color: #fff;
  border: none;
  font-weight: 500;
  transition: background-color 0.2s;
}


input,
input:focus,
textarea,
textarea:focus,
select,
select:focus {
  outline: none !important;       /* tira contorno */
  box-shadow: none !important;    /* tira a sombra azul do Bootstrap */
  border-color: #ced4da !important; /* opcional: volta borda neutra */
  resize: none !important;
}


</style>
