<?php
namespace SMKApp;

use SMKApp\Database\DB;
use SMKApp\Models\Jurusan;
use SMKApp\Models\Kelas;
use SMKApp\Models\Murid;

require_once __DIR__ . '/autoload.php';

session_start();

// Ganti dengan konfigurasi database Anda
$db = new DB('localhost', 'root', '', 'smkdb');
$mysqli = $db->getConnection();

$jurusanModel = new Jurusan($mysqli);
$kelasModel = new Kelas($mysqli);
$muridModel = new Murid($mysqli);

$entity = $_GET['entity'] ?? 'murid';
$action = $_GET['action'] ?? 'list';

ob_start();

switch ($entity) {
  case 'jurusan':
    handleJurusan($action, $jurusanModel);
    break;
  case 'kelas':
    handleKelas($action, $kelasModel, $jurusanModel);
    break;
  case 'murid':
  default:
    handleMurid($action, $muridModel, $kelasModel);
    break;
}

$content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Aplikasi CRUD SMK</title>
    <style>
      body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin:0; padding:0; background:#f0f2f5; color:#222; }
      header { background:#007bff; color:#fff; padding:1rem; text-align:center; }
      nav { background:#0056b3; padding:0.5rem; display:flex; justify-content:center; flex-wrap:wrap; }
      nav a { color:#fff; margin:0 1rem; text-decoration:none; font-weight:600; padding:0.3rem 0.6rem; border-radius:4px; }
      nav a.active, nav a:hover { background:#003d80; }
      main { max-width:900px; margin:1rem auto; padding:1rem; background:#fff; box-shadow:0 0 10px rgba(0,0,0,0.1); }
      table { border-collapse:collapse; width:100%; margin-top:1rem; }
      th, td { border:1px solid #ddd; padding:0.5rem; text-align:left; }
      th { background:#007bff; color:#fff;}
      form { margin-top:1rem; }
      label { display:block; margin-top:0.5rem; font-weight:bold; }
      input[type="text"], select, textarea, input[type="date"] { width:100%; padding:0.5rem; margin-top:0.3rem; border:1px solid #bbb; border-radius:4px; }
      button { margin-top:1rem; padding:0.6rem 1.2rem; border:none; background:#007bff; color:#fff; border-radius:4px; cursor:pointer; font-weight:600; }
      button:hover { background:#0056b3; }
      .actions a { margin-right:0.5rem; color:#007bff; text-decoration:none; }
      .actions a:hover { text-decoration:underline; }
      .search-form { display:flex; max-width:400px; margin:1rem 0; }
      .search-form input[type="text"] { flex-grow:1; }
      .search-form button { flex-shrink:0; }
      .error { color:red; font-weight:bold; }
      @media (max-width: 600px) {
        nav { flex-direction: column; }
        nav a { margin: 0.3rem 0; }
        .search-form { flex-direction: column; }
        .search-form input[type="text"], .search-form button { width: 100%; margin: 0.3rem 0 0 0; }
      }
    </style>
</head>
<body>
<header>
    <h1>Aplikasi CRUD SMK</h1>
    <p>Kelola Murid, Kelas dan Jurusan SMK</p>
</header>
<nav>
    <a href="?entity=murid" class="<?= $entity === 'murid' ? 'active' : ''; ?>">Kelola Murid</a>
    <a href="?entity=kelas" class="<?= $entity === 'kelas' ? 'active' : ''; ?>">Kelola Kelas</a>
    <a href="?entity=jurusan" class="<?= $entity === 'jurusan' ? 'active' : ''; ?>">Kelola Jurusan</a>
</nav>
<main>
    <?= $content ?>
</main>
</body>
</html>

<?php

function req_post(string $key, $default = '') {
  return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function req_get(string $key, $default = '') {
  return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
}

function redirect(string $url) {
  header("Location: $url");
  exit;
}

// Handler Jurusan
function handleJurusan(string $action, Jurusan $model) {
  switch ($action) {
    case 'create':
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = req_post('nama');
        if ($nama === '') {
          echo "<p class='error'>Nama jurusan tidak boleh kosong.</p>";
        } else {
          $model->create(['nama' => $nama]);
          redirect('?entity=jurusan');
        }
      }
      formJurusan('Tambah Jurusan');
      break;
    case 'edit':
      $id = (int)req_get('id');
      $item = $model->readById($id);
      if (!$item) {
        echo "<p class='error'>Jurusan tidak ditemukan.</p>";
        return;
      }
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = req_post('nama');
        if ($nama === '') {
          echo "<p class='error'>Nama jurusan tidak boleh kosong.</p>";
          formJurusan('Edit Jurusan', $item);
          return;
        }
        $model->update($id, ['nama' => $nama]);
        redirect('?entity=jurusan');
      }
      formJurusan('Edit Jurusan', $item);
      break;
    case 'delete':
      $id = (int)req_get('id');
      $model->delete($id);
      redirect('?entity=jurusan');
      break;
    default:
      $search = req_get('search', '');
      $items = $search !== '' ? $model->search($search) : $model->readAll();
      listJurusan($items, $search);
      break;
  }
}

function formJurusan(string $title, ?array $item = null) {
  $nama = $item['nama'] ?? '';
  $id = $item['id'] ?? '';
  $actionUrl = $id ? "?entity=jurusan&action=edit&id=$id" : "?entity=jurusan&action=create";

  echo "<h2>$title</h2>";
  echo "<form method='post' action='$actionUrl'>
    <label>Nama Jurusan:</label>
    <input type='text' name='nama' value=\"" . htmlspecialchars($nama) . "\" required />
    <button type='submit'>Simpan</button>
    <a href='?entity=jurusan'>Batal</a>
  </form>";
}

function listJurusan(array $items, string $search) {
  echo "<h2>Daftar Jurusan</h2>";
  echo "<form method='get' class='search-form'>
    <input type='hidden' name='entity' value='jurusan' />
    <input type='text' name='search' placeholder='Cari jurusan...' value=\"" . htmlspecialchars($search) . "\" />
    <button type='submit'>Cari</button>
  </form>";
  echo "<a href='?entity=jurusan&action=create'>Tambah Jurusan Baru</a>";

  if (count($items) === 0) {
    echo "<p>Tidak ada data jurusan.</p>";
  } else {
    echo "<table><thead><tr><th>ID</th><th>Nama Jurusan</th><th>Aksi</th></tr></thead><tbody>";
    foreach ($items as $item) {
      echo "<tr>
        <td>{$item['id']}</td>
        <td>" . htmlspecialchars($item['nama']) . "</td>
        <td class='actions'>
          <a href='?entity=jurusan&action=edit&id={$item['id']}'>Edit</a>
          <a href='?entity=jurusan&action=delete&id={$item['id']}' onclick='return confirm(\"Yakin ingin hapus?\");'>Hapus</a>
        </td>
      </tr>";
    }
    echo "</tbody></table>";
  }
}

// Handler Kelas
function handleKelas(string $action, Kelas $model, Jurusan $jurusanModel) {
  switch ($action) {
    case 'create':
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = req_post('nama');
        $jurusan_id = (int)req_post('jurusan_id');
        if ($nama === '' || $jurusan_id <= 0) {
          echo "<p class='error'>Nama kelas dan jurusan harus diisi.</p>";
        } else {
          $model->create(['nama' => $nama, 'jurusan_id' => $jurusan_id]);
          redirect('?entity=kelas');
        }
      }
      formKelas('Tambah Kelas', null, $jurusanModel);
      break;
    case 'edit':
      $id = (int)req_get('id');
      $item = $model->readById($id);
      if (!$item) {
        echo "<p class='error'>Kelas tidak ditemukan.</p>";
        return;
      }
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = req_post('nama');
        $jurusan_id = (int)req_post('jurusan_id');
        if ($nama === '' || $jurusan_id <= 0) {
          echo "<p class='error'>Nama kelas dan jurusan harus diisi.</p>";
          formKelas('Edit Kelas', $item, $jurusanModel);
          return;
        }
        $model->update($id, ['nama' => $nama, 'jurusan_id' => $jurusan_id]);
        redirect('?entity=kelas');
      }
      formKelas('Edit Kelas', $item, $jurusanModel);
      break;
    case 'delete':
      $id = (int)req_get('id');
      $model->delete($id);
      redirect('?entity=kelas');
      break;
    default:
      $search = req_get('search', '');
      $items = $search !== '' ? $model->search($search) : $model->readAll();
      listKelas($items, $jurusanModel, $search);
      break;
  }
}

function formKelas(string $title, ?array $item = null, Jurusan $jurusanModel) {
  $nama = $item['nama'] ?? '';
  $jurusan_id = $item['jurusan_id'] ?? 0;
  $id = $item['id'] ?? '';
  $jurusanList = $jurusanModel->readAll();

  $actionUrl = $id ? "?entity=kelas&action=edit&id=$id" : "?entity=kelas&action=create";

  echo "<h2>$title</h2>";
  echo "<form method='post' action='$actionUrl'>
    <label>Nama Kelas:</label>
    <input type='text' name='nama' value=\"" . htmlspecialchars($nama) . "\" required />

    <label>Jurusan:</label>
    <select name='jurusan_id' required>
      <option value=''>-- Pilih Jurusan --</option>";
      foreach ($jurusanList as $j) {
        $sel = ($j['id'] == $jurusan_id) ? 'selected' : '';
        echo "<option value='{$j['id']}' $sel>" . htmlspecialchars($j['nama']) . "</option>";
      }
  echo "</select>
    <button type='submit'>Simpan</button>
    <a href='?entity=kelas'>Batal</a>
  </form>";
}

function listKelas(array $items, Jurusan $jurusanModel, string $search) {
  echo "<h2>Daftar Kelas</h2>";
  echo "<form method='get' class='search-form'>
    <input type='hidden' name='entity' value='kelas' />
    <input type='text' name='search' placeholder='Cari kelas...' value=\"" . htmlspecialchars($search) . "\" />
    <button type='submit'>Cari</button>
  </form>";
  echo "<a href='?entity=kelas&action=create'>Tambah Kelas Baru</a>";
  if (count($items) === 0) {
    echo "<p>Tidak ada data kelas.</p>";
  } else {
    $jurusanMap = [];
    foreach ($jurusanModel->readAll() as $j) {
      $jurusanMap[$j['id']] = $j['nama'];
    }
    echo "<table><thead><tr><th>ID</th><th>Nama Kelas</th><th>Jurusan</th><th>Aksi</th></tr></thead><tbody>";
    foreach ($items as $item) {
      $jurusanName = $jurusanMap[$item['jurusan_id']] ?? '-';
      echo "<tr>
        <td>{$item['id']}</td>
        <td>" . htmlspecialchars($item['nama']) . "</td>
        <td>" . htmlspecialchars($jurusanName) . "</td>
        <td class='actions'>
          <a href='?entity=kelas&action=edit&id={$item['id']}'>Edit</a>
          <a href='?entity=kelas&action=delete&id={$item['id']}' onclick='return confirm(\"Yakin ingin hapus?\");'>Hapus</a>
        </td>
      </tr>";
    }
    echo "</tbody></table>";
  }
}

// Handler Murid
function handleMurid(string $action, Murid $model, Kelas $kelasModel) {
  switch ($action) {
    case 'create':
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = req_post('nama');
        $kelas_id = (int)req_post('kelas_id');
        $alamat = req_post('alamat');
        $tanggal_lahir = req_post('tanggal_lahir');
        if ($nama === '' || $kelas_id <= 0) {
          echo "<p class='error'>Nama murid dan kelas harus diisi.</p>";
        } else {
          $model->create([
            'nama' => $nama,
            'kelas_id' => $kelas_id,
            'alamat' => $alamat,
            'tanggal_lahir' => $tanggal_lahir
          ]);
          redirect('?entity=murid');
        }
      }
      formMurid('Tambah Murid', null, $kelasModel);
      break;
    case 'edit':
      $id = (int)req_get('id');
      $item = $model->readById($id);
      if (!$item) {
        echo "<p class='error'>Murid tidak ditemukan.</p>";
        return;
      }
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama = req_post('nama');
        $kelas_id = (int)req_post('kelas_id');
        $alamat = req_post('alamat');
        $tanggal_lahir = req_post('tanggal_lahir');
        if ($nama === '' || $kelas_id <= 0) {
          echo "<p class='error'>Nama murid dan kelas harus diisi.</p>";
          formMurid('Edit Murid', $item, $kelasModel);
          return;
        }
        $model->update($id, [
          'nama' => $nama,
          'kelas_id' => $kelas_id,
          'alamat' => $alamat,
          'tanggal_lahir' => $tanggal_lahir
        ]);
        redirect('?entity=murid');
      }
      formMurid('Edit Murid', $item, $kelasModel);
      break;
    case 'delete':
      $id = (int)req_get('id');
      $model->delete($id);
      redirect('?entity=murid');
      break;
    default:
      $search = req_get('search', '');
      $items = $search !== '' ? $model->search($search) : $model->readAll();
      listMurid($items, $kelasModel, $search);
      break;
  }
}

function formMurid(string $title, ?array $item = null, Kelas $kelasModel) {
  $nama = $item['nama'] ?? '';
  $kelas_id = $item['kelas_id'] ?? 0;
  $alamat = $item['alamat'] ?? '';
  $tanggal_lahir = $item['tanggal_lahir'] ?? '';
  $id = $item['id'] ?? '';
  $kelasList = $kelasModel->readAll();

  $actionUrl = $id ? "?entity=murid&action=edit&id=$id" : "?entity=murid&action=create";

  echo "<h2>$title</h2>";
  echo "<form method='post' action='$actionUrl'>
    <label>Nama Murid:</label>
    <input type='text' name='nama' value=\"" . htmlspecialchars($nama) . "\" required />

    <label>Kelas:</label>
    <select name='kelas_id' required>
      <option value=''>-- Pilih Kelas --</option>";
      foreach ($kelasList as $k) {
        $sel = ($k['id'] == $kelas_id) ? 'selected' : '';
        echo "<option value='{$k['id']}' $sel>" . htmlspecialchars($k['nama']) . "</option>";
      }
  echo "</select>

    <label>Alamat:</label>
    <textarea name='alamat' rows='3'>" . htmlspecialchars($alamat) . "</textarea>

    <label>Tanggal Lahir:</label>
    <input type='date' name='tanggal_lahir' value=\"" . htmlspecialchars($tanggal_lahir) . "\" />

    <button type='submit'>Simpan</button>
    <a href='?entity=murid'>Batal</a>
  </form>";
}

function listMurid(array $items, Kelas $kelasModel, string $search) {
  echo "<h2>Daftar Murid</h2>";
  echo "<form method='get' class='search-form'>
    <input type='hidden' name='entity' value='murid' />
    <input type='text' name='search' placeholder='Cari murid...' value=\"" . htmlspecialchars($search) . "\" />
    <button type='submit'>Cari</button>
  </form>";
  echo "<a href='?entity=murid&action=create'>Tambah Murid Baru</a>";
  if (count($items) === 0) {
    echo "<p>Tidak ada data murid.</p>";
  } else {
    $kelasMap = [];
    foreach ($kelasModel->readAll() as $k) {
      $kelasMap[$k['id']] = $k['nama'];
    }
    echo "<table><thead><tr><th>ID</th><th>Nama</th><th>Kelas</th><th>Alamat</th><th>Tanggal Lahir</th><th>Aksi</th></tr></thead><tbody>";
    foreach ($items as $item) {
      $kelasName = $kelasMap[$item['kelas_id']] ?? '-';
      echo "<tr>
        <td>{$item['id']}</td>
        <td>" . htmlspecialchars($item['nama']) . "</td>
        <td>" . htmlspecialchars($kelasName) . "</td>
        <td>" . nl2br(htmlspecialchars($item['alamat'])) . "</td>
        <td>" . htmlspecialchars($item['tanggal_lahir']) . "</td>
        <td class='actions'>
          <a href='?entity=murid&action=edit&id={$item['id']}'>Edit</a>
          <a href='?entity=murid&action=delete&id={$item['id']}' onclick='return confirm(\"Yakin ingin hapus?\");'>Hapus</a>
        </td>
      </tr>";
    }
    echo "</tbody></table>";
  }
}
?>
