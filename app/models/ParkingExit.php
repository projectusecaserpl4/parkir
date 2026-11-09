<?php

class ParkingExit extends Model
{
    public function activeEntries(): array
    {
        $sql = "SELECT pe.id_entry, pe.nomor_tiket, pe.waktu_masuk,
                       v.nomor_polisi, c.nama_customer, vt.nama_jenis,
                       ps.kode_slot, pa.nama_area
                FROM parking_entries pe
                INNER JOIN vehicles v ON v.id_vehicle = pe.id_vehicle
                INNER JOIN customers c ON c.id_customer = v.id_customer
                INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
                LEFT JOIN parking_slots ps ON ps.id_slot = pe.id_slot
                LEFT JOIN parking_areas pa ON pa.id_area = ps.id_area
                WHERE pe.status = 'parkir'
                ORDER BY pe.waktu_masuk ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function findActive(int $entryId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT pe.*, v.nomor_polisi, v.merk, v.model,
                    c.nama_customer, vt.id_jenis_kendaraan, vt.nama_jenis,
                    ps.kode_slot, pa.nama_area
             FROM parking_entries pe
             INNER JOIN vehicles v ON v.id_vehicle = pe.id_vehicle
             INNER JOIN customers c ON c.id_customer = v.id_customer
             INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
             LEFT JOIN parking_slots ps ON ps.id_slot = pe.id_slot
             LEFT JOIN parking_areas pa ON pa.id_area = ps.id_area
             WHERE pe.id_entry = ? AND pe.status = 'parkir'
             LIMIT 1"
        );
        $stmt->execute([$entryId]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function estimate(int $entryId): ?array
    {
        $entry = $this->findActive($entryId);
        if (!$entry) {
            return null;
        }

        return $this->calculate($entry);
    }

    public function process(int $entryId, int $userId, string $condition): array
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "SELECT pe.*, v.nomor_polisi, vt.id_jenis_kendaraan, vt.nama_jenis,
                        ps.id_slot, ps.kode_slot
                 FROM parking_entries pe
                 INNER JOIN vehicles v ON v.id_vehicle = pe.id_vehicle
                 INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
                 LEFT JOIN parking_slots ps ON ps.id_slot = pe.id_slot
                 WHERE pe.id_entry = ? AND pe.status = 'parkir'
                 FOR UPDATE"
            );
            $stmt->execute([$entryId]);
            $entry = $stmt->fetch();

            if (!$entry) {
                throw new RuntimeException('Data parkir aktif tidak ditemukan atau sudah diproses.');
            }

            $calculation = $this->calculate($entry);

            $exitStmt = $this->db->prepare(
                "INSERT INTO parking_exits
                    (id_entry, id_user, waktu_keluar, kondisi_kendaraan_keluar, lama_parkir, status)
                 VALUES (?, ?, NOW(), ?, ?, 'keluar')"
            );
            $exitStmt->execute([
                $entryId,
                $userId,
                $condition !== '' ? $condition : null,
                $calculation['lama_parkir'],
            ]);
            $exitId = (int) $this->db->lastInsertId();

            $entryUpdate = $this->db->prepare(
                "UPDATE parking_entries SET status = 'selesai' WHERE id_entry = ?"
            );
            $entryUpdate->execute([$entryId]);

            if (!empty($entry['id_slot'])) {
                // Hanya kosongkan slot yang memang berstatus terisi (slot "rusak" tidak ikut berubah).
                $slotUpdate = $this->db->prepare(
                    "UPDATE parking_slots SET status = 'kosong' WHERE id_slot = ? AND status = 'terisi'"
                );
                $slotUpdate->execute([(int) $entry['id_slot']]);
            }

            $payment = $this->db->prepare(
                "INSERT INTO parking_payments
                    (id_entry, id_exit, id_tarif, total_bayar, metode_pembayaran, status)
                 VALUES (?, ?, ?, ?, 'cash', 'belum_bayar')"
            );
            $payment->execute([
                $entryId,
                $exitId,
                $calculation['id_tarif'],
                $calculation['total_bayar'],
            ]);
            $paymentId = (int) $this->db->lastInsertId();

            $history = $this->db->prepare(
                "INSERT INTO parking_history
                    (id_entry, id_exit, id_user, aktivitas, keterangan)
                 VALUES (?, ?, ?, 'Kendaraan Keluar', ?)"
            );
            $history->execute([
                $entryId,
                $exitId,
                $userId,
                'Kendaraan ' . $entry['nomor_polisi'] . ' keluar. Total parkir Rp ' . number_format($calculation['total_bayar'], 0, ',', '.'),
            ]);

            $this->db->commit();

            $calculation['id_exit'] = $exitId;
            $calculation['id_payment'] = $paymentId;
            return $calculation;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function calculate(array $entry): array
    {
        // Hitung selisih waktu memakai jam database (sama dengan yang mencatat waktu_masuk dan waktu_keluar).
        $diffStmt = $this->db->prepare("SELECT TIMESTAMPDIFF(SECOND, CAST(? AS DATETIME), NOW())");
        $diffStmt->execute([$entry['waktu_masuk']]);
        $seconds = max(0, (int) $diffStmt->fetchColumn());
        $hours = max(1, (int) ceil($seconds / 3600));

        $rateStmt = $this->db->prepare(
            "SELECT id_tarif, tarif_per_jam, tarif_maksimal
             FROM parking_rates
             WHERE id_jenis_kendaraan = ?
               AND status = 'aktif'
               AND berlaku_mulai <= NOW()
             ORDER BY berlaku_mulai DESC, id_tarif DESC
             LIMIT 1"
        );
        $rateStmt->execute([(int) $entry['id_jenis_kendaraan']]);
        $rate = $rateStmt->fetch();

        if (!$rate) {
            throw new RuntimeException('Tarif aktif untuk jenis kendaraan ini belum tersedia.');
        }

        $total = $hours * (float) $rate['tarif_per_jam'];
        if ($rate['tarif_maksimal'] !== null && (float) $rate['tarif_maksimal'] > 0) {
            $total = min($total, (float) $rate['tarif_maksimal']);
        }

        return [
            'lama_parkir' => $hours,
            'tarif_per_jam' => (float) $rate['tarif_per_jam'],
            'tarif_maksimal' => $rate['tarif_maksimal'] !== null ? (float) $rate['tarif_maksimal'] : null,
            'total_bayar' => $total,
            'id_tarif' => (int) $rate['id_tarif'],
        ];
    }
}
