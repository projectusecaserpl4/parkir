<?php

class DashboardController extends Controller
{
    public function index(): void
    {
        requireLogin();
        $dashboard = $this->model('Dashboard');
        $role = currentRole();
        $stats = [
            'customers' => $dashboard->totalCustomers(),
            'vehicles' => $dashboard->totalVehicles(),
            'slots' => $dashboard->totalSlots(),
            'occupied' => $dashboard->occupiedSlots(),
            'available' => $dashboard->availableSlots(),
            'activeParking' => $dashboard->activeParking(),
            'revenue' => $dashboard->totalRevenue(),
            'pendingPayments' => $dashboard->pendingPayments(),
        ];
        $welcome = match ($role) {
            'owner' => 'Ringkasan bisnis dan pemantauan seluruh aktivitas parkir.',
            'admin' => 'Kelola data master, pengguna, dan operasional parkir.',
            'petugas' => 'Akses cepat untuk melayani kendaraan masuk dan keluar.',
            default => 'Ringkasan aktivitas sistem parkir.',
        };
        $this->view('dashboard/index', [
            'title' => 'Dashboard ' . ucfirst($role),
            'role' => $role,
            'welcome' => $welcome,
            'stats' => $stats,
        ]);
    }
}
