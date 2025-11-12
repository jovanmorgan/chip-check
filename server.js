// server.js
const express = require("express");
const http = require("http");
const cors = require("cors");
const { SerialPort } = require("serialport");
const { ReadlineParser } = require("@serialport/parser-readline");
const { Server } = require("socket.io");

const app = express();
app.use(cors());
app.use(express.json());
app.use(express.static("public/"));

const server = http.createServer(app);
const io = new Server(server);

const SCAN_INTERVAL = 4000; // ms

// map: path -> { port: SerialPort, parser, info:{port,manufacturer,modem,provider,imsi}, lastSeen }
const portMap = new Map();

async function probePort(path) {
  // jika sudah ada, return existing info
  if (portMap.has(path)) return portMap.get(path).info;

  const info = {
    port: path,
    manufacturer: "",
    modem: "Unknown",
    provider: "Unknown",
    imsi: null,
    status: "connecting",
  };

  try {
    const serial = new SerialPort({ path, baudRate: 115200, autoOpen: false });

    await new Promise((res, rej) => serial.open(err => (err ? rej(err) : res())));
    const parser = serial.pipe(new ReadlineParser({ delimiter: "\r\n" }));

    // helper send AT and collect lines until OK/ERROR or timeout
    const sendAT = (cmd, timeout = 2500) =>
      new Promise((resolve) => {
        let lines = [];
        const onData = (line) => {
          lines.push(line);
          if (line === "OK" || line === "ERROR") {
            parser.off("data", onData);
            resolve(lines);
          }
        };
        parser.on("data", onData);
        serial.write(cmd + "\r");
        setTimeout(() => {
          parser.off("data", onData);
          resolve(lines);
        }, timeout);
      });

    // flush with simple AT
    await sendAT("AT");
    const ati = await sendAT("ATI");
    const gmm = await sendAT("AT+GMM");

    // store modem text
    const modemName = (gmm.find(l => l && l !== "OK" && l !== "ERROR") ||
                       ati.find(l => l && l !== "OK" && l !== "ERROR")) || "Unknown";
    info.modem = Array.isArray(modemName) ? modemName.join(" ") : modemName;

    // try COPS (operator)
    const cops = await sendAT("AT+COPS?");
    // parse provider string if available
    let provider = "Unknown";
    for (const line of cops) {
      const m = line.match(/"([^"]+)"/);
      if (m) {
        provider = m[1];
        break;
      }
    }

    // try get IMSI if provider unknown or as fallback
    const cimires = await sendAT("AT+CIMI");
    const imsi = cimires.find(l => /^\d{5,}$/.test(l)) || null;
    if (imsi) info.imsi = imsi;

    // if provider still Unknown but IMSI present, map common MCC/MNC for Indonesia
    if ((provider === "Unknown" || !provider) && imsi) {
      const prefix = imsi.slice(0,5);
      const map = {
        "51010":"TELKOMSEL",
        "51011":"INDOSAT",
        "51012":"XL/AXIS",
        "51089":"SMARTFREN",
        "51008":"TRI",
        // tambahkan sesuai kebutuhan
      };
      provider = map[prefix] || provider;
    }

    // finalize info
    info.provider = provider;
    info.manufacturer = serial.manufacturer || "";
    info.status = "ready";

    // store in map
    portMap.set(path, {
      port: serial,
      parser,
      info,
      lastSeen: Date.now()
    });

    // listen for disconnection
    serial.on("close", () => {
      info.status = "disconnected";
      // keep entry until next scan cleans it up
    });

    // listen for incoming USSD/SMS notifications (emit to clients)
    parser.on("data", (line) => {
      if (!line) return;
      if (line.startsWith("+CUSD:") || line.includes("+CMTI") || line.includes("+CMGR")) {
        io.emit("port-notification", { port: path, line });
      }
    });

    return info;
  } catch (err) {
    // can't open port or probe failed
    info.status = "error";
    info.error = String(err.message || err);
    portMap.set(path, { info, lastSeen: Date.now() });
    return info;
  }
}

async function scanPorts() {
  try {
    const ports = await SerialPort.list();
    const paths = ports.map(p => p.path);

    // probe new ports
    await Promise.all(paths.map(async path => {
      if (!portMap.has(path) || portMap.get(path).info.status === "error") {
        await probePort(path);
      } else {
        const entry = portMap.get(path);
        entry.lastSeen = Date.now();
      }
    }));

    // cleanup removed ports
    for (const key of Array.from(portMap.keys())) {
      if (!paths.includes(key)) {
        const entry = portMap.get(key);
        try {
          if (entry.port && entry.port.isOpen) entry.port.close();
        } catch (e) {}
        portMap.delete(key);
      }
    }

    // emit ke frontend
    const payload = Array.from(portMap.values()).map(e => ({
      port: e.info.port,
      manufacturer: e.info.manufacturer,
      modem: e.info.modem,
      provider: e.info.provider,
      imsi: e.info.imsi,
      status: e.info.status,
      error: e.info.error || null
    }));

    io.emit("ports", payload);
  } catch (e) {
    console.error("Scan error:", e);
  }
}


// start periodic scanning
setInterval(scanPorts, SCAN_INTERVAL);
scanPorts(); // initial

app.post("/sms", async (req, res) => {
  const { port, number, message } = req.body;
  if (!portMap.has(port)) return res.status(400).json({ error: "Port not available" });

  const entry = portMap.get(port);
  const serial = entry.port;
  const parser = entry.parser;

  try {
    await new Promise(r => serial.write("AT+CMGF=1\r", r)); // text mode
    await new Promise(r => setTimeout(r, 300));
    await new Promise(r => serial.write("AT+CNMI=2,1,0,0,0\r", r)); // notif SMS masuk
    await new Promise(r => setTimeout(r, 300));

    await new Promise(r => serial.write(`AT+CMGS="${number}"\r`, r));
    await new Promise(r => setTimeout(r, 500));
    await new Promise(r => serial.write(message + String.fromCharCode(26), r));

    let buffer = [];
    let lastCmgr = "";
    let collectingMsg = false;
    let msgContent = "";

    const onData = (line) => {
      if (!line) return;

      line = line.trim();
      buffer.push(line);

      // hindari duplikasi
      if (line === lastCmgr) return;
      lastCmgr = line;

      // tampilkan semua respon ke UI
      io.emit("port-notification", { port, line });

      // deteksi ada SMS baru masuk
      if (line.includes("+CMTI:")) {
        const match = line.match(/\+CMTI: ".*",(\d+)/);
        if (match) {
          const index = match[1];
          serial.write(`AT+CMGR=${index}\r`);
        }
      }

      // deteksi awal isi SMS (+CMGR)
      if (line.startsWith("+CMGR:")) {
        collectingMsg = true;
        msgContent = ""; // reset isi
      } else if (collectingMsg && line !== "OK" && !line.startsWith("AT+CMGR")) {
        // baris berikutnya adalah isi pesan
        msgContent += line + "\n";
      } else if (collectingMsg && line === "OK") {
        // selesai baca pesan
        collectingMsg = false;
        const cleanMsg = msgContent.trim();
        if (cleanMsg) {
          io.emit("port-notification", { port, line: `📩 Isi SMS: ${cleanMsg}` });
        }
      }
    };

    parser.removeAllListeners("data"); // cegah listener ganda
    parser.on("data", onData);

    await new Promise(r => setTimeout(r, 6000));
    parser.off("data", onData);

    return res.json({ success: true, raw: buffer });
  } catch (err) {
    io.emit("port-notification", { port, line: "❌ Error kirim SMS: " + String(err) });
    return res.status(500).json({ error: String(err) });
  }
});


let ussdSessions = {};

app.post("/dial", async (req, res) => {
  const { port, code } = req.body;
  if (!portMap.has(port)) return res.status(400).json({ error: "Port not available" });
  const entry = portMap.get(port);
  const serial = entry.port;
  const parser = entry.parser;

  try {
    // Cek apakah kita sedang di dalam sesi USSD aktif
    const isNewSession = code.startsWith("*") || !ussdSessions[port] || ussdSessions[port].ended;

    // Jika sesi baru, kirim perintah pembuka
    const cmd = isNewSession
      ? `AT+CUSD=1,"${code}",15\r`
      : `AT+CUSD=1,"${code}"\r`;

    await new Promise(r => serial.write(cmd, r));
    console.log(`[${port}] USSD ${isNewSession ? 'NEW' : 'CONTINUE'} → ${code}`);

    let responseText = "";
    const onData = (line) => {
      if (line.startsWith("+CUSD:")) {
        responseText = line;
        // deteksi akhir sesi (CUSD: 2 artinya sesi selesai)
        const ended = line.includes("+CUSD: 2");
        ussdSessions[port] = { last: code, ended };
      }
    };

    parser.on("data", onData);

    // timeout 7 detik
    await new Promise(r => setTimeout(r, 7000));
    parser.off("data", onData);

    if (!responseText) {
      return res.json({ success: false, message: "Tidak ada respon dari modem" });
    }

    return res.json({
      success: true,
      response: responseText,
      sessionEnd: responseText.includes("+CUSD: 2")
    });

  } catch (err) {
    console.error("USSD error:", err);
    return res.status(500).json({ error: String(err) });
  }
});

io.on("connection", (socket) => {
  console.log("Client connected", socket.id);
  // send current snapshot immediately
  const snapshot = Array.from(portMap.values()).map(e => ({
    port: e.info.port,
    manufacturer: e.info.manufacturer,
    modem: e.info.modem,
    provider: e.info.provider,
    imsi: e.info.imsi,
    status: e.info.status,
    error: e.info.error || null
  }));
  socket.emit("ports", snapshot);
});

const PORT_SERVER = 3000;
server.listen(PORT_SERVER, () => console.log(`Server running on http://localhost:${PORT_SERVER}`));
