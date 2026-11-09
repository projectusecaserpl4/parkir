<?php

class ParkingHistoryController extends Controller
{
    public function index(): void
    {
        requireRole(['admin']);
        $this->view('parking_history/index', [
            'title' => 'Aktivitas Parkir',
            'histories' => $this->model('ParkingHistory')->all(),
        ]);
    }
}
