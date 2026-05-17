<template>
  <div class="max-w-6xl mx-auto flex flex-col md:flex-row gap-6">
    <!-- Kolom Editor -->
    <div class="flex-1 editor-container border-2 border-gray-200 rounded-lg p-6 bg-white shadow-sm">
      <div class="flex justify-between items-center mb-4 border-b pb-4">
        <h2 class="text-xl font-bold text-gray-800">Editor Dokumen</h2>
        <button @click="saveVersion" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium shadow-sm transition-colors cursor-pointer flex items-center gap-2">
          💾 <span>Simpan Versi</span>
        </button>
      </div>
      <editor-content :editor="editor" class="prose max-w-none" />
    </div>

    <!-- Kolom Riwayat Versi -->
    <div class="w-full md:w-80 bg-gray-50 border-2 border-gray-200 rounded-lg p-6 h-fit shadow-sm">
      <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
        <span>⏱️</span> Riwayat Versi
      </h3>
      <div v-if="versions.length === 0" class="text-sm text-gray-500 italic text-center py-4">Belum ada versi tersimpan.</div>
      <ul class="space-y-3">
        <li v-for="v in versions" :key="v.id" class="bg-white p-4 rounded-md border border-gray-200 shadow-sm hover:border-blue-300 transition-colors flex flex-col gap-3">
          <div>
            <div class="font-semibold text-gray-700">{{ v.version_name }}</div>
            <div class="text-xs text-gray-500 mt-1">Oleh: <span class="font-medium text-blue-600">{{ v.author_name || 'Anonim' }}</span></div>
            <div class="text-xs text-gray-400 mt-1">{{ new Date(v.created_at).toLocaleString('id-ID') }}</div>
          </div>
          <button @click="restoreVersion(v.id)" class="w-full text-sm bg-yellow-100 text-yellow-700 hover:bg-yellow-200 py-1.5 rounded-md font-medium transition-colors flex items-center justify-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
            Kembalikan (Restore)
          </button>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Collaboration from '@tiptap/extension-collaboration'
import CollaborationCursor from '@tiptap/extension-collaboration-cursor'
import { HocuspocusProvider } from '@hocuspocus/provider'
import * as Y from 'yjs'

// Menerima prop 'docName', 'userName', dan 'userColor' dari blade
const props = defineProps({
  docName: {
    type: String,
    required: true
  },
  userName: {
    type: String,
    required: true
  },
  userColor: {
    type: String,
    required: true
  }
})

// 1. Inisialisasi Yjs Document
const ydoc = new Y.Doc()

// 2. Koneksikan ke server Hocuspocus
const provider = new HocuspocusProvider({
  url: 'ws://127.0.0.1:1234',
  name: props.docName, // Menggunakan nama dokumen dinamis
  document: ydoc,
})

const editor = useEditor({
  extensions: [
    StarterKit.configure({
      history: false,
    }),
    Collaboration.configure({
      document: ydoc,
    }),
    // Menambahkan ekstensi Kursor (Live Cursor)
    CollaborationCursor.configure({
      provider: provider,
      user: {
        name: props.userName,
        color: props.userColor,
      },
    }),
  ],
})

// === Fitur Riwayat Versi ===
const versions = ref([])

// 1. Mengambil riwayat versi dari database Laravel
const fetchVersions = async () => {
  try {
    const response = await axios.get(`/api/versions/${props.docName}`)
    versions.value = response.data
  } catch (error) {
    console.error("Gagal mengambil riwayat versi:", error)
  }
}

// 2. Mengirim perintah Simpan Versi ke API Laravel
const saveVersion = async () => {
  const name = prompt("Masukkan nama untuk versi ini (contoh: Draft 1, Final):")
  if (!name) return

  // Kita akan mengambil isi HTML saat ini untuk disimpan sebagai string
  const currentContent = editor.value.getHTML()

  try {
    await axios.post('/api/versions', {
      document_name: props.docName,
      version_name: name,
      data: currentContent
    })
    
    // Refresh daftar versi di UI
    fetchVersions()
    alert('Versi berhasil disimpan!')
  } catch (error) {
    console.error("Gagal menyimpan versi:", error)
    alert('Gagal menyimpan versi.')
  }
}

// 3. Mengembalikan dokumen ke versi lama (Restore)
const restoreVersion = async (versionId) => {
  const confirmRestore = confirm("Apakah kamu yakin ingin mengembalikan dokumen ke versi ini? Perubahan saat ini akan tertimpa.")
  if (!confirmRestore) return

  try {
    // Ambil data (HTML) dari versi lama
    const response = await axios.get(`/api/versions/data/${versionId}`)
    const oldContent = response.data.data

    if (oldContent) {
      // Tiptap otomatis akan menghitung selisih (diff) dan mengirimkan 
      // perintah Hapus/Tambah via Yjs ke semua client yang terhubung!
      editor.value.commands.setContent(oldContent)
      alert('Dokumen berhasil dikembalikan!')
    } else {
      alert('Gagal: Data versi lama tidak valid atau tidak bisa dibaca (Mungkin ini versi lama format biner).')
    }
  } catch (error) {
    console.error("Gagal merestore versi:", error)
    alert('Terjadi kesalahan saat memulihkan versi.')
  }
}

onMounted(() => {
  fetchVersions()
})

onBeforeUnmount(() => {
  provider.destroy()
  if (editor.value) {
    editor.value.destroy()
  }
})
</script>

<style>
/* Tiptap styles */
.ProseMirror {
  min-height: 300px;
  outline: none;
}
.ProseMirror p.is-editor-empty:first-child::before {
  color: #adb5bd;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

/* --- Styling untuk Live Cursor (Kursor Orang Lain) --- */
.collaboration-cursor__caret {
  border-left: 1px solid #0D0D0D;
  border-right: 1px solid #0D0D0D;
  margin-left: -1px;
  margin-right: -1px;
  pointer-events: none;
  position: relative;
  word-break: normal;
}

/* Teks nama pengguna yang muncul di atas kursor */
.collaboration-cursor__label {
  border-radius: 3px 3px 3px 0;
  color: #1a1a1a;
  font-size: 12px;
  font-style: normal;
  font-weight: 600;
  left: -1px;
  line-height: normal;
  padding: 0.1rem 0.3rem;
  position: absolute;
  top: -1.4em;
  user-select: none;
  white-space: nowrap;
}
</style>
