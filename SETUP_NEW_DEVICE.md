# Setup Device Baru (Otomatis)

## Satu Perintah (disarankan)
Dari root repo, jalankan:

`powershell -NoProfile -ExecutionPolicy Bypass -File scripts/bootstrap-dev.ps1 -ProjectRoot .`

Script ini otomatis akan:
- memastikan `backend/.env` ada (copy dari `.env.example` jika belum),
- install dependency Composer,
- generate `APP_KEY`,
- membuat database PostgreSQL jika belum ada,
- menjalankan `php artisan migrate`.

## Opsi Restore Data Lama
Jika Anda punya file dump `.dump`, jalankan:

`powershell -NoProfile -ExecutionPolicy Bypass -File scripts/bootstrap-dev.ps1 -ProjectRoot . -RestoreDumpPath "C:\path\mbg_inventory.dump"`

## Opsi Seed Data
Untuk isi data awal dari seeder:

`powershell -NoProfile -ExecutionPolicy Bypass -File scripts/bootstrap-dev.ps1 -ProjectRoot . -Seed`

## Prasyarat
- PHP aktif di terminal
- PostgreSQL terpasang dan service running
- Kredensial database benar di `backend/.env`

## VS Code Task
Anda juga bisa jalankan:
- `Tasks: Run Task` -> `Bootstrap Dev Device`
