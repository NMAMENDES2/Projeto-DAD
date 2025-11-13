import { Server } from "socket.io";
const io = new Server(3000, {
    cors: {
        origin: "*"
    }
});
io.on("connection", (socket) => {
    socket.on("echo", (msg) => {
        socket.emit("echo", msg);
    });
});


console.log("WebSocket server is running on ws://localhost:3000");