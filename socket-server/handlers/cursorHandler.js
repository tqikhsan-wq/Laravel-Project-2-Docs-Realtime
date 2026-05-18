export default function setupCursorHandler(io, socket) {
    let currentDocument = null;

    socket.on('join-document', (documentName) => {
        currentDocument = documentName;
    });

    // ── Text cursor (Quill caret position) ──
    socket.on('cursor-move', (data) => {
        if (!data.documentName) return;
        currentDocument = data.documentName;
        socket.to(data.documentName).emit('cursor-update', {
            socketId: socket.id,
            user: data.user,
            range: data.range
        });
    });

    // ── Mouse pointer position (seperti Figma) ──
    socket.on('mouse-move', (data) => {
        if (!data.documentName) return;
        currentDocument = data.documentName;
        socket.to(data.documentName).emit('mouse-update', {
            socketId: socket.id,
            user: data.user,
            x: data.x,
            y: data.y
        });
    });

    // ── Saat disconnect, hapus cursor & mouse pointer dari semua user ──
    socket.on('disconnect', () => {
        if (currentDocument) {
            socket.to(currentDocument).emit('cursor-update', {
                socketId: socket.id, user: null, range: null
            });
            socket.to(currentDocument).emit('mouse-update', {
                socketId: socket.id, user: null, x: null, y: null
            });
        }
    });
}
