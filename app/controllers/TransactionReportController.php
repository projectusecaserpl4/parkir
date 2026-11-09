<?php

class TransactionReportController extends Controller
{
    public function index(): void
    {
        requireRole(['admin']);

        $today = date('Y-m-d');
        $startDate = trim((string)($_GET['mulai'] ?? date('Y-m-01')));
        $endDate = trim((string)($_GET['sampai'] ?? $today));
        $isDate = static fn(string $date): bool => (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            && strtotime($date) !== false
            && date('Y-m-d', strtotime($date)) === $date;

        if (!$isDate($startDate)) $startDate = date('Y-m-01');
        if (!$isDate($endDate)) $endDate = $today;
        if ($startDate > $endDate) [$startDate, $endDate] = [$endDate, $startDate];

        $model = $this->model('TransactionReport');
        $this->view('transaction_reports/index', [
            'title' => 'Rekap Transaksi',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => $model->summary($startDate, $endDate),
            'transactions' => $model->rows($startDate, $endDate),
        ]);
    }
}
