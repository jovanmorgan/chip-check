<!-- index.php -->
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Modem Activator - Demo</title>

  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-3">Modem Activator — Kontrol Serial (AT commands)</h2>

  <div class="row g-3">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Pilih COM Port</h5>
          <p class="card-text">Deteksi COM port yang terhubung ke PC (modem / USB-serial).</p>

          <div class="mb-2">
            <select id="portSelect" class="form-select">
              <option value="">-- Pilih port --</option>
            </select>
          </div>
          <div class="d-flex gap-2">
            <button id="btnRefresh" class="btn btn-outline-primary">Refresh Ports</button>
            <button id="btnDetect" class="btn btn-primary">Detect Modem (AT)</button>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mt-3">
        <div class="card-body">
          <h5 class="card-title">Actions</h5>
          <div class="mb-2">
            <input id="atInput" class="form-control" placeholder="Masukkan AT command (mis. AT+CCID)" value="AT" />
          </div>
          <div class="d-flex gap-2">
            <button id="btnSendAT" class="btn btn-success">Kirim AT</button>
            <button id="btnUSSD" class="btn btn-warning">Kirim USSD</button>
            <button id="btnSMS" class="btn btn-secondary">Kirim SMS</button>
          </div>

          <div id="smsForm" class="mt-3" style="display:none;">
            <input id="smsNumber" class="form-control mb-2" placeholder="Nomor tujuan (contoh: 0812...)" />
            <textarea id="smsText" class="form-control" placeholder="Isi SMS"></textarea>
            <button id="btnSendSMSConfirm" class="btn btn-sm btn-dark mt-2">Kirim SMS</button>
          </div>

        </div>
      </div>
    </div>

    <div class="col-md-7">
      <div class="card shadow-sm">
        <div class="card-header">
          <strong>Output / Log</strong>
        </div>
        <div class="card-body">
          <pre id="logArea" style="height:420px; overflow:auto; background:#111; color:#0f0; padding:10px; border-radius:6px;">Log akan muncul di sini...</pre>
        </div>
      </div>

      <div class="card shadow-sm mt-3">
        <div class="card-body">
          <h6>Petunjuk Singkat</h6>
          <ul>
            <li>Pastikan modem terhubung dan driver sudah terinstall (CH340/FTDI/Prolific).</li>
            <li>Jika COM &gt; 9, sistem memakai path khusus Windows (\\\\\\.\\COM10).</li>
            <li>Jalankan Apache/IIS sebagai Administrator bila ada error akses port.</li>
            <li>Gunakan tombol <em>Detect Modem</em> untuk memeriksa apakah modem merespon AT.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS (bundle includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const logArea = document.getElementById('logArea');
function log(...args){
  const t = new Date().toLocaleString();
  logArea.textContent = `${t} > ${args.join(' ')}\n` + logArea.textContent;
}

// Fetch list ports
async function refreshPorts(){
  log('Refreshing ports...');
  try {
    const r = await fetch('proses/aktivasi/list_ports.php');
    const j = await r.json();
    const sel = document.getElementById('portSelect');
    sel.innerHTML = '<option value="">-- Pilih port --</option>';
    if(j.ok && Array.isArray(j.ports)){
      j.ports.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.device;
        opt.textContent = `${p.device} - ${p.caption}`;
        sel.appendChild(opt);
      });
      log('Ports loaded:', j.ports.length);
    } else {
      log('Gagal load ports:', j.error || JSON.stringify(j));
    }
  } catch (err){
    log('Error refreshPorts:', err);
  }
}

document.getElementById('btnRefresh').addEventListener('click', refreshPorts);

// Detect modem by sending AT to selected port (or first)
document.getElementById('btnDetect').addEventListener('click', async ()=>{
  const sel = document.getElementById('portSelect').value;
  if(!sel){ log('Pilih port dulu, atau refresh ports.'); return; }
  log('Mencoba detect modem di', sel);
  const fd = new FormData();
  fd.append('com', sel);
  fd.append('cmd', 'AT');
  fd.append('timeout', '2');
  const r = await fetch('proses/aktivasi/api_send_at.php', { method: 'POST', body: fd });
  const j = await r.json();
  if(j.ok){
    log('Response:', j.resp || '(kosong)');
  } else {
    log('Detect failed:', j.err || JSON.stringify(j));
  }
});

// Send AT button
document.getElementById('btnSendAT').addEventListener('click', async ()=>{
  const sel = document.getElementById('portSelect').value;
  const cmd = document.getElementById('atInput').value.trim();
  if(!sel) { log('Pilih port dulu'); return; }
  if(!cmd) { log('Masukkan AT command'); return; }
  log('Mengirim:', cmd, 'ke', sel);
  const fd = new FormData();
  fd.append('com', sel);
  fd.append('cmd', cmd);
  fd.append('timeout', '4');
  const r = await fetch('proses/aktivasi/api_send_at.php', { method:'POST', body: fd });
  const j = await r.json();
  if(j.ok){
    log('OK ->', j.resp);
  } else {
    log('ERROR ->', j.err || JSON.stringify(j));
  }
});

// USSD flow (popup prompt)
document.getElementById('btnUSSD').addEventListener('click', async ()=>{
  const sel = document.getElementById('portSelect').value;
  if(!sel){ log('Pilih port dulu'); return; }
  const ussd = prompt('Masukkan kode USSD (contoh: *123#):','*123#');
  if(!ussd) return;
  log('Mengirim USSD', ussd, 'ke', sel);
  // Format: AT+CUSD=1,"<code>",15
  const cmd = `AT+CUSD=1,"${ussd}",15`;
  const fd = new FormData();
  fd.append('com', sel);
  fd.append('cmd', cmd);
  fd.append('timeout', '6');
  const r = await fetch('proses/aktivasi/api_send_at.php', { method:'POST', body: fd });
  const j = await r.json();
  if(j.ok) log('USSD Resp ->', j.resp); else log('USSD FAIL ->', j.err);
});

// SMS UI toggle
document.getElementById('btnSMS').addEventListener('click', ()=>{
  const el = document.getElementById('smsForm');
  el.style.display = el.style.display === 'none' ? 'block' : 'none';
});

// Send SMS confirm
document.getElementById('btnSendSMSConfirm').addEventListener('click', async ()=>{
  const sel = document.getElementById('portSelect').value;
  if(!sel){ log('Pilih port dulu'); return; }
  const num = document.getElementById('smsNumber').value.trim();
  const txt = document.getElementById('smsText').value.trim();
  if(!num || !txt){ log('Masukkan nomor dan isi SMS'); return; }
  log('Kirim SMS ke', num);
  const fd = new FormData();
  fd.append('com', sel);
  fd.append('cmd', 'SEND_SMS'); // special: backend tahu ini perintah kirim sms
  fd.append('sms_number', num);
  fd.append('sms_text', txt);
  fd.append('timeout', '8');
  const r = await fetch('proses/aktivasi/api_send_at.php', { method:'POST', body: fd });
  const j = await r.json();
  if(j.ok) log('SMS Resp ->', j.resp); else log('SMS FAIL ->', j.err);
});

// load ports on start
refreshPorts();
</script>

</body>
</html>
