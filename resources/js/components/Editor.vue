<template>
  <div class="editor-container border-2 border-gray-200 rounded-lg p-6 bg-white shadow-sm">
    <editor-content :editor="editor" class="prose max-w-none" />
  </div>
</template>

<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Collaboration from '@tiptap/extension-collaboration'
import CollaborationCursor from '@tiptap/extension-collaboration-cursor'
import { HocuspocusProvider } from '@hocuspocus/provider'
import * as Y from 'yjs'

// Fungsi untuk membuat warna dan nama acak (karena kita belum punya sistem Login)
const getRandomColor = () => {
  const colors = ['#958DF1', '#F98181', '#FBCE76', '#8AE234', '#729FCF', '#AD7FA8']
  return colors[Math.floor(Math.random() * colors.length)]
}
const getRandomName = () => {
  const names = ['Anonim Kelinci', 'Anonim Kucing', 'Anonim Beruang', 'Anonim Rubah', 'Anonim Panda']
  return names[Math.floor(Math.random() * names.length)]
}

// 1. Inisialisasi Yjs Document
const ydoc = new Y.Doc()

// 2. Koneksikan ke server Hocuspocus
const provider = new HocuspocusProvider({
  url: 'ws://127.0.0.1:1234',
  name: 'dokumen-rahasia',
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
        name: getRandomName(),
        color: getRandomColor(),
      },
    }),
  ],
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
