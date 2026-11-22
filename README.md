# AI-Powered Nutrition Analyzer System
### Sistem Estimasi Persentase Nutrisi Berbasis Kamera Smartphone & Kalibrasi Human-in-the-Loop (HITL)
**Universitas Klabat (UNKLAB) — Airmadidi, Minahasa Utara, Sulawesi Utara**

---

## 🎯 Ringkasan Eksekutif & Tujuan Sistem

Platform digital modern yang dirancang untuk sivitas akademika dan masyarakat di lingkungan **Universitas Klabat (UNKLAB)**. Sistem ini memudahkan mahasiswa, dosen, petugas dining hall/kantin, serta warga kampus untuk mengecek estimasi kandungan gizi makanan secara instan hanya menggunakan **kamera smartphone tanpa perlu registrasi/login**.

Sistem menghitung perkiraan **total kalori (kkal)**, berat porsi (gram), dan **distribusi persentase makronutrien utama (Karbohidrat, Protein, dan Lemak)** berdasarkan **faktor Atwater** ($1\text{g } C = 4\text{ kkal}, 1\text{g } P = 4\text{ kkal}, 1\text{g } F = 9\text{ kkal}$) sehingga total persentase tepat 100%, serta kandungan serat pangan.

### ⚖️ Aturan Eskalasi Human-in-the-Loop (Ambang Batas 70% Confidence)
- **Skor Keyakinan $\ge$ 70%**: Hasil dianggap valid, berstatus `auto_approved` (*Terverifikasi AI*), dan langsung ditampilkan penuh kepada pengguna.
- **Skor Keyakinan < 70%**: Dipicu apabila foto buram, pencahayaan gelap, atau komponen lauk tertutup kuah tebal. Sistem menyimpan status `pending_admin_review` (*Menunggu Verifikasi Admin*), mencatat alasan ketidakpastian (`ai_uncertainty_reason`), dan meneruskan tiket ke antrean **Administrator** untuk dikalibrasi secara manual (*Admin Corrected*).
- **Mekanisme Fallback Lokal**: Apabila `GEMINI_API_KEY` belum dikonfigurasi atau server offline, sistem otomatis beralih ke *Local Heuristic Nutrition Parser* berbasis data kuliner lokal (Tinutuan, Cakalang Fufu, Ayam Woku, Kuah Asam) dan menetapkan skor < 70% agar masuk antrean intervensi Administrator.

---

## 👥 Hak Akses Pengguna (Role-Based Access Control)

| Peran Pengguna | Status Login | Hak Akses & Fitur Operasional |
| :--- | :--- | :--- |
| **Public User** *(Mahasiswa, Dosen, Warga)* | **Tanpa Login** *(Open Access)* | Akses langsung kamera smartphone (WebRTC), pemotretan piring makanan, visualisasi grafik makronutrien, serta membaca Papan Menu Harian UNKLAB. |
| **Petugas** *(Staf Kantin / Dining Hall)* | **Wajib Login** *(RBAC: `petugas`)* | **Staff Nutrition Checker** untuk uji mutu hidangan sampel, **Manual Menu Builder** dengan bantuan hitung otomatis Atwater/TKPI, dan toggle ketersediaan (*Tersedia/Habis*) 1-klik. |
| **Administrator** *(Pengelola Sistem)* | **Wajib Login** *(Super RBAC: `admin`)* | **Executive Operations Dashboard**, **Antrean Intervensi HITL (< 70%)**, **Alat Kalibrasi Nutrisi** (*Admin Corrected*), **Master Data Pangan TKPI**, dan manajemen akun staf. |

### 🔑 Kredensial Login Demo
- **Administrator**: `admin` / `admin123`
- **Petugas Kantin**: `petugas` / `petugas123`
- Halaman Login: `http://localhost/template-final-project-ai/login/`

---

## 🎨 Identitas Visual & Palet Warna Universitas Klabat

| Elemen Warna | Kode Hex | Penerapan pada Antarmuka Sistem |
| :--- | :--- | :--- |
| **UNKLAB Navy Blue** | `#0A2240` | Header navigasi utama, sidebar portal, judul bagian, kartu total energi. |
| **Royal Blue** | `#163A6C` | Sub-judul, status aktif elemen, grafik batang karbohidrat & protein. |
| **UNKLAB Gold** | `#F2A900` | Tombol pemicu utama scan kamera, badge antrean HITL, aksen status penting. |
| **Clean White & Slate** | `#FFFFFF` / `#F8FAFC` | Latar belakang permukaan kontras tinggi ramah layar smartphone outdoor. |

---

## 🚀 Panduan Menjalankan Sistem

1. **Pastikan Web Server XAMPP Aktif**:
   Buka **XAMPP Control Panel**, lalu klik **Start** pada modul **Apache** dan **MySQL**.

2. **Buka Aplikasi di Browser**:
   Akses URL:
   👉 **`http://localhost/template-final-project-ai/`**

3. **Inisialisasi Database (1-Klik Otomatis)**:
   - Jika database `db_project` belum ada, buka halaman utama dan klik tombol **"Buat Database & Tabel Otomatis"**.
   - Atau import file `database/db_project.sql` melalui **phpMyAdmin** (`http://localhost/phpmyadmin`).

4. **Konfigurasi Gemini Vision API (Opsional)**:
   - Salin file `.env.example` menjadi `.env`.
   - Masukkan kunci API Google Gemini Anda:
     ```env
     GEMINI_API_KEY=AIzaSy...
     GEMINI_MODEL=gemini-2.5-flash
     ```
   - *Catatan*: Jika `GEMINI_API_KEY` tidak diisi, sistem tetap berjalan normal 100% menggunakan **Local Heuristic Fallback Engine** dan secara cerdas mensimulasikan alur antrean kalibrasi Human-in-the-Loop (< 70%).

---

## 📁 Struktur Direktori MVC

```text
template-final-project-ai/
├── app/
│   ├── config/config.php        # Konfigurasi database, konstanta warna UNKLAB, threshold 70%
│   ├── controller/
│   │   ├── admin.php            # Executive dashboard, antrean HITL < 70%, kalibrasi, master pangan, staff
│   │   ├── landing.php          # Zero-friction public camera scanner & papan menu UNKLAB
│   │   ├── login.php            # Autentikasi RBAC Admin & Petugas
│   │   ├── menu.php             # Papan menu harian publik
│   │   ├── petugas.php          # Portal staf kantin, nutrition checker, manual menu builder
│   │   ├── scan.php             # Pemrosesan scan dan halaman rincian
│   │   └── setup.php            # Auto-installer database db_project
│   ├── core/
│   │   ├── App.php              # Router & gateway WAF
│   │   ├── Controller.php       # Base controller & security headers
│   │   ├── Database.php         # MySQLi/PDO bridge & connection diagnostics
│   │   ├── GeminiService.php    # Gemini Vision API & Local Heuristic Fallback
│   │   └── Security.php         # Bcrypt hashing & sanitization
│   ├── model/
│   │   ├── Daily_menu_model.php     # Model menu harian kantin UNKLAB
│   │   ├── Food_reference_model.php # Model master data pangan TKPI & Sulut
│   │   ├── Nutrition_scan_model.php # Model scan nutrisi & antrean HITL
│   │   └── User_model.php           # Model otentikasi staf & admin
│   └── view/
│       ├── admin/               # Tampilan dashboard, queue HITL, kalibrasi, pangan, staff
│       ├── landing/             # Tampilan mobile-first WebRTC camera scanner
│       ├── menu/                # Tampilan papan menu publik
│       ├── petugas/             # Tampilan portal staf, checker, form builder
│       └── template/            # Header dan footer palet warna UNKLAB
├── database/
│   └── db_project.sql           # Skema tabel MySQL & seed data lengkap
├── public/
│   ├── api/
│   │   ├── admin_review.php     # REST API kalibrasi HITL administrator
│   │   ├── menus.php            # REST API menu harian
│   │   └── scan.php             # REST API penerima foto kamera & analisis gizi
│   ├── assets/                  # Bootstrap 5 & Bootstrap Icons lokal
│   └── uploads/
│       ├── menus/               # Foto menu harian
│       └── scans/               # Foto tangkapan kamera makanan
├── .env.example                 # Contoh konfigurasi environment
├── .htaccess                    # URL routing & perlindungan direktori
└── index.php                    # Front controller utama
```
