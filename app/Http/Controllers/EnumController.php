<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ApiService;

class EnumController extends Controller
{
    protected $api;

    public function __construct(ApiService $api)
    {
        $this->api = $api;
    }

    /**
     * GET /enums/racas
     */
    public function racas()
    {
        return response()->json(
            $this->api->get("api/enums/racas")
        );
    }

    /**
     * GET /enums/classes
     */
    public function classes()
    {
        return response()->json(
            $this->api->get("api/enums/classes")
        );
    }

    /**
     * GET /enums/bonus-racas/{raca}
     */
    public function bonusRacas($raca)
    {
        return response()->json(
            $this->api->get("api/enums/bonus-racas/{$raca}")
        );
    }
}
