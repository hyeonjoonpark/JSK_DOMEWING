<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenMarkets\Coupang\ApiController;

class CoupangOrderController extends Controller
{
    private $ssac;
    public function __construct()
    {
        $this->ssac = new ApiController();
    }
}
