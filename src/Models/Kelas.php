<?php
namespace SMKApp\Models;

/**
 * Kelas Kelas yang meng-extends Model
 * Melibatkan enkapsulasi serta override method search (polymorphism)
 */
class Kelas extends Model
{
    private ?int $id = null;
    private string $nama;
    private int $jurusan_id;

    protected string $table = 'kelas';

    public function __construct(\mysqli $conn)
    {
        parent::__construct($conn);
    }

    /**
     * Mencari kelas berdasarkan nama (polymorphism)
     */
    public function search(string $keyword): array
    {
        $keyword = "%{$keyword}%";
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE nama LIKE ?");
        $stmt->bind_param('s', $keyword);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $items;
    }

    // Getter dan Setter (enkapsulasi)
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setNama(string $nama): void
    {
        $this->nama = $nama;
    }
    public function getNama(): string
    {
        return $this->nama;
    }
    public function setJurusanId(int $jurusan_id): void
    {
        $this->jurusan_id = $jurusan_id;
    }
    public function getJurusanId(): int
    {
        return $this->jurusan_id;
    }
}
?>
