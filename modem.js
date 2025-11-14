// === Simple Modem SMS UI ===
// Jalankan: node server.js

const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const { SerialPort } = require("serialport");
const { ReadlineParser } = require("@serialport/parser-readline");

const app = express();
const server = http.createServer(app);
const io = new Server(server);
const PORT = 3000;

// Serve file HTML
app.use(express.static("public"));
app.use(express.json());

// ====== KONFIGURASI MODEM ======
const MODEM_PORT = "COM6"; // Ganti sesuai port modem kamu
const BAUD_RATE = 115200;

const port = new SerialPort({ path: MODEM_PORT, baudRate: BAUD_RATE, autoOpen: false });
const parser = port.pipe(new ReadlineParser({ delimiter: "\r\n" }));

// ====== BUKA PORT ======
port.open((err) => {
  if (err) {
    console.error("❌ Gagal membuka port:", err.message);
    return;
  }
  console.log(`✅ Modem terhubung di ${MODEM_PORT}`);
  port.write("AT\r");
  port.write("AT+CMGF=1\r");
  port.write('AT+CSCS="GSM"\r');
  console.log("📱 Modem siap untuk SMS (text mode)");
});

// ====== Kirim data modem ke browser secara real-time ======
parser.on("data", (data) => {
  if (data.trim()) {
    console.log("📩", data);
    io.emit("modem-response", data);
  }
});

port.on("error", (err) => {
  console.error("⚠️ Port error:", err.message);
  io.emit("modem-response", "⚠️ Port Error: " + err.message);
});

// ====== API untuk kirim SMS ======
app.post("/sms", (req, res) => {
  const { number, message } = req.body;
  if (!number || !message) return res.status(400).json({ error: "Nomor dan pesan wajib diisi!" });

  console.log(`➡️ Kirim SMS ke ${number}: ${message}`);
  io.emit("modem-response", `➡️ Kirim SMS ke ${number}: ${message}`);

  port.write("AT+CMGF=1\r");
  setTimeout(() => {
    port.write(`AT+CMGS="${number}"\r`);
    setTimeout(() => {
      port.write(message + String.fromCharCode(26)); // CTRL+Z
      res.json({ success: true });
    }, 500);
  }, 500);
});

// ====== Socket.IO koneksi ======
io.on("connection", (socket) => {
  console.log("Client connected:", socket.id);
  socket.emit("modem-response", "✅ Tersambung ke server modem");
});

// ====== Jalankan server ======
server.listen(PORT, () => {
  console.log(`🚀 Server berjalan di http://localhost:${PORT}`);
});
