<template>
  <div id="editor-root">

    <!-- ===== HEADER ===== -->
    <header id="editor-header">
      <div class="hdr-left">
        <a href="/" class="back-btn" title="Dashboard">
          <svg viewBox="0 0 24 24" fill="#4285F4"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
        </a>
        <div>
          <div class="doc-title">{{ docName }}</div>
          <div class="menu-bar">
            <button>File</button><button>Edit</button>
            <button>View</button><button>Insert</button><button>Format</button>
          </div>
        </div>
      </div>

      <div class="hdr-right">
        <span class="conn-badge" :class="connected ? 'ok' : 'err'">
          {{ connected ? '● Terhubung' : '○ Offline' }}
        </span>
        <div class="user-pills">
          <div v-for="(u, sid) in activeUsers" :key="sid"
            class="pill" :style="{ background: u.color }" :title="u.name">
            {{ u.name[0].toUpperCase() }}
          </div>
        </div>
        <button @click="toggleHistory" class="ico-btn" title="Riwayat Versi">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </button>
        <button @click="saveVersion" class="save-btn">💾 Simpan Versi</button>
        <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=random`"
          class="uavatar" :title="userName" />
      </div>
    </header>

    <!-- ===== QUILL WRAPPER (toolbar auto-created di sini oleh Quill) ===== -->
    <div id="quill-wrap">
      <!-- Quill Snow akan membuat .ql-toolbar di sini, lalu .ql-container -->
      <div id="quill-mount"></div>
    </div>

    <!-- ===== VERSION HISTORY ===== -->
    <transition name="slide">
      <div v-if="showHistory" id="history-panel">
        <div class="hp-head">
          <span>Riwayat Versi</span>
          <button @click="toggleHistory" class="ico-btn">✕</button>
        </div>
        <div class="hp-body">
          <p v-if="!versions.length" class="empty">Belum ada versi.</p>
          <ul v-else>
            <li v-for="v in versions" :key="v.id" class="vi">
              <div class="vi-dot"></div>
              <div class="vi-date">{{ fmtDate(v.created_at) }}</div>
              <div class="vi-name">{{ v.version_name }}</div>
              <div class="vi-author">
                <span class="vi-av">{{ (v.author_name||'A')[0] }}</span>
                {{ v.author_name || 'Anonim' }}
              </div>
              <button @click.stop="restoreVersion(v.id)" class="vi-restore">Pulihkan</button>
            </li>
          </ul>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { io } from 'socket.io-client'
import Quill from 'quill'
import QuillCursors from 'quill-cursors'
// JANGAN import CSS di sini — pindah ke <style> block

const props = defineProps({
  docName:   { type: String, required: true },
  userName:  { type: String, required: true },
  userColor: { type: String, required: true }
})

// ---- state ----
let quill      = null
let socket     = null
let cursors    = null
let myId       = null
let saveTimer  = null

const versions    = ref([])
const showHistory = ref(false)
const activeUsers = ref({})
const connected   = ref(false)

// ---- helpers ----
const fmtDate = (d) => new Date(d).toLocaleString('id-ID', { day:'numeric', month:'short', hour:'2-digit', minute:'2-digit' })

// ---- versions ----
const fetchVersions = async () => {
  try { versions.value = (await axios.get(`/api/versions/${props.docName}`)).data }
  catch (e) { console.error(e.message) }
}
const toggleHistory = () => { showHistory.value = !showHistory.value; if (showHistory.value) fetchVersions() }
const saveVersion = async () => {
  const name = prompt('Nama versi:'); if (!name || !quill) return
  try {
    await axios.post(`/api/versions/${props.docName}`, { version_name: name, data: JSON.stringify(quill.getContents()) })
    fetchVersions(); showHistory.value = true; alert('Versi disimpan!')
  } catch { alert('Gagal menyimpan.') }
}
const restoreVersion = async (id) => {
  if (!confirm('Pulihkan versi ini?') || !quill) return
  try {
    const r = await axios.get(`/api/versions/data/${id}`)
    if (r.data.data) { try { quill.setContents(JSON.parse(r.data.data), 'user') } catch { quill.setText(r.data.data, 'user') } }
  } catch { alert('Gagal memulihkan.') }
}

// ---- debounced save to DB ----
const scheduleSave = () => {
  clearTimeout(saveTimer)
  saveTimer = setTimeout(() => {
    if (quill && socket) socket.emit('save-document', { documentName: props.docName, content: JSON.stringify(quill.getContents()) })
  }, 800)
}

// ---- INIT ----
onMounted(() => {
  try {
  // Register cursor module (safe to call multiple times)
  try { Quill.register('modules/cursors', QuillCursors) } catch {}

  // =============================================
  // QUILL INIT — biarkan Snow buat toolbar sendiri
  // Jangan pakai container selector untuk toolbar!
  // =============================================
  quill = new Quill('#quill-mount', {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, false] }, { font: [] }, { size: [] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ color: [] }, { background: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }, { indent: '-1' }, { indent: '+1' }],
        [{ align: [] }, 'link', 'clean']
      ],
      cursors: {
        transformOnTextChange: true,
        hideDelayMs: 3000,
        hideSpeedMs: 400,
      }
    },
    placeholder: 'Mulai mengetik di sini...',
  })

  cursors = quill.getModule('cursors')

  // =============================================
  // SOCKET.IO
  // =============================================
  socket = io('http://localhost:3000', { transports: ['websocket'] })

  socket.on('connect', () => {
    myId = socket.id
    connected.value = true
    socket.emit('join-document', props.docName)
  })
  socket.on('disconnect', () => { connected.value = false })
  socket.on('connect_error', () => { connected.value = false })

  // Load isi dokumen dari server
  socket.on('load-document', (content) => {
    if (!content || !quill) return
    try { quill.setContents(JSON.parse(content), 'silent') }
    catch { quill.setText(String(content), 'silent') }
  })

  // Terima perubahan dari user lain
  socket.on('receive-changes', (delta) => {
    if (quill) quill.updateContents(delta, 'silent')
  })

  // Kirim perubahan ke user lain saat mengetik
  quill.on('text-change', (delta, _, source) => {
    if (source !== 'user' || !socket?.connected) return
    socket.emit('send-changes', { documentName: props.docName, delta })
    scheduleSave()
  })

  // =============================================
  // LIVE CURSOR — kirim posisi kursor ke user lain
  // =============================================
  quill.on('selection-change', (range) => {
    if (!socket?.connected) return
    socket.emit('cursor-move', {
      documentName: props.docName,
      user: { name: props.userName, color: props.userColor },
      range: range   // null = blur/defocus
    })
  })

  // Tampilkan kursor user lain
  socket.on('cursor-update', ({ socketId, user, range }) => {
    if (!cursors || socketId === myId) return
    if (range && user) {
      activeUsers.value[socketId] = user
      try { cursors.createCursor(socketId, user.name, user.color) } catch {}
      cursors.moveCursor(socketId, range)
    } else {
      delete activeUsers.value[socketId]
      try { cursors.removeCursor(socketId) } catch {}
    }
  })

  fetchVersions()
  } catch (err) {
    console.error('Editor init error:', err)
    alert('Editor gagal dimuat: ' + err.message)
  }
})

onBeforeUnmount(() => {
  clearTimeout(saveTimer)
  if (socket) socket.disconnect()
})
</script>

<style>
/* ================================================================
   EDITOR — CSS berbasis ID, tidak konflik dengan Tailwind
   ================================================================ */

/* ===== quill-cursors v4 CSS (manual, tanpa attr() yang tidak valid) ===== */
.ql-cursor .ql-cursor-caret-container,
.ql-cursor .ql-cursor-flag { position: absolute; }
.ql-cursor .ql-cursor-flag {
  z-index: 1;
  transform: translate3d(-1px, -100%, 0);
  color: #fff;
  padding-bottom: 2px;
  border-radius: 0 3px 3px 0;
  /* PAKSA selalu terlihat (default-nya hidden) */
  opacity: 1 !important;
  visibility: visible !important;
}
.ql-cursor .ql-cursor-flag .ql-cursor-name {
  margin: 0 5px;
  display: inline-block;
  white-space: nowrap;
  font-size: 12px;
  font-weight: 600;
}
.ql-cursor .ql-cursor-caret-container {
  cursor: text;
  padding: 0 9px;
  margin-left: -9px;
  top: 0; bottom: 0;
}
.ql-cursor .ql-cursor-caret {
  position: absolute;
  top: 0; bottom: 0;
  width: 2px;
  margin-left: -1px;
  /* background-color set via inline style oleh quill-cursors */
}
.ql-cursor.hidden { opacity: 0; pointer-events: none; }
.ql-cursor-selection-block { position: absolute; pointer-events: none; }
/* ================================================================ */
html, body { margin: 0; padding: 0; height: 100%; }

#editor-root {
  display: flex;
  flex-direction: column;
  height: 100vh;
  width: 100vw;
  overflow: hidden;
  font-family: 'Roboto', Arial, sans-serif;
  background: #f8f9fa;
}

/* ---- HEADER ---- */
#editor-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 6px 12px; background: white;
  border-bottom: 1px solid #e0e0e0; flex-shrink: 0;
}
.hdr-left { display: flex; align-items: center; gap: 8px; }
.hdr-right { display: flex; align-items: center; gap: 8px; }
.back-btn { display: flex; padding: 6px; border-radius: 50%; text-decoration: none; }
.back-btn:hover { background: #f1f3f4; }
.back-btn svg { width: 30px; height: 30px; }
.doc-title { font-size: 17px; font-weight: 500; color: #202124; padding: 1px 4px; }
.menu-bar { display: flex; }
.menu-bar button { font-size: 12px; color: #444; background: none; border: none; padding: 2px 7px; border-radius: 4px; cursor: pointer; }
.menu-bar button:hover { background: #f1f3f4; }
.conn-badge { font-size: 11px; padding: 3px 8px; border-radius: 10px; font-weight: 500; white-space: nowrap; }
.conn-badge.ok  { color: #1e8e3e; background: #e6f4ea; }
.conn-badge.err { color: #c5221f; background: #fce8e6; }
.user-pills { display: flex; }
.pill {
  width: 28px; height: 28px; border-radius: 50%;
  border: 2px solid white; display: flex; align-items: center;
  justify-content: center; color: white; font-size: 11px; font-weight: bold;
  margin-left: -5px; cursor: default;
}
.ico-btn {
  width: 32px; height: 32px; background: none; border: none;
  border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;
  color: #5f6368; font-size: 14px;
}
.ico-btn svg { width: 18px; height: 18px; }
.ico-btn:hover { background: #f1f3f4; }
.save-btn {
  background: #c2e7ff; color: #001d35; border: none;
  padding: 7px 16px; border-radius: 20px; font-size: 13px;
  font-weight: 500; cursor: pointer; white-space: nowrap;
}
.save-btn:hover { background: #a8d4ee; }
.uavatar { width: 32px; height: 32px; border-radius: 50%; cursor: pointer; }

/* ================================================================
   QUILL WRAPPER — scroll ada di sini, bukan di .ql-container
   (quill-cursors butuh .ql-container overflow:visible)
   ================================================================ */
#quill-wrap {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Toolbar yang dibuat Quill Snow */
#quill-wrap .ql-toolbar.ql-snow {
  flex-shrink: 0;
  background: #EDF2FA !important;
  border: none !important;
  border-bottom: 1px solid #dadce0 !important;
  padding: 4px 8px !important;
}
#quill-wrap .ql-toolbar button { border-radius: 4px !important; }
#quill-wrap .ql-toolbar button:hover { background: #d0ddf0 !important; }
#quill-wrap .ql-toolbar button.ql-active { background: #c2e7ff !important; }

/* Container Quill — JANGAN overflow:auto di sini, biar cursor tidak ter-clip */
#quill-wrap .ql-container.ql-snow {
  flex: 1;
  border: none !important;
  background: #f8f9fa;
  overflow-y: auto;       /* scroll tetap di sini agar halaman bisa scroll */
  position: relative;     /* quill-cursors butuh ini */
}

/* ================================================================
   QUILL EDITOR = Kertas Putih A4 Centered
   ================================================================ */
#quill-wrap .ql-editor {
  /* Kertas A4 di tengah */
  width: 816px !important;
  min-height: 1056px !important;
  margin: 28px auto !important;
  padding: 96px !important;
  background: white !important;
  box-shadow: 0 1px 4px rgba(0,0,0,0.15), 0 4px 16px rgba(0,0,0,0.06) !important;

  /* Typography */
  font-family: Arial, Helvetica, sans-serif !important;
  font-size: 11pt !important;
  line-height: 1.5 !important;
  color: #000 !important;

  /* Penting: jangan sampai ini dioverride */
  cursor: text !important;
  box-sizing: border-box !important;

  /* Biarkan editor expand sesuai konten */
  height: auto !important;
  overflow: visible !important;
}

/* Placeholder text */
#quill-wrap .ql-editor.ql-blank::before {
  font-style: italic;
  color: #bbb;
  left: 96px;
  right: 96px;
}

/* ================================================================
   LIVE CURSOR — override quill-cursors v4
   ================================================================ */

/* Paksa .ql-container punya position:relative dan overflow visible
   agar cursor tidak terpotong */
#quill-wrap .ql-container {
  position: relative !important;
  overflow: visible !important;
}

/* Pastikan .ql-editor punya position:relative agar cursor abs. terposisi benar */
#quill-wrap .ql-editor {
  position: relative !important;
}

/* SELALU tampilkan flag nama cursor (bukan hanya saat hover) */
.ql-cursor .ql-cursor-flag {
  opacity: 1 !important;
  visibility: visible !important;
  transition: none !important;
  background-color: currentColor;
}

/* Style nama user di dalam flag */
.ql-cursor .ql-cursor-flag .ql-cursor-name {
  font-size: 11px !important;
  font-weight: 600 !important;
  font-family: 'Roboto', Arial, sans-serif !important;
  color: white !important;
  padding: 2px 8px !important;
  white-space: nowrap !important;
}

/* Garis kursor vertikal */
.ql-cursor .ql-cursor-caret {
  width: 2px !important;
  opacity: 1 !important;
}

/* Pastikan cursor tidak tersembunyi */
.ql-cursor.hidden { display: block !important; opacity: 0.7 !important; }

/* ================================================================
   HISTORY PANEL
   ================================================================ */
#history-panel {
  position: fixed; right: 0; top: 0; bottom: 0; width: 280px; z-index: 200;
  background: white; border-left: 1px solid #e0e0e0;
  box-shadow: -4px 0 16px rgba(0,0,0,0.12);
  display: flex; flex-direction: column;
}
.hp-head {
  display: flex; justify-content: space-between; align-items: center;
  padding: 14px 16px; border-bottom: 1px solid #e0e0e0;
  font-weight: 600; font-size: 15px; color: #202124;
  background: white; position: sticky; top: 0;
}
.hp-body { padding: 14px 16px; overflow-y: auto; flex: 1; }
.empty { font-size: 13px; color: #999; text-align: center; padding: 20px 0; font-style: italic; }
.vi { position: relative; padding-left: 16px; padding-bottom: 18px; border-left: 2px solid #e8eaed; }
.vi:last-child { border-left-color: transparent; }
.vi-dot { width: 10px; height: 10px; background: white; border: 2px solid #bbb; border-radius: 50%; position: absolute; left: -6px; top: 3px; }
.vi-date { font-size: 13px; font-weight: 500; color: #202124; }
.vi-name { font-size: 12px; color: #666; margin-top: 2px; }
.vi-author { font-size: 12px; color: #666; margin-top: 4px; display: flex; align-items: center; gap: 5px; }
.vi-av { width: 15px; height: 15px; background: #4285f4; color: white; border-radius: 50%; font-size: 9px; font-weight: bold; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.vi-restore { margin-top: 6px; font-size: 12px; background: #f1f3f4; color: #3c4043; border: 1px solid #e0e0e0; padding: 4px 12px; border-radius: 12px; cursor: pointer; }
.vi-restore:hover { background: #c2e7ff; color: #001d35; border-color: #c2e7ff; }

/* Transition */
.slide-enter-active, .slide-leave-active { transition: transform 0.2s ease; }
.slide-enter-from, .slide-leave-to { transform: translateX(100%); }
</style>
