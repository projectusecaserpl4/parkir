<?php

class ParkingPayment extends Model
{
    public function all(): array
    {
        $sql = "SELECT pp.*, pe.nomor_tiket, pe.waktu_masuk,
                       pe.id_entry, pp.id_exit,
                       px.waktu_keluar, px.lama_parkir,
                       v.nomor_polisi, c.nama_customer,
                       vt.nama_jenis, u.nama_lengkap AS petugas_bayar
                FROM parking_payments pp
                INNER JOIN parking_entries pe ON pe.id_entry = pp.id_entry
                LEFT JOIN parking_exits px ON px.id_exit = pp.id_exit
                INNER JOIN vehicles v ON v.id_vehicle = pe.id_vehicle
                INNER JOIN customers c ON c.id_customer = v.id_customer
                INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
                LEFT JOIN users u ON u.id_user = (
                    SELECT ph.id_user
                    FROM parking_history ph
                    WHERE ph.id_entry = pp.id_entry
                      AND ph.aktivitas = 'Pembayaran'
                    ORDER BY ph.id_history DESC
                    LIMIT 1
                )
                ORDER BY pp.status ASC, pp.waktu_pembayaran DESC, pp.id_payment DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function summary(): array
    {
        $row = $this->db->query(
            "SELECT
                COUNT(*) AS total_transaksi,
                SUM(status = 'belum_bayar') AS belum_bayar,
                SUM(status = 'lunas') AS lunas,
                COALESCE(SUM(CASE WHEN status = 'lunas' THEN total_bayar ELSE 0 END), 0) AS total_lunas
             FROM parking_payments"
        )->fetch();

        return [
            'total_transaksi' => (int) ($row['total_transaksi'] ?? 0),
            'belum_bayar' => (int) ($row['belum_bayar'] ?? 0),
            'lunas' => (int) ($row['lunas'] ?? 0),
            'total_lunas' => (float) ($row['total_lunas'] ?? 0),
        ];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT pp.*, pe.nomor_tiket, pe.waktu_masuk,
                    px.waktu_keluar, px.lama_parkir,
                    v.nomor_polisi, c.nama_customer, vt.nama_jenis,
                    pr.tarif_per_jam, pr.tarif_maksimal,
                    payer.nama_lengkap AS petugas_bayar
             FROM parking_payments pp
             INNER JOIN parking_entries pe ON pe.id_entry = pp.id_entry
             LEFT JOIN parking_exits px ON px.id_exit = pp.id_exit
             INNER JOIN vehicles v ON v.id_vehicle = pe.id_vehicle
             INNER JOIN customers c ON c.id_customer = v.id_customer
             INNER JOIN vehicle_types vt ON vt.id_jenis_kendaraan = v.id_jenis_kendaraan
             INNER JOIN parking_rates pr ON pr.id_tarif = pp.id_tarif
             LEFT JOIN parking_history ph ON ph.id_entry = pp.id_entry
                 AND ph.aktivitas = 'Pembayaran'
                 AND ph.id_history = (
                     SELECT MAX(ph2.id_history)
                     FROM parking_history ph2
                     WHERE ph2.id_entry = pp.id_entry
                       AND ph2.aktivitas = 'Pembayaran'
                 )
             LEFT JOIN users payer ON payer.id_user = ph.id_user
             WHERE pp.id_payment = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function markPaid(int $paymentId, string $method, ?float $amount, int $userId): void
    {
        $allowed = ['cash', 'qris', 'debit', 'lainnya'];
        if (!in_array($method, $allowed, true)) {
            throw new RuntimeException('Metode pembayaran tidak valid.');
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "SELECT pp.*, pe.nomor_tiket
                 FROM parking_payments pp
                 INNER JOIN parking_entries pe ON pe.id_entry = pp.id_entry
                 WHERE pp.id_payment = ?
                 FOR UPDATE"
            );
            $stmt->execute([$paymentId]);
            $payment = $stmt->fetch();

            if (!$payment) {
                throw new RuntimeException('Data pembayaran tidak ditemukan.');
            }

            if ($payment['status'] === 'lunas') {
                throw new RuntimeException('Pembayaran tersebut sudah lunas.');
            }

            $total = (float) $payment['total_bayar'];

            if ($method === 'cash') {
                if ($amount === null || $amount <= 0) {
                    throw new RuntimeException('Jumlah uang diterima wajib diisi untuk pembayaran cash.');
                }

                if ($amount < $total) {
                    throw new RuntimeException(
                        'Uang yang diterima kurang. Minimal Rp ' . number_format($total, 0, ',', '.') . '.'
                    );
                }
            } else {
                // QRIS/debit/lainnya dianggap dibayar sesuai nominal tagihan.
                $amount = $total;
            }

            $change = max(0, $amount - $total);

            $update = $this->db->prepare(
                "UPDATE parking_payments
                 SET metode_pembayaran = ?,
                     jumlah_dibayar = ?,
                     kembalian = ?,
                     status = 'lunas',
                     waktu_pembayaran = NOW()
                 WHERE id_payment = ?"
            );
            $update->execute([$method, $amount, $change, $paymentId]);

            $history = $this->db->prepare(
                "INSERT INTO parking_history
                    (id_entry, id_user, aktivitas, keterangan)
                 VALUES (?, ?, 'Pembayaran', ?)"
            );
            $history->execute([
                (int) $payment['id_entry'],
                $userId,
                'Pembayaran tiket ' . $payment['nomor_tiket']
                    . ' sebesar Rp ' . number_format($total, 0, ',', '.')
                    . ' dengan ' . strtoupper($method)
                    . '. Diterima Rp ' . number_format($amount, 0, ',', '.')
                    . ', kembalian Rp ' . number_format($change, 0, ',', '.') . '.',
            ]);

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
