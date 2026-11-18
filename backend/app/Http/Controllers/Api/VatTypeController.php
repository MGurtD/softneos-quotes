<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VatTypeResource;
use App\Models\VatType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VatTypeController extends Controller
{
    /**
     * Active VAT types, highest rate first (General is the usual default).
     */
    public function __invoke(): AnonymousResourceCollection
    {
        return VatTypeResource::collection(
            VatType::where('active', true)->orderByDesc('rate')->get()
        );
    }
}
