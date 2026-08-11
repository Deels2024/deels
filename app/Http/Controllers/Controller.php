<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function __construct()
    {

    }

    protected function collectionToExcel(Collection $collection): BinaryFileResponse
    {
        $time = Carbon::now()->format('d_m_Y_H_i_s');
        $filename = 'export_'.$time.'.csv';
        $handle = fopen(public_path($filename), 'w');

        // Добавляем BOM для корректного отображения кириллицы в Excel
        fwrite($handle, "\xEF\xBB\xBF");

        // Получаем заголовки из первого элемента (если коллекция не пуста)
        if ($collection->isNotEmpty()) {
            $firstItem = $collection->first()->toArray();
            $headers = [];

            foreach ($firstItem as $key => $value) {
                if (!is_array($value)) {
                    $headers[] = $key;
                }
            }

            fputcsv($handle, $headers, ';');
        }

        foreach ($collection as $row) {
            $data = $row->toArray();
            foreach ($data as $k => $item) {
                if (is_array($item)) {
                    unset($data[$k]);
                }
            }

            fputcsv($handle, $data, ';');
        }

        fclose($handle);

        return response()->download(public_path($filename), $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ])->deleteFileAfterSend(true);
    }
}