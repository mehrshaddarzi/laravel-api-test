<?php

namespace Blegrator\Http\Controllers\Web;

use Blegrator\Http\Requests\InternalRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Blegrator\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * Displays the application Order
     */
    public function index(Request $request)
    {
        if ($request->has('service')) {
            $service_id = $request->input('service');

            // Get Services List
            $services = Request::create('/api/service_type?include=service', 'GET');
            $services_Array = InternalRequest::toArray($services);

            // Show in View
            return view('order.index', ['services' => $services_Array['data']]);
        }
    }
}
