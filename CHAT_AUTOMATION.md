# Otomatis Pindah Sesi Chat

Tujuan: saat chat lama penuh, Anda bisa lanjut di chat baru tanpa kehilangan konteks proyek.

## Cara Pakai (Paling Cepat)
1. Selesai kerja, jalankan task VS Code: `Tasks: Run Task` -> `Generate Chat Handover`.
2. File `CHAT_CONTEXT_AUTO.md` akan ter-update otomatis.
3. Buka chat baru.
4. Kirim pesan: "Baca `CHAT_CONTEXT_AUTO.md` lalu lanjutkan next task."

## Full Otomatis (Disarankan untuk solo developer)
Aktifkan Git hook sekali saja dari root repo:

`git config core.hooksPath .githooks`

Setelah aktif:
- Setiap kali Anda `git commit`, file `CHAT_CONTEXT_AUTO.md` akan ter-generate otomatis.
- Jadi Anda tidak wajib lagi menjalankan task manual sebelum pindah chat.

### Sumber Next Task otomatis
Generator akan mengambil Next Task dengan urutan prioritas berikut:
1. Parameter manual `-NextTask` saat script dijalankan.
2. Isi file `NEXT_TASK.txt` (jika ada dan tidak kosong).
3. Task pertama yang belum selesai dari `TODO.md` / `TODO.txt` / `TASKS.md`.
4. Fallback teks default.

Tips: jika Anda ingin full otomatis tanpa mengetik apa pun, cukup maintain `NEXT_TASK.txt` atau checklist `TODO.md`.

## Opsi dengan perintah manual
Jalankan dari root repo:

`powershell -NoProfile -ExecutionPolicy Bypass -File scripts/generate-chat-context.ps1 -OutputFile CHAT_CONTEXT_AUTO.md -NextTask "isi target sesi berikutnya"`

## Kapan dijalankan
- Setiap sebelum tutup sesi kerja.
- Setiap sebelum pindah ke chat baru.
- Setelah ada perubahan besar (fitur, refactor, atau deploy).

Jika mode full otomatis sudah aktif, file akan selalu ikut terbarui setiap selesai commit.

## Catatan
- Script mengambil status Git aktual: branch, commit terakhir, perubahan staged/unstaged, dan daftar commit terbaru.
- Pastikan commit/push rutin agar konteks makin akurat.
