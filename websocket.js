import { Server } from '@hocuspocus/server'

// Konfigurasi sederhana Server Hocuspocus
const server = new Server({
  port: 1234, // Port yang akan dipakai untuk WebSocket
})

server.listen()
console.log('✅ Server Hocuspocus berjalan di ws://127.0.0.1:1234')
