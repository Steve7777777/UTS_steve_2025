-- Database smkdb
CREATE DATABASE IF NOT EXISTS smkdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smkdb;

-- Tabel jurusan (jurusan SMK)
CREATE TABLE IF NOT EXISTS jurusan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL
);

-- Tabel kelas (kelas SMK)
CREATE TABLE IF NOT EXISTS kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    jurusan_id INT NOT NULL,
    FOREIGN KEY (jurusan_id) REFERENCES jurusan(id) ON DELETE CASCADE
);

-- Tabel murid (nama murid)
CREATE TABLE IF NOT EXISTS murid (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kelas_id INT NOT NULL,
    alamat TEXT,
    tanggal_lahir DATE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE
);

-- Data contoh jurusan
INSERT INTO jurusan (nama) VALUES ('Teknik Komputer Jaringan'), ('Rekayasa Perangkat Lunak'), ('Teknik Elektro');

-- Data contoh kelas
INSERT INTO kelas (nama, jurusan_id) VALUES
('TKJ 1', 1),
('TKJ 2', 1),
('RPL 1', 2),
('RPL 2', 2),
('TE 1', 3);

-- Data contoh murid
INSERT INTO murid (nama, kelas_id, alamat, tanggal_lahir) VALUES
('Ahmad Fauzi', 1, 'Jl. Mawar No.12', '2005-02-13'),
('Siti Nurhaliza', 3, 'Jl. Melati No.4', '2005-05-21'),
('Budi Setiawan', 5, 'Jl. Kenanga No.7', '2004-12-11');
