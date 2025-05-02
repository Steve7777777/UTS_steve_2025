<?php
namespace SMKApp\Models;

/**
 * Kelas Jurusan yang meng-extends Model
 * Memanfaatkan inheritance, polymorphism (method search override)
 * dan enkapsulasi private properti dengan getter/setter
 */
class Jurusan extends Model
{
    private ?int $id = null;
    private string $nama;

    protected string $table = 'jurusan';

    public function __construct(\mysqli $conn)
    {
        parent::__construct($conn);
    }

    /**
     * Override method search untuk mencari jurusan berdasarkan nama
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

    // Getter dan Setter untuk properti, encapsulation
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
}
?>
