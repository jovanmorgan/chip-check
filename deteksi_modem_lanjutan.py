import serial
import serial.tools.list_ports
import time
import re

def scan_ports():
    """Mendeteksi semua port COM yang tersedia"""
    ports = serial.tools.list_ports.comports()
    return [port.device for port in ports]

def kirim_perintah(port, command, delay=0.5):
    """Kirim perintah AT dan baca respon"""
    try:
        ser = serial.Serial(port, 115200, timeout=1)
        ser.write((command + "\r").encode())
        time.sleep(delay)
        response = ser.read_all().decode(errors='ignore')
        ser.close()
        return response.strip()
    except Exception as e:
        return str(e)

def cek_modem(port):
    """Cek modem dan status SIM di port tertentu"""
    hasil = {"port": port, "status_modem": "❌", "status_sim": "-", "provider": "-"}

    # Tes koneksi AT
    resp_at = kirim_perintah(port, "AT")
    if "OK" not in resp_at:
        hasil["status_modem"] = "❌ Tidak merespons"
        return hasil
    hasil["status_modem"] = "✅ OK"

    # Cek status SIM
    resp_sim = kirim_perintah(port, "AT+CPIN?")
    if "+CPIN: READY" in resp_sim:
        hasil["status_sim"] = "✅ READY"
    elif "SIM PIN" in resp_sim:
        hasil["status_sim"] = "🔒 Terkunci (PIN)"
    else:
        hasil["status_sim"] = "❌ Tidak ada SIM"
        return hasil  # Tidak lanjut jika tidak ada SIM

    # Cek provider/operator
    resp_cops = kirim_perintah(port, "AT+COPS?")
    match = re.search(r'\+COPS:.*,"([^"]+)"', resp_cops)
    if match:
        hasil["provider"] = match.group(1)
    else:
        hasil["provider"] = "Tidak terdeteksi"

    return hasil

def main():
    print("🔍 Mendeteksi modem dan kartu SIM...\n")
    ports = scan_ports()

    if not ports:
        print("❌ Tidak ada port COM terdeteksi.")
        return

    print(f"Ditemukan {len(ports)} port aktif.")
    print("=" * 70)
    print(f"{'PORT':<8} {'MODEM':<15} {'SIM STATUS':<15} {'PROVIDER':<25}")
    print("-" * 70)

    for port in ports:
        hasil = cek_modem(port)
        print(f"{hasil['port']:<8} {hasil['status_modem']:<15} {hasil['status_sim']:<15} {hasil['provider']:<25}")

    print("=" * 70)
    print("✅ Pemindaian selesai.\n")

if __name__ == "__main__":
    main()
