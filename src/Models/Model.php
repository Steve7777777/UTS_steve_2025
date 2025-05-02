<?php
namespace SMKApp\Models;

/**
 * Kelas Model abstrak dasar sebagai parent untuk model spesifik
 * Menerapkan inheritance dan polymorphism (method search abstrak)
 * Menerapkan enkapsulasi (property protected dan private method)
 */
abstract class Model
{
    protected \mysqli $conn;
    protected string $table;

    public function __construct(\mysqli $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Membuat data baru di tabel
     * @param array $data pasangan kolom-nilai
     * @return bool status sukses operasi
     */
    public function create(array $data): bool
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $types = $this->detectParamTypes($data);

        $stmt = $this->conn->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param($types, ...array_values($data));
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Membaca semua data dari tabel (tanpa filter)
     * @return array daftar data dalam array asosiatif
     */
    public function readAll(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $res = $this->conn->query($sql);
        if (!$res) {
            return [];
        }
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Membaca data berdasarkan id
     * @param int $id primary key
     * @return array|null data array asosiatif atau null jika tidak ditemukan
     */
    public function readById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        if (!$stmt) return null;
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $item = $res->fetch_assoc();
        $stmt->close();
        return $item ?: null;
    }

    /**
     * Mengupdate data berdasar id
     * @param int $id
     * @param array $data array pasangan kolom-nilai
     * @return bool status sukses operasi
     */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        foreach ($data as $key => $val) {
            $sets[] = "$key = ?";
        }
        $setStr = implode(',', $sets);
        $types = $this->detectParamTypes($data) . 'i';

        $stmt = $this->conn->prepare("UPDATE {$this->table} SET $setStr WHERE id = ?");
        if (!$stmt) {
            return false;
        }
        $values = array_values($data);
        $values[] = $id;
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Menghapus data berdasar id
     * @param int $id
     * @return bool status sukses operasi
     */
    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Method abstrak - harus diimplementasikan di child class
     * Untuk mencari data berdasarkan kata kunci (search)
     * @param string $keyword
     * @return array hasil pencarian (array asosiatif)
     */
    abstract public function search(string $keyword): array;

    /**
     * Mendeteksi tipe data parameter untuk prepared stmt ('s' string, 'i' integer)
     * @param array $data
     * @return string tipe parameter
     */
    protected function detectParamTypes(array $data): string
    {
        $types = '';
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } else {
                $types .= 's';
            }
        }
        return $types;
    }
}
?>
