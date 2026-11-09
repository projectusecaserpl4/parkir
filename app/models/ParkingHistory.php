<?php

class ParkingHistory extends Model
{
    public function all(): array
    {
        $sql = "SELECT ph.*, u.nama_lengkap,
                       pe.nomor_tiket, v.nomor_polisi
                FROM parking_history ph
                LEFT JOIN users u ON u.id_user = ph.id_user
                LEFT JOIN parking_entries pe ON pe.id_entry = ph.id_entry
                LEFT JOIN vehicles v ON v.id_vehicle = pe.id_vehicle
                ORDER BY ph.waktu_aktivitas DESC, ph.id_history DESC";

        return $this->db->query($sql)->fetchAll();
    }
}
