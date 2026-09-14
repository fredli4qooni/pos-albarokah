<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Single Moving Average (SMA) Window Size
    |--------------------------------------------------------------------------
    |
    | Sesuai spesifikasi PRD §2.1 & proposal skripsi, jendela waktu (n)
    | perhitungan Single Moving Average ditetapkan sebesar 7 hari.
    |
    */
    'sma_window_days' => (int) env('FORECAST_SMA_WINDOW_DAYS', 7),
];
