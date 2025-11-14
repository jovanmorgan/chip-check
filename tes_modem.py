import serial
import time

# Ganti port di bawah ini sesuai port modem kamu (misal "COM4")
PORT = "COM42"
BAUD = 115200

try:
    ser = serial.Serial(PORT, BAUD, timeout=1)
    print(f"✅ Terhubung ke modem di {PORT}")
except Exception as e:
    print(f"❌ Gagal membuka port: {e}")
    exit()

def kirim_perintah(perintah):
    """Kirim perintah AT dan tampilkan respon"""
    ser.write((perintah + "\r").encode())
    time.sleep(0.5)
    while ser.in_waiting:
        print(ser.readline().decode(errors="ignore").strip())

print("\n➡️ Tes koneksi ke modem...")
kirim_perintah("AT")

print("\n➡️ Informasi modem (ATI)...")
kirim_perintah("ATI")

print("\n➡️ Status SIM (AT+CPIN?)...")
kirim_perintah("AT+CPIN?")

ser.close()
print("\n🔚 Tes selesai.")
