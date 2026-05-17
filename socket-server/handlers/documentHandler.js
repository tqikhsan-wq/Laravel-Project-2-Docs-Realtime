import axios from 'axios';

// In-memory document storage for fast real-time sync
const documents = {}; 

export default function setupDocumentHandler(io, socket) {
    socket.on('join-document', async (documentName) => {
        socket.join(documentName);
        console.log(`Socket ${socket.id} joined document: ${documentName}`);
        
        // Fetch from Laravel API if not in memory
        if (!documents[documentName]) {
             try {
                 // Assuming Laravel runs on port 8000
                 const res = await axios.get(`http://127.0.0.1:8000/api/documents/${documentName}`);
                 documents[documentName] = res.data.content || '';
             } catch (e) {
                 console.error('Error fetching document from API', e.message);
                 documents[documentName] = '';
             }
        }
        
        // Send current document state to the joining user
        socket.emit('load-document', documents[documentName]);
    });

    socket.on('send-changes', (data) => {
        // Broadcast delta changes to others in the same document room
        socket.to(data.documentName).emit('receive-changes', data.delta);
        
        // Optionally update in-memory state based on deltas
        // Note: For rich text, true Operational Transformation (OT) requires applying the delta
        // If simply storing text/HTML for simplicity, you'd listen to 'save-document' instead.
    });

    socket.on('save-document', async (data) => {
        // HTTP sync to Laravel API for persistence
        try {
            await axios.put(`http://127.0.0.1:8000/api/documents/${data.documentName}`, { content: data.content });
            documents[data.documentName] = data.content;
            console.log(`Saved document to DB: ${data.documentName}`);
        } catch (e) {
            console.error('Error saving document to API', e.message);
        }
    });
}
