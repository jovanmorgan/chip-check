import serial
import serial.tools.list_ports
import time

def scan_ports():
    """Mendeteksi semua port COM yang tersedia"""
    ports = serial.tools.list_ports.comports()
    return [port.device for port in ports]

def cek_modem(port):
    """Coba komunikasi AT dengan port tertentu"""
    try:
        ser = serial.Serial(port, 115200, timeout=1)
        ser.write(b"AT\r")
        time.sleep(0.5)
        response = ser.readlines()
        ser.close()

        # Periksa apakah ada respon "OK"
        for line in response:
            if b"OK" in line:
                return True, "Terdeteksi (respon OK)"
        return False, "Tidak merespons AT"
    except Exception as e:
        return False, str(e)

def main():
    print("🔍 Mendeteksi port modem yang terhubung...\n")
    ports = scan_ports()

    if not ports:
        print("❌ Tidak ada port COM terdeteksi.")
        return

    print(f"Ditemukan {len(ports)} port:")
    print("=" * 40)

    for port in ports:
        status, info = cek_modem(port)
        tanda = "✅" if status else "❌"
        print(f"{tanda} {port:<8} → {info}")

    print("=" * 40)
    print("✅ Pemindaian selesai.\n")

if __name__ == "__main__":
    main()
