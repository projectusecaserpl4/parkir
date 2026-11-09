<?php
class ParkingRate extends Model
{
    public function all(): array
    {
        return $this->db->query(
            "SELECT pr.*, vt.nama_jenis,
                    (SELECT COUNT(*) FROM parking_payments pp WHERE pp.id_tarif = pr.id_tarif) AS dipakai
             FROM parking_rates pr
             INNER JOIN vehicle_types vt
             ON vt.id_jenis_kendaraan = pr.id_jenis_kendaraan
             ORDER BY pr.id_tarif DESC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM parking_rates WHERE id_tarif = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function paymentCount(int $id): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM parking_payments WHERE id_tarif = ?");
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO parking_rates
             (id_jenis_kendaraan, tarif_per_jam, tarif_maksimal, berlaku_mulai, status)
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['id_jenis_kendaraan'],
            $data['tarif_per_jam'],
            $data['tarif_maksimal'],
            $data['berlaku_mulai'],
            $data['status']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE parking_rates SET id_jenis_kendaraan = ?,
             tarif_per_jam = ?, tarif_maksimal = ?,
             berlaku_mulai = ?, status = ? WHERE id_tarif = ?"
        );
        return $stmt->execute([
            $data['id_jenis_kendaraan'],
            $data['tarif_per_jam'],
            $data['tarif_maksimal'],
            $data['berlaku_mulai'],
            $data['status'],
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM parking_rates WHERE id_tarif = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
