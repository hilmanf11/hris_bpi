<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dokumentasi Teknis Modul Payroll</title>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; line-height: 1.5; color: #222; margin: 30px; }
  h1 { border-bottom: 2px solid #333; padding-bottom: 8px; font-size: 22px; }
  h2 { margin-top: 25px; font-size: 16px; color: #1a5276; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
  h3 { margin-top: 15px; font-size: 14px; color: #333; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; font-size: 12px; }
  th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: top; }
  th { background-color: #f4f6f7; font-weight: 600; }
  tr:nth-child(even) { background-color: #fafafa; }
  code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; font-family: monospace; font-size: 11px; }
  .desc-human { color: #1b4f72; font-style: normal; font-weight: 500; }
  ul, ol { font-size: 12px; padding-left: 20px; }
</style>
</head>
<body>

<h1>DOKUMEN TEKNIS & PANDUAN FORMULA PERHITUNGAN PAYROLL</h1>

<h2>1. Informasi Dasar & Kolom Pembeku (Frozen Columns)</h2>
<table>
  <thead>
    <tr>
      <th style="width: 15%;">Field / Kolom UI</th>
      <th style="width: 15%;">Key JSON / DB</th>
      <th style="width: 30%;">Penjelasan Teknis & Sumber Data</th>
      <th style="width: 40%;">Penjelasan Sederhana (Bahasa Umum)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>Employee ID</code></td>
      <td><code>number</code></td>
      <td>VARCHAR(30) dari <code>employees.number</code></td>
      <td class="desc-human">Nomor Induk Karyawan (NIK) resmi yang terdaftar di perusahaan.</td>
    </tr>
    <tr>
      <td><code>Employee Name</code></td>
      <td><code>name</code></td>
      <td>VARCHAR(100) dari <code>employees.name</code></td>
      <td class="desc-human">Nama lengkap karyawan penerima gaji.</td>
    </tr>
    <tr>
      <td><code>Employee Status</code></td>
      <td><code>status_karyawan</code></td>
      <td>Status: <code>ACTIVE</code>, <code>INACTIVE</code>, atau <code>RESIGN</code></td>
      <td class="desc-human">Status keaktifan karyawan (Aktif, Non-Aktif, atau Berhenti/Resign).</td>
    </tr>
  </tbody>
</table>

<h2>2. Parameter Organisasi & Pajak</h2>
<table>
  <thead>
    <tr>
      <th style="width: 15%;">Field / Kolom UI</th>
      <th style="width: 15%;">Key JSON / DB</th>
      <th style="width: 30%;">Sumber Data Teknis</th>
      <th style="width: 40%;">Penjelasan Sederhana (Bahasa Umum)</th>
    </tr>
  </thead>
  <tbody>
    <tr><td><code>Division</code></td><td><code>division_name</code></td><td><code>divisions.name</code></td><td class="desc-human">Divisi atau lokasi pabrik/plant tempat karyawan bekerja.</td></tr>
    <tr><td><code>Departement</code></td><td><code>departement_name</code></td><td><code>departements.name</code></td><td class="desc-human">Nama departemen/bagian karyawan.</td></tr>
    <tr><td><code>Departement Sub</code></td><td><code>departement_sub_name</code></td><td><code>departement_subs.name</code></td><td class="desc-human">Nama seksi / sub-bagian unit kerja.</td></tr>
    <tr><td><code>Job Type</code></td><td><code>job_type</code></td><td><code>departement_subs.type</code></td><td class="desc-human">Kelompok pekerjaan: Tenaga Kerja Langsung (DIRECT) atau Tidak Langsung (INDIRECT).</td></tr>
    <tr><td><code>Status</code></td><td><code>contract_name</code></td><td><code>contracts.name</code></td><td class="desc-human">Status kontrak kerja (misal: Kontrak/PKWT, Tetap/PKWTT, Magang).</td></tr>
    <tr><td><code>Source</code></td><td><code>source_name</code></td><td><code>sources.name</code></td><td class="desc-human">Asal rekrutmen karyawan (Internal atau Yayasan/Vendor).</td></tr>
    <tr><td><code>Position</code></td><td><code>position_name</code></td><td><code>positions.name</code></td><td class="desc-human">Jabatan karyawan di perusahaan (Staff, Operator, Leader, Supervisor, dll.).</td></tr>
    <tr><td><code>Shift</code></td><td><code>shift_name</code></td><td><code>shift_details.name</code></td><td class="desc-human">Jadwal pola hari kerja (Shift 5 Hari kerja atau 6 Hari kerja).</td></tr>
    <tr><td><code>Level</code></td><td><code>level</code></td><td><code>positions.level</code></td><td class="desc-human">Tingkatan jenjang jabatan/golongan kerja.</td></tr>
    <tr><td><code>Marital</code></td><td><code>marital</code></td><td><code>maritals.number</code></td><td class="desc-human">Status pernikahan & jumlah tanggungan pajak (misal: TK/0 = Lajang tanpa tanggungan, K/1 = Kawin 1 anak).</td></tr>
    <tr><td><code>TER Code</code></td><td><code>ter_number</code></td><td><code>marital_categories.number</code></td><td class="desc-human">Kategori tabel tarif pajak pemerintah (Kategori TER A, TER B, atau TER C).</td></tr>
    <tr><td><code>TER %</code></td><td><code>ter_persentase</code></td><td><code>marital_categories.ter</code></td><td class="desc-human">Persentase tarif potongan pajak penghasilan yang berlaku di bulan ini.</td></tr>
    <tr><td><code>Group</code></td><td><code>group_name</code></td><td><code>groups.name</code></td><td class="desc-human">Golongan penggajian karyawan.</td></tr>
    <tr><td><code>National ID</code></td><td><code>national_id</code></td><td><code>employees.national_id</code></td><td class="desc-human">Nomor KTP (16 Digit NIK KTP).</td></tr>
    <tr><td><code>NPWP</code></td><td><code>tax_id</code></td><td><code>employees.tax_id</code></td><td class="desc-human">Nomor Pokok Wajib Pajak karyawan (jika kosong, tarif pajak lebih tinggi).</td></tr>
  </tbody>
</table>

<h2>3. Parameter Waktu & Kehadiran (Attandance)</h2>
<table>
  <thead>
    <tr>
      <th style="width: 15%;">Field / Kolom UI</th>
      <th style="width: 15%;">Key JSON / DB</th>
      <th style="width: 35%;">Formula & Logika Teknis (PHP)</th>
      <th style="width: 35%;">Penjelasan Sederhana (Bahasa Umum)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>[KODE PERMIT]</code> (CT, DL, SK, dll.)</td>
      <td><code>[permit_code]a</code> (JSON <code>attandance</code>)</td>
      <td><code>COUNT(permit_date)</code> dengan kriteria <code>payroll = 'NON DEDUCTION'</code>.</td>
      <td class="desc-human">Jumlah hari izin resmi/cuti berbayar yang diambil karyawan (gaji tetap dibayar utuh).</td>
    </tr>
    <tr>
      <td><code>WD</code> (Working Day)</td>
      <td><code>attandance_wd</code></td>
      <td><code>$working_days = hadir_fisik + permit_yes + permit_no + change_days</code><br><code>attandance_wd = MIN($working_days, HKW)</code></td>
      <td class="desc-human">Total hari kerja efektif karyawan yang diakui (Kehadiran fisik + Cuti/Izin resmi), dengan batas maksimal tidak boleh melebihi total hari kerja kalender.</td>
    </tr>
    <tr>
      <td><code>Working Calendar</code></td>
      <td><code>working_day</code> (HKW)</td>
      <td><code>HKW = count(weekday_day) - calendar_amount</code></td>
      <td class="desc-human">Jumlah hari kerja wajib dalam satu bulan kalender cut-off (Hari kerja normal dikurangi tanggal merah/libur nasional).</td>
    </tr>
  </tbody>
</table>

<h2>4. Penghasilan Kotor (Bruto Income)</h2>
<table>
  <thead>
    <tr>
      <th style="width: 15%;">Field / Kolom UI</th>
      <th style="width: 15%;">Key JSON / DB</th>
      <th style="width: 35%;">Formula & Logika Teknis (PHP)</th>
      <th style="width: 35%;">Penjelasan Sederhana (Bahasa Umum)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>Salary</code></td>
      <td><code>salary</code></td>
      <td><strong>Aktif:</strong> <code>setup_salaries.amount</code><br><strong>Resign:</strong> <code>ROUND((salary / HKW) * WD)</code></td>
      <td class="desc-human"><strong>Karyawan Aktif:</strong> Gaji pokok penuh.<br><strong>Karyawan Resign:</strong> Gaji pokok dihitung prorata sesuai jumlah hari kerja aktifnya (Gaji Pokok / Hari Kerja Kalender x Hari Masuk Kerja).</td>
    </tr>
    <tr>
      <td><code>[KODE ALLOWANCE]</code> (Tunjangan)</td>
      <td><code>[allowance_code]</code> (JSON <code>allowence</code>)</td>
      <td><strong>Harian:</strong> <code>amount * HKW</code> (Resign: <code>amount * WD</code>)<br><strong>Bulanan:</strong> <code>amount</code> (Resign: <code>(amount / HKW) * WD</code>)</td>
      <td class="desc-human">Tunjangan tetap/tidak tetap (seperti Tunjangan Jabatan, Transport, Makan, dll.). Jika tunjangan harian, dikalikan jumlah hari kerja.</td>
    </tr>
    <tr>
      <td><code>[BPJS Company]</code> (JKK, JKM, JHT, JP, KES)</td>
      <td><code>[bpjs_code]_company</code> (JSON <code>bpjs_company</code>)</td>
      <td><code>Basis Gaji * % Tarif Perusahaan</code></td>
      <td class="desc-human">Iuran BPJS Ketenagakerjaan & Kesehatan yang ditanggung/dibayarkan oleh perusahaan untuk karyawan.</td>
    </tr>
    <tr>
      <td><code>Total BPJS Company</code></td>
      <td><code>bpjs_company_total</code></td>
      <td><code>SUM(Semua Iuran BPJS Porsi Perusahaan)</code></td>
      <td class="desc-human">Total akumulasi seluruh iuran premi BPJS yang dibayarkan perusahaan.</td>
    </tr>
    <tr>
      <td><code>Correction Plus</code></td>
      <td><code>correction_plus</code></td>
      <td><code>SUM(amount) WHERE type = 'PLUS'</code></td>
      <td class="desc-human">Penambahan gaji manual (misal: rapelan gaji, kekurangan bayar bulan lalu, bonus insidental).</td>
    </tr>
    <tr>
      <td><code>Overtime (Weekday)</code></td>
      <td><code>overtime_amount_weekday</code></td>
      <td><code>(Jam Lembur * Tarif Lembur Weekday) + Uang Makan</code></td>
      <td class="desc-human">Upah uang lembur yang dikerjakan pada hari kerja biasa (Senin - Jumat/Sabtu).</td>
    </tr>
    <tr>
      <td><code>Overtime (Weekend)</code></td>
      <td><code>overtime_amount_holiday</code></td>
      <td><code>(Jam Lembur * Tarif Lembur Weekend) + Uang Makan</code></td>
      <td class="desc-human">Upah uang lembur yang dikerjakan pada hari libur mingguan atau hari libur nasional.</td>
    </tr>
    <tr>
      <td><code>Total Overtime</code></td>
      <td><code>total_overtime_amount</code></td>
      <td><code>Lembur Weekday + Lembur Weekend</code></td>
      <td class="desc-human">Total uang lembur keseluruhan yang berhak diterima karyawan.</td>
    </tr>
    <tr>
      <td><code>Bruto Income</code></td>
      <td><code>total_all_allowance</code></td>
      <td><code>Gaji Pokok + Total Tunjangan + BPJS Company + Koreksi Plus + Total Lembur</code></td>
      <td class="desc-human">Total seluruh penghasilan kotor karyawan sebelum dikurangi potongan apapun.</td>
    </tr>
  </tbody>
</table>

<h2>5. Potongan Kehadiran & Operasional (Deduction)</h2>
<table>
  <thead>
    <tr>
      <th style="width: 15%;">Field / Kolom UI</th>
      <th style="width: 15%;">Key JSON / DB</th>
      <th style="width: 35%;">Formula & Logika Teknis (PHP)</th>
      <th style="width: 35%;">Penjelasan Sederhana (Bahasa Umum)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>[KODE PERMIT_D] Amount</code> (IP Amount)</td>
      <td><code>[permit_code]_amount</code> (JSON <code>deduction_amount</code>)</td>
      <td><code>ROUND(((Gaji Pokok + Tunjangan) / HKW) * Hari Izin IP)</code></td>
      <td class="desc-human">Potongan gaji karena izin tidak masuk kerja yang bersifat memotong gaji (Unpaid Leave / Izin Potong Gaji).</td>
    </tr>
    <tr>
      <td><code>Absence</code></td>
      <td><code>deduction_absence</code></td>
      <td><code>HKW - Total Komponen Kehadiran Valid</code></td>
      <td class="desc-human">Jumlah hari karyawan tidak masuk kerja tanpa keterangan (Alpha/Mangkir).</td>
    </tr>
    <tr>
      <td><code>Absence Amount</code></td>
      <td><code>deduction_absence_amount</code></td>
      <td><code>ROUND(((Gaji Pokok + Tunjangan) / HKW) * Hari Alpha)</code></td>
      <td class="desc-human">Nominal uang potongan gaji akibat hari alpha/mangkir kerja.</td>
    </tr>
    <tr>
      <td><code>Correction Minus</code></td>
      <td><code>correction_minus</code></td>
      <td><code>SUM(amount) WHERE type = 'MINUS'</code></td>
      <td class="desc-human">Pengurangan gaji manual (misal: kelebihan bayar bulan lalu, denda administratif khusus).</td>
    </tr>
    <tr>
      <td><code>Total Deduction</code></td>
      <td><code>total_all_deduction</code></td>
      <td><code>Potongan Izin IP + Potongan Alpha + Koreksi Minus</code></td>
      <td class="desc-human">Total seluruh potongan akibat ketidakhadiran kerja dan koreksi minus.</td>
    </tr>
    <tr>
      <td><code>Total BPJS Company</code> (Pengurang)</td>
      <td><code>bpjs_company_total_2</code></td>
      <td>Nilai sama dengan <code>bpjs_company_total</code></td>
      <td class="desc-human">Pengeluaran kembali nilai BPJS Perusahaan (karena BPJS Perusahaan hanya titipan komponen bruto untuk pajak, bukan uang yang diterima tunai oleh karyawan).</td>
    </tr>
    <tr>
      <td><code>Income</code></td>
      <td><code>income</code></td>
      <td><code>Bruto Income - Total Deduction - BPJS Company</code></td>
      <td class="desc-human">Penghasilan bersih karyawan sebelum dikenakan pajak TER, pinjaman, dan potongan rutin karyawan.</td>
    </tr>
  </tbody>
</table>

<h2>6. Potongan Rutin, Pinjaman, BPJS Karyawan, & Gaji Bersih (Take Home Pay)</h2>
<table>
  <thead>
    <tr>
      <th style="width: 15%;">Field / Kolom UI</th>
      <th style="width: 15%;">Key JSON / DB / Variabel</th>
      <th style="width: 35%;">Formula & Logika Teknis (PHP)</th>
      <th style="width: 35%;">Penjelasan Sederhana (Bahasa Umum)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>[Deduction Code]</code> (Potongan Rutin)</td>
      <td><code>[deduction_code]</code> (JSON <code>deduction</code>)<br>Total: <code>$arr_deduction_amount_total</code></td>
      <td>Diambil dari master <code>setup_deductions</code> per karyawan (<code>deductions.amount</code>).</td>
      <td class="desc-human">Potongan rutin tetap karyawan per bulan (seperti: Iuran Koperasi, Denda, Simpanan Wajib, Infaq, dll.).</td>
    </tr>
    <tr>
      <td><code>LOAN (Koperasi)</code></td>
      <td><code>loan_cooperative</code></td>
      <td><code>SUM(amount)</code> dari tabel <code>loans</code> tipe <code>COOPERATIVE</code></td>
      <td class="desc-human">Potongan cicilan/angsuran pinjaman koperasi karyawan bulan ini.</td>
    </tr>
    <tr>
      <td><code>LOAN (Bank)</code></td>
      <td><code>loan_bank</code></td>
      <td><code>SUM(amount)</code> dari tabel <code>loans</code> tipe <code>BANK</code></td>
      <td class="desc-human">Potongan cicilan/angsuran pinjaman perbankan karyawan bulan ini.</td>
    </tr>
    <tr>
      <td><code>LOAN (Other)</code></td>
      <td><code>loan_other</code></td>
      <td><code>SUM(amount)</code> dari tabel <code>loans</code> tipe <code>OTHER</code></td>
      <td class="desc-human">Potongan cicilan pinjaman lainnya (pinjaman perusahaan/piutang lain).</td>
    </tr>
    <tr>
      <td><code>[BPJS Employee]</code></td>
      <td><code>[bpjs_code]_employee</code> (JSON <code>bpjs_employee</code>)</td>
      <td>Iuran porsi karyawan: JHT (2%), JP (1%), KES (1%).</td>
      <td class="desc-human">Iuran BPJS Ketenagakerjaan dan Kesehatan yang dipotong langsung dari gaji karyawan.</td>
    </tr>
    <tr>
      <td><code>Total BPJS Employee</code></td>
      <td><code>bpjs_employee_total</code> (<code>$arr_bpjs_emp_amount_total</code>)</td>
      <td><code>SUM(Semua Potongan BPJS Porsi Karyawan)</code></td>
      <td class="desc-human">Total potongan BPJS yang dibebankan kepada gaji karyawan (Total 4%: JHT 2% + JP 1% + Kes 1%).</td>
    </tr>
    <tr>
      <td><code>TER</code> (Pajak PPh 21)</td>
      <td><code>ter</code></td>
      <td><code>(Bruto TER * Tarif TER %) / 100</code></td>
      <td class="desc-human">Potongan Pajak Penghasilan Pasal 21 bulanan menggunakan metode Tarif Efektif Rata-Rata (TER) pemerintah.</td>
    </tr>
    <tr>
      <td><code>Net Income</code></td>
      <td><code>net_income</code> (<code>$roundupIncome</code>)</td>
      <td>
        <code>$netincome = Income - TER - Potongan Rutin - BPJS Employee - Total Pinjaman</code><br>
        <code>$roundupIncome = CEIL($netincome / 500) * 500</code>
      </td>
      <td class="desc-human">
        <strong>Gaji Bersih / Uang yang Diterima Karyawan (Take Home Pay):</strong><br>
        Dihitung dari Income bersih dikurangi Pajak TER, Potongan Rutin, BPJS Karyawan, dan Cicilan Pinjaman. Kemudian hasilnya <strong>dibulatkan ke atas ke kelipatan Rp 500 terdekat</strong> (contoh: Rp 4.500.200 menjadi Rp 4.500.500).
      </td>
    </tr>
  </tbody>
</table>

<h2>7. Alur Ringkas Perhitungan Take Home Pay</h2>
<ol>
  <li><strong>Langkah 1:</strong> Hitung <code>Bruto Income</code> = (Gaji Pokok + Tunjangan + Lembur + BPJS Perusahaan + Koreksi Tambah).</li>
  <li><strong>Langkah 2:</strong> Hitung <code>Income</code> = (Bruto Income - Potongan Absen/Alpha/Izin Potong Gaji - BPJS Perusahaan).</li>
  <li><strong>Langkah 3:</strong> Hitung <code>Gaji Bersih (Net Income)</code> = (Income - Pajak TER - Potongan Rutin/Koperasi - BPJS Karyawan - Cicilan Pinjaman).</li>
  <li><strong>Langkah 4:</strong> Lakukan pembulatan ratusan ke atas (kelipatan Rp 500) untuk mempermudah transfer bank.</li>
</ol>

</body>
</html>