<?php
class Vehicle extends Model
{
    public function all(): array
    {
        return $this->db->query(
            "SELECT v.*, c.nama_customer, vt.nama_jenis
             FROM vehicles v
             INNER JOIN customers c ON c.id_customer = v.id_customer
             INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
             ORDER BY v.id_vehicle DESC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE id_vehicle = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function plateExists(string $plate, int $ignoreId = 0): bool
    {
        // Bandingkan tanpa spasi agar "D1234AB" dan "D 1234 AB" dianggap sama.
        $stmt = $this->db->prepare(
            "SELECT 1 FROM vehicles
             WHERE REPLACE(nomor_polisi, ' ', '') = ? AND id_vehicle <> ?
             LIMIT 1"
        );
        $stmt->execute([str_replace(' ', '', $plate), $ignoreId]);
        return (bool) $stmt->fetchColumn();
    }

    public function hasActiveParking(int $id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM parking_entries WHERE id_vehicle = ? AND status = 'parkir' LIMIT 1"
        );
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }

    public function entryCount(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM parking_entries WHERE id_vehicle = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO vehicles
             (id_customer, id_jenis_kendaraan, nomor_polisi, merk, model, warna, tahun, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['id_customer'],
            $data['id_jenis_kendaraan'],
            $data['nomor_polisi'],
            $data['merk'] !== '' ? $data['merk'] : null,
            $data['model'] !== '' ? $data['model'] : null,
            $data['warna'] !== '' ? $data['warna'] : null,
            $data['tahun'] !== '' ? (int) $data['tahun'] : null,
            $data['status']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE vehicles SET
             id_customer = ?, id_jenis_kendaraan = ?, nomor_polisi = ?,
             merk = ?, model = ?, warna = ?, tahun = ?, status = ?
             WHERE id_vehicle = ?"
        );
        return $stmt->execute([
            $data['id_customer'],
            $data['id_jenis_kendaraan'],
            $data['nomor_polisi'],
            $data['merk'] !== '' ? $data['merk'] : null,
            $data['model'] !== '' ? $data['model'] : null,
            $data['warna'] !== '' ? $data['warna'] : null,
            $data['tahun'] !== '' ? (int) $data['tahun'] : null,
            $data['status'],
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id_vehicle = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
