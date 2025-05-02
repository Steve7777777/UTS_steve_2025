<?php
namespace SMKApp\Models;

/**
 * Kelas Murid yang meng-extends Model
 * Dengan enkapsulasi private properti, getter/setter
 * override method search untuk pencarian nama murid (polymorphism)
 */
class Murid extends Model
{
    private ?int $id = null;
    private string $nama;
    private int $kelas_id;
    private string $alamat;
    private string $tanggal_lahir;

    protected string $table = 'murid';

    public function __construct(\mysqli $conn)
    {
        parent::__construct($conn);
    }

    /**
     * Mencari murid berdasar nama
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

    // Getter dan Setter enkapsulasi
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
    public function setKelasId(int $kelas_id): void
    {
        $this->kelas_id = $kelas_id;
    }
    public function getKelasId(): int
    {
        return $this->kelas_id;
    }
    public function setAlamat(string $alamat): void
    {
        $this->alamat = $alamat;
    }
    public function getAlamat(): string
    {
        return $this->alamat;
    }
    public function setTanggalLahir(string $tanggal_lahir): void
    {
        $this->tanggal_lahir = $tanggal_lahir;
    }
    public function getTanggalLahir(): string
    {
        return $this->tanggal_lahir;
    }
}
?>
