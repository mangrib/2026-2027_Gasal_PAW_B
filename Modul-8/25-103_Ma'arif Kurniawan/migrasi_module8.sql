USE penjualan;
-- Diperlukan agar password baru (password_hash / bcrypt, 60 karakter) bisa disimpan.
-- Password md5 lama tetap bisa login dan otomatis di-upgrade saat login.
ALTER TABLE user MODIFY password VARCHAR(255) NOT NULL;
