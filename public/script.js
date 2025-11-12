const socket = io();
const table = document.getElementById("portTable");
const status = document.getElementById("status");
const loading = document.getElementById("loading");

// Modal references
const actionModal = new bootstrap.Modal(document.getElementById("actionModal"));
const resultModal = new bootstrap.Modal(document.getElementById("resultModal"));
const modalTitle = document.getElementById("modalTitle");
const modalLabel = document.getElementById("modalLabel");
const modalInput = document.getElementById("modalInput");
const modalMessage = document.getElementById("modalMessage");
const extraField = document.getElementById("extraField");
const modalForm = document.getElementById("modalForm");
const modalPort = document.getElementById("modalPort");
const resultBody = document.getElementById("resultBody");

let activeUSSD = null; // simpan sesi USSD aktif

// === Render tabel port ===
function renderRows(ports) {
  if (!ports || ports.length === 0) {
    table.innerHTML = `<tr><td colspan="5" class="text-center">Tidak ada modem terdeteksi</td></tr>`;
    return;
  }

  table.innerHTML = ports.map(p => {
    const providerBadge = p.provider
      ? `<span class="badge bg-success badge-provider">${p.provider}</span>`
      : `<span class="badge bg-secondary">Unknown</span>`;
    const imsi = p.imsi ? `<div class="text-muted small">${p.imsi}</div>` : "";
    const statusLabel = p.status === "ready"
      ? `<span class="badge bg-success">Ready</span>`
      : (p.status === "connecting"
        ? `<span class="badge bg-warning">Connecting</span>`
        : `<span class="badge bg-danger">${p.status}</span>`);

    return `<tr>
      <td>${p.port}</td>
      <td>${escapeHtml(p.modem || p.manufacturer || "Unknown")}</td>
      <td>${providerBadge}${imsi}</td>
      <td>${statusLabel}${p.error ? `<div class="text-danger small">${escapeHtml(p.error)}</div>` : ""}</td>
      <td>
        <button class="btn btn-sm btn-info me-1" onclick="showSMS('${p.port}')">📩 SMS</button>
        <button class="btn btn-sm btn-warning" onclick="showUSSD('${p.port}')">📞 Dial</button>
      </td>
    </tr>`;
  }).join("");
}

// === Socket event listeners ===
socket.on("connect", () => status.textContent = "Connected to server");
socket.on("disconnect", () => status.textContent = "Disconnected");
socket.on("ports", (data) => {
  status.textContent = `Last update: ${new Date().toLocaleTimeString()}`;
  renderRows(data);
});
socket.on("port-notification", (n) => console.log("Notif:", n.port, n.line));

// === USSD interaktif ===
socket.on("ussd-response", (data) => {
  if (!activeUSSD || data.port !== activeUSSD.port) return;
  const textarea = document.getElementById("ussdSessionBox");
  if (!textarea) return;
  textarea.value += "\n\n📡 " + (data.response || "(tidak ada respon)");

  if (data.sessionEnd) {
    textarea.value += "\n\n✅ Sesi selesai.";
    document.getElementById("ussdSendBtn").disabled = true;
  }
});

// === SMS ===
function showSMS(port) {
  modalTitle.textContent = "Kirim SMS";
  modalLabel.textContent = "Nomor Tujuan";
  modalInput.placeholder = "contoh: 08123456789";
  modalInput.value = "";
  modalMessage.value = "";
  extraField.style.display = "block";
  modalPort.value = port;
  modalForm.onsubmit = sendSMS;
  actionModal.show();
}

// === USSD ===
function showUSSD(port) {
  activeUSSD = { port };
  modalTitle.textContent = "Dial Kode USSD";
  modalLabel.textContent = "Masukkan kode (contoh: *888#)";
  modalInput.placeholder = "*888#";
  modalInput.value = "";
  extraField.style.display = "none";
  modalPort.value = port;
  modalForm.onsubmit = sendUSSD;
  actionModal.show();
}

async function sendSMS(e) {
  e.preventDefault();
  const port = modalPort.value;
  const number = modalInput.value.trim();
  const message = modalMessage.value.trim();
  if (!number || !message) return alert("Lengkapi data!");
  actionModal.hide();
  showLoading(true);
  try {
    const res = await fetch("/sms", {
      method: "POST",
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify({ port, number, message })
    });
    const j = await res.json();
    showLoading(false);
    if (j.error) showResult("❌ Gagal", `<pre>${j.error}</pre>`);
    else showResult("✅ SMS Dikirim", `<pre>${JSON.stringify(j.raw || j, null, 2)}</pre>`);
  } catch (err) {
    showLoading(false);
    showResult("⚠️ Error", err.message);
  }
}

// === USSD kirim pertama / lanjutan ===
async function sendUSSD(e) {
  e.preventDefault();
  const port = modalPort.value;
  const code = modalInput.value.trim();
  if (!code) return alert("Masukkan kode!");

  actionModal.hide();
  showLoading(true);
  try {
    const res = await fetch("/dial", {
      method: "POST",
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify({ port, code })
    });
    const j = await res.json();
    showLoading(false);
    if (j.error) {
      showResult("❌ Gagal", `<pre>${j.error}</pre>`);
    } else {
      // tampilkan hasil di modal interaktif
      const text = escapeHtml(j.response || JSON.stringify(j, null, 2));
      showInteractiveUSSD(port, text);
    }
  } catch (err) {
    showLoading(false);
    showResult("⚠️ Error", err.message);
  }
}

// === Modal hasil interaktif (USSD chat) ===
function showInteractiveUSSD(port, response) {
  const html = `
    <div>
      <textarea id="ussdSessionBox" class="form-control" rows="10" readonly style="font-family:monospace;">📡 ${response}</textarea>
      <div class="input-group mt-3">
        <input type="text" id="ussdNextInput" class="form-control" placeholder="Ketik angka atau perintah lanjutan...">
        <button class="btn btn-primary" id="ussdSendBtn">Kirim</button>
      </div>
    </div>
  `;
  showResult("📞 USSD Session", html);
  const sendBtn = document.getElementById("ussdSendBtn");
  const nextInput = document.getElementById("ussdNextInput");

  sendBtn.onclick = async () => {
    const val = nextInput.value.trim();
    if (!val) return;
    const box = document.getElementById("ussdSessionBox");
    box.value += "\n\n➡️ " + val;
    nextInput.value = "";
    try {
      const res = await fetch("/dial", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({ port, code: val })
      });
      const j = await res.json();
      if (j.error) box.value += "\n\n❌ " + j.error;
      else box.value += "\n\n📡 " + (j.response || "(tidak ada respon)");
      if (j.sessionEnd) {
        box.value += "\n\n✅ Sesi selesai.";
        sendBtn.disabled = true;
      }
    } catch (err) {
      box.value += "\n\n⚠️ Error: " + err.message;
    }
  };

  activeUSSD = { port };
}

function showResult(title, content) {
  document.querySelector("#resultModal .modal-title").innerHTML = title;
  resultBody.innerHTML = content;
  resultModal.show();
}

function showLoading(v) {
  loading.style.display = v ? "flex" : "none";
}

document.getElementById("btnRefresh").addEventListener("click", () => {
  status.textContent = "Manual refresh...";
  fetch("/").catch(()=>{});
});

function escapeHtml(s){
  return (s||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
}
