import serial
import serial.tools.list_ports
import time
import re
from datetime import datetime

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

    # Tes koneksi
    resp_at = kirim_perintah(port, "AT")
    if "OK" not in resp_at:
        return {"sim": "❌ Modem tidak merespons", "provider": "-"}

    # Cek SIM
    resp_cpin = kirim_perintah(port, "AT+CPIN?")
    if "+CPIN: READY" in resp_cpin:
        hasil["sim"] = "✅ READY"
    elif "SIM PIN" in resp_cpin:
        hasil["sim"] = "🔒 SIM Terkunci (PIN)"
    else:
        hasil["sim"] = "❌ Tidak ada SIM"
        return hasil

    # Cek provider jika SIM READY
    resp_cops = kirim_perintah(port, "AT+COPS?")
    match = re.search(r'\+COPS:.*,"([^"]+)"', resp_cops)
    hasil["provider"] = match.group(1) if match else "Tidak terdeteksi"

    return hasil

def get_ports():
    """Ambil daftar port yang tersedia"""
    return [p.device for p in serial.tools.list_ports.comports()]

def print_log(msg):
    """Log dengan timestamp"""
    print(f"[{datetime.now().strftime('%H:%M:%S')}] {msg}")

def main():
    print("📡 Memulai pemantauan modem secara realtime...\n")
    ports = get_ports()
    if not ports:
        print("❌ Tidak ada modem terdeteksi.")
        return

    # Simpan status sebelumnya agar tahu perubahan
    last_status = {}

    while True:
        for port in ports:
            status = cek_status_sim(port)
            sim_status = status["sim"]
            provider = status["provider"]

            # Gabungkan untuk deteksi perubahan
            current_state = f"{sim_status}|{provider}"

            if port not in last_status:
                # Pertama kali deteksi
                print_log(f"🟡 {port} → {sim_status} ({provider})")
            elif last_status[port] != current_state:
                # Ada perubahan
                if "READY" in sim_status:
                    print_log(f"🟢 {port} → SIM DIPASANG ({provider})")
                elif "Tidak ada" in sim_status:
                    print_log(f"🔴 {port} → SIM DICABUT")
                else:
                    print_log(f"🟠 {port} → {sim_status}")

            # Update status terakhir
            last_status[port] = current_state

        time.sleep(3)  # interval 3 detik

if __name__ == "__main__":
    main()
