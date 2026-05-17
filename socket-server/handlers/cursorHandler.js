export default function setupCursorHandler(io, socket) {
    socket.on('cursor-move', (data) => {
        // Broadcast cursor position to others in the same document room
        socket.to(data.documentName).emit('cursor-update', {
            socketId: socket.id,
            user: data.user, // User information from the client
            range: data.range  // Quill cursor range (index, length)
        });
    });
}
