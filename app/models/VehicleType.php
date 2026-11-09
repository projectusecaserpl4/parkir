<?php
class VehicleType extends Model
{
    public function all(): array
    {
        return $this->db->query(
            "SELECT * FROM vehicle_types ORDER BY id_jenis_kendaraan DESC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM vehicle_types WHERE id_jenis_kendaraan = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function nameExists(string $name, int $ignoreId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM vehicle_types
             WHERE LOWER(nama_jenis) = LOWER(?) AND id_jenis_kendaraan <> ? LIMIT 1"
        );
        $stmt->execute([$name, $ignoreId]);
        return (bool) $stmt->fetchColumn();
    }

    public function usage(int $id): array
    {
        $stmt = $this->db->prepare(
            "SELECT (SELECT COUNT(*) FROM vehicles WHERE id_jenis_kendaraan = ?) AS kendaraan,
                    (SELECT COUNT(*) FROM parking_rates WHERE id_jenis_kendaraan = ?) AS tarif"
        );
        $stmt->execute([$id, $id]);
        $row = $stmt->fetch();
        return ['kendaraan' => (int) $row['kendaraan'], 'tarif' => (int) $row['tarif']];
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO vehicle_types (nama_jenis, deskripsi) VALUES (?, ?)"
        );
        return $stmt->execute([
            $data['nama_jenis'],
            $data['deskripsi'] !== '' ? $data['deskripsi'] : null
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE vehicle_types SET nama_jenis = ?, deskripsi = ?
             WHERE id_jenis_kendaraan = ?"
        );
        return $stmt->execute([
            $data['nama_jenis'],
            $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare(
                "DELETE FROM vehicle_types WHERE id_jenis_kendaraan = ?"
            );
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
