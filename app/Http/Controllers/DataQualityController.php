<?php

namespace App\Http\Controllers;

use App\Services\DataQualityService;

class DataQualityController extends Controller
{
    public function index(DataQualityService $service)
    {
        return view('admin.data-quality', [
            'duplicateEvents'             => $service->duplicateEvents(),
            'positionMismatches'          => $service->positionMismatches(),
            'activeWithoutCrm'            => $service->activeWithoutCrm(),
            'activeWithoutKmp'            => $service->activeWithoutKmp(),
            'territoriesHeldByDismissed'  => $service->territoriesHeldByDismissed(),
            'tabletsHeldByDismissed'      => $service->tabletsHeldByDismissed(),
            'tabletsResponsibleDismissed' => $service->tabletsResponsibleDismissed(),
        ]);
    }
}
