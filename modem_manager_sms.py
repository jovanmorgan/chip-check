import serial
import serial.tools.list_ports
import time
import re
import mysql.connector
from datetime import datetime
import threading

# 🔧 Konfigurasi database MySQL
db_config = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "chip_check"
}

def connect_db():
    try:
        return mysql.connector.connect(**db_config)
    except Exception as e:
        print(f"❌ Gagal konek DB: {e}")
        return None


# ----------------- 🔹 SIMPAN LOG -----------------
def simpan_log_modem(port, status_sim, provider):
    conn = connect_db()
    if not conn:
        return
    cursor = conn.cursor()
    waktu = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    cursor.execute("SELECT id FROM log_modem WHERE port=%s", (port,))
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


def simpan_sms(port, direction, nomor, pesan):
    """Simpan log SMS masuk/keluar"""
    conn = connect_db()
    if not conn:
        return
    cursor = conn.cursor()
    cursor.execute("""
        INSERT INTO sms_log (port, direction, nomor, pesan, waktu)
        VALUES (%s, %s, %s, %s, NOW())
    """, (port, direction, nomor, pesan))
    conn.commit()
    cursor.close()
    conn.close()


# ----------------- 🔹 MODEM -----------------
def kirim_perintah(port, command, delay=0.5):
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

    # Tes koneksi
    resp_at = kirim_perintah(port, "AT")
    if "OK" not in resp_at:
        hasil["status_sim"] = "❌ Tidak merespons"
        simpan_log_modem(port, hasil["status_sim"], hasil["provider"])
        return

    # Status SIM
    resp_sim = kirim_perintah(port, "AT+CPIN?")
    if "+CPIN: READY" in resp_sim:
        hasil["status_sim"] = "✅ READY"
    elif "SIM PIN" in resp_sim:
        hasil["status_sim"] = "🔒 Terkunci (PIN)"
    else:
        hasil["status_sim"] = "❌ Tidak ada SIM"
        simpan_log_modem(port, hasil["status_sim"], hasil["provider"])
        return

    # Provider
    resp_cops = kirim_perintah(port, "AT+COPS?")
    match = re.search(r'\+COPS:.*,"([^"]+)"', resp_cops)
    hasil["provider"] = match.group(1) if match else "Tidak terdeteksi"

    # Simpan jika berubah
    current_state = f"{hasil['status_sim']}|{hasil['provider']}"
    if port not in last_status or last_status[port] != current_state:
        print(f"[{datetime.now().strftime('%H:%M:%S')}] {port} → {hasil['status_sim']} ({hasil['provider']})")
        simpan_log_modem(port, hasil["status_sim"], hasil["provider"])
        last_status[port] = current_state


# ----------------- 🔹 SMS -----------------
def kirim_sms(port, nomor, pesan):
    """Kirim SMS dan simpan ke database"""
    try:
        ser = serial.Serial(port, 115200, timeout=2)
        ser.write(b'AT+CMGF=1\r')
        time.sleep(0.5)
        ser.write(f'AT+CMGS="{nomor}"\r'.encode())
        time.sleep(0.5)
        ser.write(pesan.encode() + b"\x1A")  # Ctrl+Z
        time.sleep(3)
        response = ser.read_all().decode(errors='ignore')
        ser.close()

        simpan_sms(port, "OUT", nomor, pesan)
        print(f"📤 SMS ke {nomor} via {port}: {pesan}")
        return response
    except Exception as e:
        print(f"❌ Gagal kirim SMS di {port}: {e}")
        return str(e)


def baca_sms(port):
    """Baca SMS masuk dan simpan ke database"""
    try:
        ser = serial.Serial(port, 115200, timeout=2)
        ser.write(b'AT+CMGF=1\r')
        time.sleep(0.5)
        ser.write(b'AT+CMGL="ALL"\r')
        time.sleep(2)
        data = ser.read_all().decode(errors='ignore')
        ser.close()

        sms_pattern = r'\+CMGL: \d+,"[^"]+","([^"]+)"[^\n]*\n(.+?)(?=\r\n\+CMGL|\Z)'
        for nomor, pesan in re.findall(sms_pattern, data, re.DOTALL):
            simpan_sms(port, "IN", nomor, pesan.strip())
            print(f"📥 SMS dari {nomor} di {port}: {pesan.strip()}")

    except Exception as e:
        print(f"❌ Gagal baca SMS dari {port}: {e}")


# ----------------- 🔹 LOOP PARALEL -----------------
def get_ports():
    return [p.device for p in serial.tools.list_ports.comports()]

def loop_monitor():
    print("📡 Memulai pemantauan modem & SMS...\n")
    last_status = {}

    while True:
        ports = get_ports()
        threads = []

        for port in ports:
            t = threading.Thread(target=cek_modem, args=(port, last_status))
            threads.append(t)
            t.start()
            baca_sms(port)  # Baca SMS masuk di setiap loop

        for t in threads:
            t.join()

        time.sleep(5)


if __name__ == "__main__":
    loop_monitor()
