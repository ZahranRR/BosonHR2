<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            
            //2025
            ['date' => '2025-01-01', 'name' => 'Tahun Baru 2025 M'],
            ['date' => '2025-01-27', 'name' => 'Isra Mikraj Nabi Muhammad SAW'],
            ['date' => '2025-01-29', 'name' => 'Tahun Baru Imlek 2576 Kongzili'],
            ['date' => '2025-03-29', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1947'],
            ['date' => '2025-03-31', 'name' => 'Hari Raya Idul Fitri 1446 H (Hari 1)'],
            ['date' => '2025-04-01', 'name' => 'Hari Raya Idul Fitri 1446 H (Hari 2)'],
            ['date' => '2025-04-18', 'name' => 'Wafat Yesus Kristus'],
            ['date' => '2025-04-20', 'name' => 'Kebangkitan Yesus Kristus (Paskah)'],
            ['date' => '2025-05-01', 'name' => 'Hari Buruh Internasional'],
            ['date' => '2025-05-12', 'name' => 'Hari Raya Waisak 2569 BE'],
            ['date' => '2025-05-29', 'name' => 'Kenaikan Yesus Kristus'],
            ['date' => '2025-06-01', 'name' => 'Hari Lahir Pancasila'],
            ['date' => '2025-06-06', 'name' => 'Hari Raya Idul Adha 1446 H'],
            ['date' => '2025-06-27', 'name' => 'Tahun Baru Islam 1447 H'],
            ['date' => '2025-08-17', 'name' => 'Proklamasi Kemerdekaan RI'],
            ['date' => '2025-09-05', 'name' => 'Maulid Nabi Muhammad SAW'],
            ['date' => '2025-12-25', 'name' => 'Hari Raya Natal'],

            //2026
            ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 M'],
            ['date' => '2026-01-16', 'name' => 'Isra Mikraj Nabi Muhammad SAW'],
            ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577 Kongzili'],
            ['date' => '2026-03-19', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1948'],
            ['date' => '2026-03-21', 'name' => 'Hari Raya Idul Fitri 1447 H (Hari 1)'],
            ['date' => '2026-03-22', 'name' => 'Hari Raya Idul Fitri 1447 H (Hari 2)'],
            ['date' => '2026-04-03', 'name' => 'Wafat Yesus Kristus'],
            ['date' => '2026-04-05', 'name' => 'Kebangkitan Yesus Kristus (Paskah)'],
            ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional'],
            ['date' => '2026-05-14', 'name' => 'Kenaikan Yesus Kristus'],
            ['date' => '2026-05-27', 'name' => 'Hari Raya Idul Adha 1447 H'],
            ['date' => '2026-05-31', 'name' => 'Hari Raya Waisak 2570 BE'],
            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila'],
            ['date' => '2026-06-16', 'name' => 'Tahun Baru Islam 1448 H'],
            ['date' => '2026-08-17', 'name' => 'Proklamasi Kemerdekaan RI'],
            ['date' => '2026-08-25', 'name' => 'Maulid Nabi Muhammad SAW'],
            ['date' => '2026-12-25', 'name' => 'Hari Raya Natal'],
        ];

        foreach ($holidays as $h) {
            Event::updateOrCreate(
                [
                    'start_date' => $h['date'],
                    'title'      => $h['name']
                ],
                [
                    'end_date'   => $h['date'],
                    'category'   => 'danger',
                ]
            );
        }

        // $startYear = date('Y') - 100;
        // $endYear = date('Y') + 100;

        // for ($year = $startYear; $year <= $endYear; $year++) {
        //     $startDate = "$year-01-01"; // Awal tahun
        //     $endDate = "$year-12-31"; // Akhir tahun

        //     $currentDate = Carbon::createFromFormat('Y-m-d', $startDate);

        //     while ($currentDate->format('Y-m-d') <= $endDate) {
        //         // Memeriksa apakah hari ini adalah hari Minggu
        //         // if ($currentDate->dayOfWeek === 0) {
        //         //     // Memeriksa apakah event sudah ada untuk tanggal ini
        //         //     $existingEvent = Event::where('start_date', $currentDate->format('Y-m-d'))
        //         //         ->where('title', 'holiday')
        //         //         ->first();


        //         //     if (!$existingEvent) {
        //         //         Event::create([
        //         //             'title' => 'holiday',
        //         //             'start_date' => $currentDate->format('Y-m-d'),
        //         //             'end_date' => $currentDate->format('Y-m-d'),
        //         //             'category' => 'danger', // Kategori 'danger'
        //         //             'created_at' => now(),
        //         //             'updated_at' => now(),
        //         //         ]);
        //         //     }
        //         // }

        //         // Mengupdate tanggal ke hari berikutnya
        //         $currentDate->addDay();
        //     }
        // }
    }
}
