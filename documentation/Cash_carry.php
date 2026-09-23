<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dokumentasi Teknis Modul Cash Carry</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.5; color: #222; margin: 30px; }
  h1 { border-bottom: 2px solid #333; padding-bottom: 8px; font-size: 22px; }
  h2 { margin-top: 25px; font-size: 16px; color: #1a5276; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; font-size: 13px; }
  th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; vertical-align: top; }
  th { background-color: #f4f6f7; font-weight: 600; }
  tr:nth-child(even) { background-color: #fafafa; }
  code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; font-family: monospace; font-size: 12px; }
  ul { font-size: 13px; padding-left: 20px; }
</style>
</head>
<body>

<h1>DOKUMEN TEKNIS: SPESIFIKASI DATA & LOGIKA CASH CARRY (LEMBUR)</h1>

<h2>1. Identifikasi & Parameter Pengajuan Lembur</h2>
<table>
  <thead>
    <tr><th>Field / Kolom</th><th>Tipe Data</th><th>Deskripsi</th><th>Sumber / Logika</th></tr>
  </thead>
  <tbody>
    <tr><td><code>request_code</code></td><td>VARCHAR(30)</td><td>Kode pengajuan lembur</td><td>Auto: <code>"CC" + dept_code + YYMMDD + 4_digit_seq</code></td></tr>
    <tr><td><code>idm_no</code></td><td>VARCHAR(50)</td><td>Nomor Memo IDM</td><td>Input manual / Auto prefix IDM</td></tr>
    <tr><td><code>trans_date</code></td><td>DATE</td><td>Tanggal pelaksanaan lembur</td><td>Form Datebox (YYYY-MM-DD)</td></tr>
    <tr><td><code>type</code></td><td>ENUM</td><td>Jenis lembur</td><td>Pilihan: <code>REGULAR</code> / <code>CORRECTION</code></td></tr>
    <tr><td><code>start</code> / <code>end</code></td><td>TIME</td><td>Jam mulai & selesai</td><td>Jika <code>end &lt; start</code>, dihitung melewati tengah malam (+1 hari).</td></tr>
    <tr><td><code>break</code></td><td>INTEGER</td><td>Menit istirahat</td><td>Pengurang durasi kotor (satuan menit).</td></tr>
    <tr><td><code>meal</code></td><td>TINYINT</td><td>Hak uang makan</td><td><code>1</code> (YES / Dapat Uang Makan) atau <code>0</code> (NO / Tidak).</td></tr>
    <tr><td><code>plan</code> / <code>actual</code></td><td>INTEGER</td><td>Output produksi/kerja</td><td>Target vs Realisasi hasil kerja.</td></tr>
  </tbody>
</table>

<h2>2. Logika Konversi Jam Lembur (convertHour)</h2>
<table>
  <thead>
    <tr><th>Komponen</th><th>Formula</th><th>Keterangan</th></tr>
  </thead>
  <tbody>
    <tr><td><strong>Menit Bersih</strong></td><td><code>total_minutes = MAX(0, ((end - start) / 60) - break)</code></td><td>Durasi kotor dikurangi istirahat.</td></tr>
    <tr><td><strong>Flooring 0.5 Jam</strong></td><td><code>floor(total_minutes / 30) * 30</code></td><td>Diterapkan jika <code>round_duration == 0.5</code>.</td></tr>
    <tr><td><strong>Flooring 1.0 Jam</strong></td><td><code>floor(total_minutes / 60) * 60</code></td><td>Diterapkan jika <code>round_duration == 1.0</code>.</td></tr>
    <tr><td><strong>duration_hour</strong></td><td><code>final_minutes_total / 60</code></td><td>Durasi desimal untuk perkalian upah.</td></tr>
  </tbody>
</table>

<h2>3. Matriks Tarif Lembur & Uang Makan (readOvertimePrice)</h2>
<table>
  <thead>
    <tr><th>Shift</th><th>Kondisi Tanggal</th><th>Kolom Tarif (allowance_cash_carry)</th><th>Uang Makan</th></tr>
  </thead>
  <tbody>
    <tr><td>Shift 5 Hari</td><td>Senin–Jumat (Kerja)</td><td><code>weekday</code></td><td><code>meal == 1 ? allowance.meal : 0</code></td></tr>
    <tr><td>Shift 5 Hari</td><td>Senin–Jumat (Libur Nasional)</td><td><code>holiday</code></td><td><code>meal == 1 ? allowance.meal : 0</code></td></tr>
    <tr><td>Shift 5 Hari</td><td>Sabtu</td><td><code>saturday</code></td><td><code>meal == 1 ? allowance.meal : 0</code></td></tr>
    <tr><td>Shift 5 Hari</td><td>Minggu</td><td><code>sunday</code></td><td><code>meal == 1 ? allowance.meal : 0</code></td></tr>
    <tr><td>Shift 6 Hari</td><td>Senin–Sabtu (Kerja)</td><td><code>weekday</code></td><td><code>meal == 1 ? allowance.meal : 0</code></td></tr>
    <tr><td>Shift 6 Hari</td><td>Senin–Sabtu (Libur Nasional)</td><td><code>holiday</code></td><td><code>meal == 1 ? allowance.meal : 0</code></td></tr>
    <tr><td>Shift 6 Hari</td><td>Minggu</td><td><code>sunday</code></td><td><code>meal == 1 ? allowance.meal : 0</code></td></tr>
  </tbody>
</table>

<h2>4. Perhitungan Nominal Upah Lembur</h2>
<ul>
  <li><strong>Tarif Khusus Transfer:</strong> <code>amount_perhour = (basic_salary + total_tunjangan_fix) / 7 / 25</code> (jika <code>payment_type == 'TRANSFER'</code> & grup tertentu).</li>
  <li><strong>Plan Amount:</strong> <code>amount = (amount_perhour * duration_hour) + meal_amount</code>.</li>
  <li><strong>Actual Hour:</strong> Durasi absensi riil dari tabel <code>attandances</code> dengan formula <code>convertHour(date_in, time_in, time_out, break)</code>.</li>
  <li><strong>Actual Amount:</strong> <code>amount_actual = (amount_perhour * MIN(actual_hour, plan_hour)) + meal_amount</code>.</li>
</ul>

</body>
</html>