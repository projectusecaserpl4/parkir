<?php

class TransactionReport extends Model
{
    public function summary(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total_transaksi,
                    SUM(status = 'lunas') AS transaksi_lunas,
                    SUM(status = 'belum_bayar') AS belum_bayar,
                    SUM(status = 'dibatalkan') AS dibatalkan,
                    COALESCE(SUM(CASE WHEN status = 'lunas' THEN total_bayar ELSE 0 END), 0) AS total_pendapatan
             FROM parking_payments
             WHERE DATE(COALESCE(waktu_pembayaran, '1970-01-01')) BETWEEN ? AND ?"
        );
        $stmt->execute([$startDate, $endDate]);
        $row = $stmt->fetch() ?: [];
        return [
            'total_transaksi' => (int)($row['total_transaksi'] ?? 0),
            'transaksi_lunas' => (int)($row['transaksi_lunas'] ?? 0),
            'belum_bayar' => (int)($row['belum_bayar'] ?? 0),
            'dibatalkan' => (int)($row['dibatalkan'] ?? 0),
            'total_pendapatan' => (float)($row['total_pendapatan'] ?? 0),
        ];
    }

    public function rows(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare(
            "SELECT pp.id_payment, pp.total_bayar, pp.jumlah_dibayar, pp.kembalian,
                    pp.metode_pembayaran, pp.waktu_pembayaran, pp.status,
                    pe.nomor_tiket, v.nomor_polisi, c.nama_customer,
                    payer.nama_lengkap AS petugas_bayar
             FROM parking_payments pp
             INNER JOIN parking_entries pe ON pe.id_entry = pp.id_entry
             INNER JOIN vehicles v ON v.id_vehicle = pe.id_vehicle
             INNER JOIN customers c ON c.id_customer = v.id_customer
             LEFT JOIN parking_history ph ON ph.id_history = (
                 SELECT MAX(ph2.id_history) FROM parking_history ph2
                 WHERE ph2.id_entry = pp.id_entry AND ph2.aktivitas = 'Pembayaran'
             )
             LEFT JOIN users payer ON payer.id_user = ph.id_user
             WHERE DATE(COALESCE(pp.waktu_pembayaran, '1970-01-01')) BETWEEN ? AND ?
             ORDER BY pp.waktu_pembayaran DESC, pp.id_payment DESC"
        );
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }
}
