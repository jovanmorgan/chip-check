import serial
import serial.tools.list_ports
import time
import re
import mysql.connector
from datetime import datetime

# 🔧 Konfigurasi database MySQL
db_config = {
    "host": "localhost",
    "user": "root",          # ganti jika user kamu berbeda
    "password": "",          # isi password MySQL kamu
    "database": "chip_check"
}

def connect_db():
    """Koneksi ke database"""
    try:
        conn = mysql.connector.connect(**db_config)
        return conn
    except Exception as e:
        print(f"❌ Gagal konek database: {e}")
        return None

def simpan_log_db(port, status_sim, provider):
    """Simpan perubahan status ke database"""
    conn = connect_db()
    if conn:
        cursor = conn.cursor()
        sql = "INSERT INTO log_modem (port, status_sim, provider) VALUES (%s, %s, %s)"
        cursor.execute(sql, (port, status_sim, provider))
        conn.commit()
        cursor.close()
        conn.close()

def simpan_log_file(pesan):
    """Tulis log ke file teks"""
    with open("log_modem.txt", "a", encoding="utf-8") as f:
        f.write(f"[{datetime.now().strftime('%Y-%m-%d %H:%M:%S')}] {pesan}\n")

def kirim_perintah(port, command, delay=0.4):
    try:
        ser = serial.Serial(port, 115200, timeout=1)
        ser.write((command + "\r").encode())
        time.sleep(delay)
        response = ser.read_all().decode(errors="ignore")
        ser.close()
        return response.strip()
    except Exception:
        return ""

def cek_status_sim(port):
    """Cek status SIM dan provider"""
    hasil = {"sim": "❌ Tidak ada SIM", "provider": "-"}

    resp_at = kirim_perintah(port, "AT")
    if "OK" not in resp_at:
        hasil["sim"] = "❌ Modem tidak merespons"
        return hasil

    resp_cpin = kirim_perintah(port, "AT+CPIN?")
    if "+CPIN: READY" in resp_cpin:
        hasil["sim"] = "✅ READY"
    elif "SIM PIN" in resp_cpin:
        hasil["sim"] = "🔒 SIM Terkunci (PIN)"
    else:
        hasil["sim"] = "❌ Tidak ada SIM"
        return hasil

    resp_cops = kirim_perintah(port, "AT+COPS?")
    match = re.search(r'\+COPS:.*,"([^"]+)"', resp_cops)
    hasil["provider"] = match.group(1) if match else "Tidak terdeteksi"

    return hasil

def get_ports():
    return [p.device for p in serial.tools.list_ports.comports()]

def print_log(msg):
    print(f"[{datetime.now().strftime('%H:%M:%S')}] {msg}")

def main():
    print("📡 Memulai pemantauan modem realtime + logging DB...\n")
    ports = get_ports()
    if not ports:
        print("❌ Tidak ada modem terdeteksi.")
        return

    last_status = {}

    while True:
        for port in ports:
            status = cek_status_sim(port)
            sim_status = status["sim"]
            provider = status["provider"]
            current_state = f"{sim_status}|{provider}"

            if port not in last_status:
                pesan = f"🟡 {port} → {sim_status} ({provider})"
                print_log(pesan)
                simpan_log_file(pesan)
                simpan_log_db(port, sim_status, provider)

            elif last_status[port] != current_state:
                if "READY" in sim_status:
                    pesan = f"🟢 {port} → SIM DIPASANG ({provider})"
                elif "Tidak ada" in sim_status:
                    pesan = f"🔴 {port} → SIM DICABUT"
                else:
                    pesan = f"🟠 {port} → {sim_status}"

                print_log(pesan)
                simpan_log_file(pesan)
                simpan_log_db(port, sim_status, provider)

            last_status[port] = current_state

        time.sleep(3)

if __name__ == "__main__":
    main()
