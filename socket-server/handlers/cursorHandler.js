export default function setupCursorHandler(io, socket) {
    // Track which document this socket is in
    let currentDocument = null;

    // Listen for join-document to track room
    socket.on('join-document', (documentName) => {
        currentDocument = documentName;
    });

    socket.on('cursor-move', (data) => {
        if (!data.documentName) return;
        currentDocument = data.documentName;

        // Broadcast cursor position to OTHERS in same document room
        socket.to(data.documentName).emit('cursor-update', {
            socketId: socket.id,
            user: data.user,
            range: data.range  // null = user left editor focus
        });
    });

    // When user disconnects, remove their cursor from everyone else
    socket.on('disconnect', () => {
        if (currentDocument) {
            socket.to(currentDocument).emit('cursor-update', {
                socketId: socket.id,
                user: null,
                range: null  // null range = remove cursor
            });
        }
    });
}
