<?php
namespace SMKApp\Database;

/**
 * Kelas DB untuk koneksi database MySQL menggunakan mysqli
 * Menerapkan enkapsulasi (property private, method private dan public)
 */
class DB
{
    private string $host;
    private string $user;
    private string $pass;
    private string $dbname;
    private \mysqli $conn;

    public function __construct(string $host, string $user, string $pass, string $dbname)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->dbname = $dbname;

        $this->connect();
    }

    /**
     * Membuat koneksi mysqli ke database
     * Private method karena internal saja
     */
    private function connect()
    {
        $this->conn = new \mysqli($this->host, $this->user, $this->pass, $this->dbname);
        if ($this->conn->connect_error) {
            die("Koneksi database gagal: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    /**
     * Mengembalikan objek koneksi mysqli
     */
    public function getConnection(): \mysqli
    {
        return $this->conn;
    }
}
?>
