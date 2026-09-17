<?php

namespace App\Http\Controllers;

use App\Services\DataQualityService;
use Illuminate\Http\Request;

class DataIntegrityController extends Controller
{
    /**
     * Объединяет три ранее отдельных страницы (Привязка CRM, Привязка KMP,
     * Проверка данных) в одну с вкладками — так связь между ними видна сразу:
     * "Активные без привязки" (по сотрудникам) и "аккаунты без сотрудника" (по
     * внешней системе) — это взаимодополняющие проверки одной и той же задачи.
     */
    public function index(Request $request, CrmMappingController $crm, KmpMappingController $kmp, DataQualityService $dqService)
    {
        $crmData = $crm->buildIndexData();
        $kmpData = $kmp->buildIndexData();

        return view('admin.data-integrity', [
            'tab' => $request->input('tab', 'crm'),

            'crmEmployees' => $crmData['crmEmployees'],
            'crmLinks'     => $crmData['crmLinks'],
            'crmTotal'     => $crmData['crmTotal'],
            'crmMapped'    => $crmData['mapped'],
            'sysEmployees' => $crmData['sysEmployees'],

            'kmpEmployees' => $kmpData['kmpEmployees'],
            'kmpLinks'     => $kmpData['kmpLinks'],
            'kmpTotal'     => $kmpData['kmpTotal'],
            'kmpMapped'    => $kmpData['mapped'],

            'duplicateEvents'             => $dqService->duplicateEvents(),
            'positionMismatches'          => $dqService->positionMismatches(),
            'activeWithoutCrm'            => $dqService->activeWithoutCrm(),
            'activeWithoutKmp'            => $dqService->activeWithoutKmp(),
            'crmAccountsWithoutEmployee'  => $dqService->crmAccountsWithoutEmployee($crmData['crmEmployees']),
            'kmpAccountsWithoutEmployee'  => $dqService->kmpAccountsWithoutEmployee($kmpData['kmpEmployees']),
            'territoriesHeldByDismissed'  => $dqService->territoriesHeldByDismissed(),
            'tabletsHeldByDismissed'      => $dqService->tabletsHeldByDismissed(),
            'tabletsResponsibleDismissed' => $dqService->tabletsResponsibleDismissed(),
        ]);
    }
}
