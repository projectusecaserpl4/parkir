<?php

class Dashboard extends Model
{
    public function totalCustomers(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM customers')->fetchColumn();
    }

    public function totalVehicles(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
    }

    public function totalSlots(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM parking_slots')->fetchColumn();
    }

    public function occupiedSlots(): int
    {
        return (int) $this->db
            ->query("SELECT COUNT(*) FROM parking_slots WHERE status = 'terisi'")
            ->fetchColumn();
    }

    public function availableSlots(): int
    {
        return (int) $this->db
            ->query("SELECT COUNT(*) FROM parking_slots ps
                     INNER JOIN parking_areas pa ON pa.id_area = ps.id_area
                     WHERE ps.status = 'kosong' AND pa.status = 'tersedia'")
            ->fetchColumn();
    }

    public function activeParking(): int
    {
        return (int) $this->db
            ->query("SELECT COUNT(*) FROM parking_entries WHERE status = 'parkir'")
            ->fetchColumn();
    }
    public function totalRevenue(): float
    {
        return (float) $this->db
            ->query("SELECT COALESCE(SUM(total_bayar), 0) FROM parking_payments WHERE status = 'lunas'")
            ->fetchColumn();
    }

    public function pendingPayments(): int
    {
        return (int) $this->db
            ->query("SELECT COUNT(*) FROM parking_payments WHERE status = 'belum_bayar'")
            ->fetchColumn();
    }
}

