<?php

class ParkingPaymentController extends Controller
{
    public function index(): void
    {
        requireLogin();

        $model = $this->model('ParkingPayment');
        $this->view('parking_payments/index', [
            'title' => 'Pembayaran',
            'payments' => $model->all(),
            'summary' => $model->summary(),
        ]);
    }

    public function create(): void
    {
        requireLogin();

        $id = queryId();
        $payment = $this->model('ParkingPayment')->find($id);

        if (!$payment) {
            flash('error', 'Data pembayaran tidak ditemukan.');
            $this->redirect('parking-payments');
        }

        if ($payment['status'] === 'lunas') {
            flash('error', 'Pembayaran tersebut sudah lunas.');
            $this->redirect('parking-payments');
        }

        $this->view('parking_payments/form', [
            'title' => 'Proses Pembayaran',
            'payment' => $payment,
        ]);
    }

    public function store(): void
    {
        requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('parking-payments');
        }

        $paymentId = (int) post('id_payment');
        $method = strtolower(trim(post('metode_pembayaran')));
        $amountRaw = trim(post('jumlah_dibayar'));
        $old = ['metode_pembayaran' => $method, 'jumlah_dibayar' => $amountRaw];

        if ($paymentId <= 0) {
            flash('error', 'Data pembayaran tidak valid.');
            $this->redirect('parking-payments');
        }

        $back = 'parking-payments/create?id=' . $paymentId;
        $errors = [];
        $amount = null;

        if (!in_array($method, ['cash', 'qris', 'debit', 'lainnya'], true)) {
            $errors[] = 'Metode pembayaran tidak valid.';
        } elseif ($method === 'cash') {
            if ($amountRaw === '') {
                $errors[] = 'Jumlah uang diterima wajib diisi untuk pembayaran cash.';
            } else {
                $amount = parseDecimal($amountRaw);
                if ($amount === null) {
                    $errors[] = 'Jumlah uang diterima harus berupa angka (tanpa titik ribuan atau huruf), contoh: 50000.';
                } elseif ($amount <= 0) {
                    $errors[] = 'Jumlah uang diterima harus lebih besar dari 0.';
                } elseif ($amount > 999999999) {
                    $errors[] = 'Jumlah uang diterima terlalu besar (maksimal 999.999.999).';
                }
            }
        }

        if ($errors) {
            $this->fail($errors, $old, $back);
        }

        try {
            $this->model('ParkingPayment')->markPaid(
                $paymentId,
                $method,
                $amount,
                (int) $_SESSION['user']['id_user']
            );
        } catch (RuntimeException $e) {
            $this->fail([$e->getMessage()], $old, $back);
        } catch (Throwable $e) {
            error_log('[ParkingPayment::store] ' . $e->getMessage());
            $this->fail(['Terjadi kesalahan saat menyimpan pembayaran. Silakan coba lagi.'], $old, $back);
        }

        flash('success', 'Pembayaran berhasil dicatat sebagai LUNAS.');
        $this->redirect('parking-payments');
    }

    public function receipt(): void
    {
        requireLogin();

        $id = queryId();
        $payment = $this->model('ParkingPayment')->find($id);

        if (!$payment || $payment['status'] !== 'lunas') {
            flash('error', 'Struk hanya dapat dicetak untuk pembayaran yang sudah lunas.');
            $this->redirect('parking-payments');
        }

        $this->view('parking_payments/receipt', [
            'title' => 'Struk Pembayaran',
            'payment' => $payment,
        ]);
    }
}
