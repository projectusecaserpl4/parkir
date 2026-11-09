<?php

class ParkingEntryController extends Controller
{
    public function index(): void
    {
        requireLogin();
        $model = $this->model('ParkingEntry');

        $this->view('parking_entries/index', [
            'title' => 'Kendaraan Masuk',
            'entries' => $model->active(),
        ]);
    }

    public function create(): void
    {
        requireLogin();
        $model = $this->model('ParkingEntry');

        $this->view('parking_entries/form', [
            'title' => 'Kendaraan Masuk',
            'vehicles' => $model->vehicleOptions(),
            'slots' => $model->allAvailableSlots(),
            'vehicleCount' => $model->countAvailableVehicles(),
            'slotCount' => $model->countAvailableSlots(),
        ]);
    }

    public function slots(): void
    {
        requireLogin();
        $type = strtolower(trim($_GET['jenis'] ?? ''));
        $allowed = ['motor', 'mobil', 'lainnya'];

        if (!in_array($type, $allowed, true)) {
            $this->json(['success' => false, 'message' => 'Jenis kendaraan tidak valid.']);
        }

        $slots = $this->model('ParkingEntry')->availableSlots($type);
        $this->json(['success' => true, 'data' => $slots]);
    }

    public function store(): void
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('parking-entries');
        }

        $vehicleId = (int) post('id_vehicle');
        $slotId = (int) post('id_slot');
        $condition = cleanText(post('kondisi_kendaraan_masuk'));

        $errors = [];
        if ($vehicleId <= 0) {
            $errors[] = 'Kendaraan wajib dipilih.';
        }
        if ($slotId <= 0) {
            $errors[] = 'Slot parkir wajib dipilih.';
        }
        if (textLength($condition) > 500) {
            $errors[] = 'Kondisi kendaraan maksimal 500 karakter.';
        }
        if ($errors) {
            $this->fail($errors, ['id_vehicle' => $vehicleId, 'id_slot' => $slotId, 'kondisi_kendaraan_masuk' => $condition], 'parking-entries/create');
        }

        try {
            $ticket = $this->model('ParkingEntry')->create(
                $vehicleId,
                $slotId,
                (int) $_SESSION['user']['id_user'],
                $condition
            );
            flash('success', 'Kendaraan berhasil masuk. Nomor tiket: ' . $ticket);
        } catch (RuntimeException $e) {
            // Pesan bisnis (slot penuh, kendaraan sudah parkir, dll) aman ditampilkan.
            $this->fail([$e->getMessage()], ['id_vehicle' => $vehicleId, 'id_slot' => $slotId, 'kondisi_kendaraan_masuk' => $condition], 'parking-entries/create');
        } catch (Throwable $e) {
            error_log('[ParkingEntry::store] ' . $e->getMessage());
            $this->fail(['Terjadi kesalahan saat menyimpan data kendaraan masuk. Silakan coba lagi.'], ['id_vehicle' => $vehicleId, 'id_slot' => $slotId, 'kondisi_kendaraan_masuk' => $condition], 'parking-entries/create');
        }

        $this->redirect('parking-entries');
    }

    private function json(array $data): never
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
