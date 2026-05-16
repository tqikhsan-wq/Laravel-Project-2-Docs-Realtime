import { Server } from '@hocuspocus/server'
import { Database } from '@hocuspocus/extension-database'
import mysql from 'mysql2/promise'
import * as Y from 'yjs'

// Buat koneksi pool ke MySQL (menyambung ke database Laravel kita)
const pool = mysql.createPool({
  host: '127.0.0.1',
  user: 'root',      // Sesuaikan dengan user phpMyAdmin kamu
  password: '',      // Sesuaikan jika ada passwordnya
  database: 'pwebdocsrealtime',
})

const server = new Server({
  port: 1234,

  // Menangkap pesan khusus dari Vue (seperti perintah "Simpan Versi")
  onStateless: async ({ payload, documentName, document }) => {
    try {
      const msg = JSON.parse(payload)
      if (msg.action === 'save-version') {
        // Ambil snapshot/keadaan dokumen saat ini ke dalam bentuk biner Yjs
        const state = Y.encodeStateAsUpdate(document)
        const versionName = msg.versionName || 'Versi ' + new Date().toLocaleString('id-ID')
        
        await pool.execute(
          `INSERT INTO document_versions (document_name, version_name, data, created_at, updated_at) 
           VALUES (?, ?, ?, NOW(), NOW())`,
          [documentName, versionName, state]
        )
        
        console.log(`✅ Versi baru disimpan: ${versionName}`)
      }
    } catch (e) {
      console.log('Error parsing stateless message:', e)
    }
  },

  extensions: [
    new Database({
      // 1. Fungsi mengambil data saat ada user yang baru bergabung/refresh halaman
      fetch: async ({ documentName }) => {
        const [rows] = await pool.execute('SELECT data FROM documents WHERE name = ?', [documentName])
        if (rows.length > 0 && rows[0].data) {
          return rows[0].data // Hocuspocus butuh Uint8Array biner
        }
        return null // Jika kosong, biarkan Hocuspocus membuat file baru
      },

      // 2. Fungsi menabung data ke database saat ada user mengetik (Otomatis menggunakan Debounce)
      store: async ({ documentName, state }) => {
        await pool.execute(
          `INSERT INTO documents (name, data, created_at, updated_at) 
           VALUES (?, ?, NOW(), NOW()) 
           ON DUPLICATE KEY UPDATE data = ?, updated_at = NOW()`,
          [documentName, state, state]
        )
      },
    }),
  ],
})

server.listen()
console.log('✅ Server Hocuspocus + MySQL berjalan di ws://127.0.0.1:1234')
