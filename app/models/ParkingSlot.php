<?php
class ParkingSlot extends Model
{
    public function all(): array
    {
        return $this->db->query(
            "SELECT ps.*, pa.nama_area,
                    EXISTS (SELECT 1 FROM parking_entries pe
                            WHERE pe.id_slot = ps.id_slot AND pe.status = 'parkir') AS dipakai
             FROM parking_slots ps
             INNER JOIN parking_areas pa ON pa.id_area = ps.id_area
             ORDER BY ps.id_slot DESC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM parking_slots WHERE id_slot = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function codeExists(string $code, int $ignoreId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM parking_slots WHERE kode_slot = ? AND id_slot <> ? LIMIT 1"
        );
        $stmt->execute([$code, $ignoreId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Slot sedang dipakai kendaraan yang masih parkir. */
    public function hasActiveEntry(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM parking_entries WHERE id_slot = ? AND status = 'parkir' LIMIT 1"
        );
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }

    public function entryCount(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM parking_entries WHERE id_slot = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public function countInArea(int $areaId, int $ignoreSlotId = 0): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM parking_slots WHERE id_area = ? AND id_slot <> ?"
        );
        $stmt->execute([$areaId, $ignoreSlotId]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO parking_slots (id_area, kode_slot, jenis_kendaraan, status)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['id_area'], $data['kode_slot'],
            $data['jenis_kendaraan'], $data['status']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE parking_slots SET id_area = ?, kode_slot = ?,
             jenis_kendaraan = ?, status = ? WHERE id_slot = ?"
        );
        return $stmt->execute([
            $data['id_area'], $data['kode_slot'],
            $data['jenis_kendaraan'], $data['status'], $id
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM parking_slots WHERE id_slot = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
