<?php
function base_path($path = '')
{
    return __DIR__ . '/../../' . ltrim($path, '/');
}

function base_url($path = '')
{
    return 'http://localhost/serba35/' . ltrim($path, '/');
}


// function formatTanggalIndo($datetimeStr)
// {
//     $formatter = new IntlDateFormatter(
//         'id_ID',
//         IntlDateFormatter::FULL,
//         IntlDateFormatter::SHORT,
//         'Asia/Jakarta',
//         IntlDateFormatter::GREGORIAN,
//         "EEEE, d MMMM yyyy '/' HH:mm"
//     );

//     $timestamp = strtotime($datetimeStr);
//     return $formatter->format($timestamp);
// }

function formatTanggalIndo($datetimeStr)
{
    $timestamp = strtotime($datetimeStr);

    $hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    $bulan = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    ];

    $namaHari = $hari[date('l', $timestamp)];
    $namaBulan = $bulan[date('F', $timestamp)];

    return $namaHari . ', ' . date('d', $timestamp) . ' ' . $namaBulan . ' ' . date('Y / H:i', $timestamp);
}
