<?php

class ParkingExitController extends Controller
{
    public function index(): void
    {
        requireLogin();
        $this->view('parking_exits/index', [
            'title' => 'Kendaraan Keluar',
            'entries' => $this->model('ParkingExit')->activeEntries(),
        ]);
    }

    public function create(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('ParkingExit');
        $entry = $model->findActive($id);

        if (!$entry) {
            flash('error', 'Data parkir aktif tidak ditemukan.');
            $this->redirect('parking-exits');
        }

        try {
            $calculation = $model->estimate($id);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            $this->redirect('parking-exits');
        }

        if (!$calculation) {
            flash('error', 'Data parkir aktif tidak ditemukan.');
            $this->redirect('parking-exits');
        }

        $this->view('parking_exits/form', [
            'title' => 'Proses Kendaraan Keluar',
            'entry' => $entry,
            'calculation' => $calculation,
        ]);
    }

    public function store(): void
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('parking-exits');
        }

        $entryId = (int) post('id_entry');
        $condition = cleanText(post('kondisi_kendaraan_keluar'));

        if ($entryId <= 0) {
            flash('error', 'Data parkir tidak valid.');
            $this->redirect('parking-exits');
        }

        if (textLength($condition) > 500) {
            flash('error', 'Kondisi kendaraan maksimal 500 karakter.');
            $this->redirect('parking-exits/create?id=' . $entryId);
        }

        try {
            $result = $this->model('ParkingExit')->process(
                $entryId,
                (int) $_SESSION['user']['id_user'],
                $condition
            );

            flash(
                'success',
                'Kendaraan berhasil keluar. Total tarif ' . rupiah($result['total_bayar']) . '. Silakan lanjut ke pembayaran.'
            );
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        } catch (Throwable $e) {
            error_log('[ParkingExit::store] ' . $e->getMessage());
            flash('error', 'Terjadi kesalahan saat memproses kendaraan keluar. Silakan coba lagi.');
        }

        $this->redirect('parking-exits');
    }
}
