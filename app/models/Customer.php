<?php
class Customer extends Model
{
    public function all(): array
    {
        return $this->db->query(
            "SELECT * FROM customers ORDER BY id_customer DESC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id_customer = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function nikExists(string $nik, int $ignoreId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM customers WHERE nik = ? AND id_customer <> ? LIMIT 1"
        );
        $stmt->execute([$nik, $ignoreId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Customer dengan nama yang sama (tanpa membedakan huruf besar/kecil) dan nomor HP yang sama. */
    public function duplicateExists(string $name, string $phone, int $ignoreId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM customers
             WHERE LOWER(nama_customer) = LOWER(?)
               AND COALESCE(no_hp, '') = ?
               AND id_customer <> ?
             LIMIT 1"
        );
        $stmt->execute([$name, $phone, $ignoreId]);
        return (bool) $stmt->fetchColumn();
    }

    public function vehicleCount(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM vehicles WHERE id_customer = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO customers (nama_customer, nik, no_hp, alamat)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['nama_customer'],
            $data['nik'] !== '' ? $data['nik'] : null,
            $data['no_hp'] !== '' ? $data['no_hp'] : null,
            $data['alamat'] !== '' ? $data['alamat'] : null
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE customers
             SET nama_customer = ?, nik = ?, no_hp = ?, alamat = ?
             WHERE id_customer = ?"
        );
        return $stmt->execute([
            $data['nama_customer'],
            $data['nik'] !== '' ? $data['nik'] : null,
            $data['no_hp'] !== '' ? $data['no_hp'] : null,
            $data['alamat'] !== '' ? $data['alamat'] : null,
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM customers WHERE id_customer = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
