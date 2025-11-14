import serial
import serial.tools.list_ports
import time
import re
import mysql.connector
from datetime import datetime
import threading
from flask import Flask, Response, render_template_string, jsonify
import json

# 🔧 Konfigurasi database
db_config = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "chip_check"
}

app = Flask(__name__)

# ====================================================
# 🔹 BAGIAN DATABASE
# ====================================================
def connect_db():
    try:
        return mysql.connector.connect(**db_config)
    except Exception as e:
        print(f"❌ Gagal konek DB: {e}")
        return None

def ambil_semua_modem():
    conn = connect_db()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM log_modem ORDER BY port ASC")
    data = cursor.fetchall()
    cursor.close()
    conn.close()
    return data

def simpan_ke_db(port, status_sim, provider):
    conn = connect_db()
    if not conn:
        return
    cursor = conn.cursor()
    waktu = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    cursor.execute("SELECT id FROM log_modem WHERE port = %s", (port,))
    ada = cursor.fetchone()
    if ada:
        cursor.execute("""
            UPDATE log_modem 
            SET status_sim=%s, provider=%s, waktu=%s
            WHERE port=%s
        """, (status_sim, provider, waktu, port))
    else:
        cursor.execute("""
            INSERT INTO log_modem (port, status_sim, provider, waktu)
            VALUES (%s, %s, %s, %s)
        """, (port, status_sim, provider, waktu))
    conn.commit()
    cursor.close()
    conn.close()

# ====================================================
# 🔹 BAGIAN CEK MODEM
# ====================================================
def kirim_perintah(port, command, delay=0.4):
    try:
        ser = serial.Serial(port, 115200, timeout=1)
        ser.write((command + "\r").encode())
        time.sleep(delay)
        response = ser.read_all().decode(errors='ignore')
        ser.close()
        return response.strip()
    except Exception:
        return ""

def cek_modem(port, last_status):
    hasil = {"port": port, "status_sim": "❌ Tidak terdeteksi", "provider": "-"}
    resp_at = kirim_perintah(port, "AT")
    if "OK" not in resp_at:
        hasil["status_sim"] = "❌ Modem tidak merespons"
        simpan_ke_db(port, hasil["status_sim"], hasil["provider"])
        return
    resp_sim = kirim_perintah(port, "AT+CPIN?")
    if "+CPIN: READY" in resp_sim:
        hasil["status_sim"] = "✅ READY"
    elif "SIM PIN" in resp_sim:
        hasil["status_sim"] = "🔒 SIM PIN"
    else:
        hasil["status_sim"] = "❌ Tidak ada SIM"
        simpan_ke_db(port, hasil["status_sim"], hasil["provider"])
        return
    resp_cops = kirim_perintah(port, "AT+COPS?")
    match = re.search(r'\+COPS:.*,"([^"]+)"', resp_cops)
    hasil["provider"] = match.group(1) if match else "Tidak terdeteksi"
    current_state = f"{hasil['status_sim']}|{hasil['provider']}"
    if port not in last_status or last_status[port] != current_state:
        print(f"[{datetime.now().strftime('%H:%M:%S')}] {port} → {hasil['status_sim']} ({hasil['provider']})")
        simpan_ke_db(port, hasil["status_sim"], hasil["provider"])
        last_status[port] = current_state

def get_ports():
    return [p.device for p in serial.tools.list_ports.comports()]

def monitor_modem():
    last_status = {}
    while True:
        ports = get_ports()
        threads = []
        for port in ports:
            t = threading.Thread(target=cek_modem, args=(port, last_status))
            t.start()
            threads.append(t)
        for t in threads:
            t.join()
        time.sleep(5)

# ====================================================
# 🔹 BAGIAN WEB FLASK
# ====================================================
@app.route('/')
def index():
    html = """
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>📡 Monitoring Modem Realtime</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: #f8f9fa; padding: 20px; }
            .card { border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
            .status-ready { color: green; font-weight: bold; }
            .status-error { color: red; font-weight: bold; }
            .status-pin { color: orange; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <h3 class="mb-4 text-center">📡 Monitoring Modem Realtime</h3>
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Port</th>
                        <th>Status SIM</th>
                        <th>Provider</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody id="data-body"></tbody>
            </table>
        </div>

        <script>
        const eventSource = new EventSource('/stream');
        eventSource.onmessage = function(e) {
            const data = JSON.parse(e.data);
            const tbody = document.getElementById('data-body');
            tbody.innerHTML = '';
            data.forEach(row => {
                let statusClass = '';
                if (row.status_sim.includes('READY')) statusClass = 'status-ready';
                else if (row.status_sim.includes('PIN')) statusClass = 'status-pin';
                else statusClass = 'status-error';
                tbody.innerHTML += `
                    <tr>
                        <td>${row.port}</td>
                        <td class="${statusClass}">${row.status_sim}</td>
                        <td>${row.provider}</td>
                        <td>${row.waktu}</td>
                    </tr>`;
            });
        };
        </script>
    </body>
    </html>
    """
    return render_template_string(html)

@app.route('/stream')
def stream():
    def event_stream():
        last_data = None
        while True:
            data = ambil_semua_modem()
            json_data = json.dumps(data, default=str)
            if json_data != last_data:
                yield f"data: {json_data}\n\n"
                last_data = json_data
            time.sleep(2)
    return Response(event_stream(), mimetype='text/event-stream')

# ====================================================
# 🔹 JALANKAN MONITOR + WEB
# ====================================================
if __name__ == "__main__":
    threading.Thread(target=monitor_modem, daemon=True).start()
    app.run(host="0.0.0.0", port=5000, debug=False)
