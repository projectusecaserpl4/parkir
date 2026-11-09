<?php
class ParkingArea extends Model
{
    public function all(): array
    {
        return $this->db->query(
            "SELECT pa.*,
                    (SELECT COUNT(*) FROM parking_slots ps WHERE ps.id_area = pa.id_area) AS jumlah_slot
             FROM parking_areas pa
             ORDER BY pa.id_area DESC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM parking_areas WHERE id_area = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function nameExists(string $name, int $ignoreId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM parking_areas
             WHERE LOWER(nama_area) = LOWER(?) AND id_area <> ? LIMIT 1"
        );
        $stmt->execute([$name, $ignoreId]);
        return (bool) $stmt->fetchColumn();
    }

    public function slotCount(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM parking_slots WHERE id_area = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO parking_areas (nama_area, lokasi, kapasitas, status)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['nama_area'], $data['lokasi'] !== '' ? $data['lokasi'] : null,
            $data['kapasitas'], $data['status']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE parking_areas SET nama_area = ?, lokasi = ?,
             kapasitas = ?, status = ? WHERE id_area = ?"
        );
        return $stmt->execute([
            $data['nama_area'], $data['lokasi'] !== '' ? $data['lokasi'] : null,
            $data['kapasitas'], $data['status'], $id
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM parking_areas WHERE id_area = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
