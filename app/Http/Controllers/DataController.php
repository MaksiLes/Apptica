<?php

namespace App\Http\Controllers;

use App\Models\CategoryPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class DataController extends Controller
{
    public function appTopCategory(Request $request)
    {
        $inputDate = $request->input("date");
        $date = Carbon::parse($inputDate)->toDateString();

        $positions = CategoryPosition::where('date', $date)->get()->toArray();
        if (count($positions) === 0) {
            $positions = $this->getPositionsForDate($date);
        }

        $result = [];
        foreach ($positions as $position) {
            $result[$position['category']] = $position['position'];
        }

        return [
            'data' => $result,
            'status_code' => 200,
            'message' => 'ok',
        ];
    }

    private function getPositionsForDate(string $date): array
    {
        $applicationId = config('services.apptica.applicationId');
        $countryId = config('services.apptica.countryId');

        $url = "https://api.apptica.com/package/top_history/${applicationId}/${countryId}?date_from=${date}&date_to=${date}&B4NKGg=fVN5Q9KVOlOHDx9mOsKPAQsFBlEhBOwguLkNEDTZvKzJzT3l0";

        $response = Http::get($url);

        $result = [];
        foreach ($response['data'] as $category => $massiv) {
            $positionsByDate = [];
            foreach ($massiv as $subcategory => $item) {
                foreach ($item as $inputDate => $position) {
                    if ($position === null) {
                        continue;
                    }

                    if (!isset($positionsByDate[$inputDate])) {
                        $positionsByDate[$inputDate] = $position;
                        continue;
                    }

                    if ($position < $positionsByDate[$inputDate]) {
                        $positionsByDate[$inputDate] = $position;
                    }}
            }

            $result[$category] = $positionsByDate;
        }

        $arrayToInsert = [];
        foreach ($result as $category => $positionByDate) {
            foreach ($positionByDate as $date => $position) {
                $arrayToInsert[] = ['category' => $category, 'date' => $date, 'position' => $position];
            }
        }
        CategoryPosition::insertOrIgnore($arrayToInsert);

        return $arrayToInsert;
    }
}
