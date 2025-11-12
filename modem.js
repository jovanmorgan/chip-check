const express = require("express");
const cors = require("cors");
const { SerialPort, SerialPortMock, list } = require("serialport");
const { ReadlineParser } = require("@serialport/parser-readline");

const app = express();
app.use(cors());
app.use(express.json());
app.use(express.static("public"));

// 🔍 Endpoint untuk mendeteksi semua port modem
app.get("/ports", async (req, res) => {
  try {
    const ports = await list();
    const result = [];

    for (const port of ports) {
      try {
        const serial = new SerialPort({ path: port.path, baudRate: 115200, autoOpen: false });
        await new Promise((r, j) => serial.open((e) => (e ? j(e) : r())));
        const parser = serial.pipe(new ReadlineParser({ delimiter: "\r\n" }));

        // Kirim AT command singkat
        const provider = await new Promise((resolve) => {
          let providerName = "Tidak terdeteksi";
          parser.on("data", (line) => {
            if (line.includes("+COPS:")) {
              const match = line.match(/"([^"]+)"/);
              if (match) providerName = match[1];
            }
            if (line === "OK") resolve(providerName);
          });
          serial.write("AT+COPS?\r");
          setTimeout(() => resolve(providerName), 2500);
        });

        result.push({
          port: port.path,
          manufacturer: port.manufacturer || "Unknown",
          provider,
        });

        serial.close();
      } catch (err) {
        console.log("❌ Gagal deteksi:", port.path);
      }
    }

    res.json(result);
  } catch (e) {
    res.status(500).json({ error: e.message });
  }
});

// 📨 Kirim SMS
app.post("/sms", async (req, res) => {
  const { port, number, message } = req.body;
  try {
    const serial = new SerialPort({ path: port, baudRate: 115200 });
    const parser = serial.pipe(new ReadlineParser({ delimiter: "\r\n" }));

    await new Promise((r) => setTimeout(r, 500));
    serial.write("AT+CMGF=1\r");
    await new Promise((r) => setTimeout(r, 500));
    serial.write(`AT+CMGS="${number}"\r`);
    await new Promise((r) => setTimeout(r, 500));
    serial.write(message + String.fromCharCode(26)); // Ctrl+Z

    res.json({ success: true, message: `SMS terkirim ke ${number}` });
  } catch (e) {
    res.status(500).json({ error: e.message });
  }
});

// 📞 Dial USSD
app.post("/dial", async (req, res) => {
  const { port, code } = req.body;
  try {
    const serial = new SerialPort({ path: port, baudRate: 115200 });
    const parser = serial.pipe(new ReadlineParser({ delimiter: "\r\n" }));

    let response = "";
    parser.on("data", (line) => {
      if (line.includes("+CUSD:")) response += line + "\n";
    });

    serial.write(`AT+CUSD=1,"${code}",15\r`);

    setTimeout(() => {
      serial.close();
      res.json({ success: true, response: response || "Tidak ada respons" });
    }, 5000);
  } catch (e) {
    res.status(500).json({ error: e.message });
  }
});

app.listen(3000, () => console.log("🌐 Server berjalan di http://localhost:3000"));
