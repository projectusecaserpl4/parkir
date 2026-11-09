<?php

class ParkingEntry extends Model
{
    public function active(): array
    {
        $sql = "SELECT pe.*, v.nomor_polisi, v.merk, c.nama_customer,
                       vt.nama_jenis, ps.kode_slot, pa.nama_area,
                       u.nama_lengkap AS petugas
                FROM parking_entries pe
                INNER JOIN vehicles v ON v.id_vehicle = pe.id_vehicle
                INNER JOIN customers c ON c.id_customer = v.id_customer
                INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
                LEFT JOIN parking_slots ps ON ps.id_slot = pe.id_slot
                LEFT JOIN parking_areas pa ON pa.id_area = ps.id_area
                INNER JOIN users u ON u.id_user = pe.id_user
                WHERE pe.status = 'parkir'
                ORDER BY pe.waktu_masuk DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function availableSlots(string $jenisKendaraan): array
    {
        $stmt = $this->db->prepare(
            "SELECT ps.id_slot, ps.kode_slot, ps.jenis_kendaraan,
                    pa.nama_area
             FROM parking_slots ps
             INNER JOIN parking_areas pa ON pa.id_area = ps.id_area
             WHERE ps.jenis_kendaraan = ?
               AND ps.status = 'kosong'
               AND pa.status = 'tersedia'
             ORDER BY pa.nama_area, ps.kode_slot"
        );
        $stmt->execute([$jenisKendaraan]);

        return $stmt->fetchAll();
    }

    public function allAvailableSlots(): array
    {
        return $this->db->query(
            "SELECT ps.id_slot, ps.kode_slot, ps.jenis_kendaraan, pa.nama_area
             FROM parking_slots ps
             INNER JOIN parking_areas pa ON pa.id_area = ps.id_area
             WHERE ps.status = 'kosong' AND pa.status = 'tersedia'
             ORDER BY pa.nama_area, ps.kode_slot"
        )->fetchAll();
    }

    public function vehicleOptions(): array
    {
        $sql = "SELECT v.id_vehicle, v.nomor_polisi, v.merk, v.model,
                       c.nama_customer, vt.nama_jenis,
                       vt.nama_jenis AS jenis_kendaraan
                FROM vehicles v
                INNER JOIN customers c ON c.id_customer = v.id_customer
                INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
                WHERE v.status = 'aktif'
                  AND NOT EXISTS (
                      SELECT 1 FROM parking_entries pe
                      WHERE pe.id_vehicle = v.id_vehicle
                        AND pe.status = 'parkir'
                  )
                ORDER BY v.nomor_polisi";

        return $this->db->query($sql)->fetchAll();
    }


    public function countAvailableVehicles(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*)
             FROM vehicles v
             WHERE v.status = 'aktif'
               AND NOT EXISTS (
                   SELECT 1 FROM parking_entries pe
                   WHERE pe.id_vehicle = v.id_vehicle
                     AND pe.status = 'parkir'
               )"
        );

        return (int) $stmt->fetchColumn();
    }

    public function countAvailableSlots(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*)
             FROM parking_slots ps
             INNER JOIN parking_areas pa ON pa.id_area = ps.id_area
             WHERE ps.status = 'kosong'
               AND pa.status = 'tersedia'"
        );

        return (int) $stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT pe.*, v.nomor_polisi, v.merk, v.model,
                    c.nama_customer, vt.nama_jenis,
                    ps.kode_slot, pa.nama_area
             FROM parking_entries pe
             INNER JOIN vehicles v ON v.id_vehicle = pe.id_vehicle
             INNER JOIN customers c ON c.id_customer = v.id_customer
             INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
             LEFT JOIN parking_slots ps ON ps.id_slot = pe.id_slot
             LEFT JOIN parking_areas pa ON pa.id_area = ps.id_area
             WHERE pe.id_entry = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(int $vehicleId, int $slotId, int $userId, string $condition): string
    {
        $this->db->beginTransaction();

        try {
            $vehicleStmt = $this->db->prepare(
                "SELECT v.id_vehicle, v.nomor_polisi, v.id_jenis_kendaraan, vt.nama_jenis
                 FROM vehicles v
                 INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
                 WHERE v.id_vehicle = ? AND v.status = 'aktif'
                 FOR UPDATE"
            );
            $vehicleStmt->execute([$vehicleId]);
            $vehicle = $vehicleStmt->fetch();

            if (!$vehicle) {
                throw new RuntimeException('Kendaraan tidak ditemukan atau nonaktif.');
            }

            $activeStmt = $this->db->prepare(
                "SELECT id_entry FROM parking_entries
                 WHERE id_vehicle = ? AND status = 'parkir'
                 LIMIT 1
                 FOR UPDATE"
            );
            $activeStmt->execute([$vehicleId]);
            if ($activeStmt->fetch()) {
                throw new RuntimeException('Kendaraan tersebut masih tercatat sedang parkir.');
            }

            $slotStmt = $this->db->prepare(
                "SELECT ps.id_slot, ps.kode_slot, ps.jenis_kendaraan
                 FROM parking_slots ps
                 INNER JOIN parking_areas pa ON pa.id_area = ps.id_area
                 WHERE ps.id_slot = ?
                   AND ps.status = 'kosong'
                   AND pa.status = 'tersedia'
                 FOR UPDATE"
            );
            $slotStmt->execute([$slotId]);
            $slot = $slotStmt->fetch();

            if (!$slot) {
                throw new RuntimeException('Slot tidak tersedia. Silakan pilih slot lain.');
            }

            $expectedType = strtolower($vehicle['nama_jenis']);
            if ($expectedType !== 'motor' && $expectedType !== 'mobil') {
                $expectedType = 'lainnya';
            }

            if ($slot['jenis_kendaraan'] !== $expectedType) {
                throw new RuntimeException('Jenis slot tidak sesuai dengan jenis kendaraan.');
            }

            $tarifStmt = $this->db->prepare(
                "SELECT 1 FROM parking_rates
                 WHERE id_jenis_kendaraan = ? AND status = 'aktif' AND berlaku_mulai <= NOW()
                 LIMIT 1"
            );
            $tarifStmt->execute([(int) $vehicle['id_jenis_kendaraan']]);
            if (!$tarifStmt->fetch()) {
                throw new RuntimeException('Tarif aktif untuk jenis kendaraan ' . $vehicle['nama_jenis'] . ' belum tersedia. Hubungi admin untuk mengatur tarif terlebih dahulu.');
            }

            // Nomor tiket unik: ulangi jika kebetulan sudah ada.
            $ticketCheck = $this->db->prepare("SELECT 1 FROM parking_entries WHERE nomor_tiket = ? LIMIT 1");
            do {
                $ticket = 'TKT-' . date('YmdHis') . '-' . random_int(100, 999);
                $ticketCheck->execute([$ticket]);
            } while ($ticketCheck->fetch());

            $entryStmt = $this->db->prepare(
                "INSERT INTO parking_entries
                    (id_vehicle, id_slot, id_user, nomor_tiket, kondisi_kendaraan_masuk, status)
                 VALUES (?, ?, ?, ?, ?, 'parkir')"
            );
            $entryStmt->execute([
                $vehicleId,
                $slotId,
                $userId,
                $ticket,
                $condition !== '' ? $condition : null,
            ]);

            $entryId = (int) $this->db->lastInsertId();

            $updateSlot = $this->db->prepare(
                "UPDATE parking_slots SET status = 'terisi' WHERE id_slot = ?"
            );
            $updateSlot->execute([$slotId]);

            $history = $this->db->prepare(
                "INSERT INTO parking_history
                    (id_entry, id_user, aktivitas, keterangan)
                 VALUES (?, ?, 'Kendaraan Masuk', ?)"
            );
            $history->execute([
                $entryId,
                $userId,
                'Tiket ' . $ticket . ' - kendaraan ' . $vehicle['nomor_polisi'] . ' masuk ke slot ' . $slot['kode_slot'],
            ]);

            $this->db->commit();
            return $ticket;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
