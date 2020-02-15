<?php

namespace Blegrator\Http\Controllers\Api\Service;

use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Role;
use Blegrator\Rules\CompanyRoleRequest;
use Blegrator\Transformers\ServiceItemTransformer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Response as Response_Code;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class ServiceItem extends ApiController
{
    /**
     * Instantiate a new PostController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $list = QueryBuilder::for(\Blegrator\ServiceItem::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('service_id'),
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('name'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::exact('period'),
                AllowedFilter::exact('price'),
            ])
            ->defaultSort('-id') //DESC
            ->allowedSorts('id', 'price', 'is_active', 'period')
            ->paginate(($request->has('per_page') ?: 15))
            ->appends(request()->query());

        return $this->respondWithPagination($list, new ServiceItemTransformer());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Sanitize Request Data
        $user = $request->user();
        $data = $request->only((new \Blegrator\ServiceItem())->getFillable());

        // Validation Request
        $validator = Validator::make($data, [
            'service_id' => ['required', 'exists:services,id'],
            'user_id' => [
                'required',
                'exists:users,id',
                new CompanyRoleRequest($user)
            ],
            'name' => ['required'],
            'is_active' => ['required'],
            'period' => ['required'],
            'price' => ['required']
        ]);
        if ($validator->fails()) {
            return $this->responseWithValidateError($validator->errors()->all());
        }

        // Create New Item
        $service_item = \Blegrator\ServiceItem::create($data);

        // Return Data
        return $this->respondWithItem($service_item, new ServiceItemTransformer());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Check Exist Item
        $Service_Item = \Blegrator\ServiceItem::find($id);
        if (is_null($Service_Item)) {
            return $this->responseWithNotFound();
        }

        // Sanitize Request Data
        $user = $request->user();
        $data = $request->only((new \Blegrator\ServiceItem())->getFillable());

        // Check CURD Permission
        if (!self::curdPermission($id, $user)) {
            return response()->json(
                [
                'status' => false,
                'message' => "Permission denied."
                ],
                Response_Code::HTTP_BAD_REQUEST
            );
        }

        // Validation Request
        $validator = Validator::make($data, [
            'service_id' => ['sometimes', 'required', 'exists:services,id'],
            'user_id' => [
                'sometimes',
                'required',
                'exists:users,id',
                new CompanyRoleRequest($user)
            ]
        ]);
        if ($validator->fails()) {
            return $this->responseWithValidateError($validator->errors()->all());
        }

        // Edit Service Type
        $Service_Item->update($data);

        // Return Data
        return $this->respondWithItem($Service_Item, new ServiceItemTransformer());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        // Check Exist Item
        $Service_Item = \Blegrator\ServiceItem::find($id);
        if (is_null($Service_Item)) {
            return $this->responseWithNotFound();
        }

        // Get User Request
        $user = $request->user();

        // Check CURD Permission
        if (!self::curdPermission($id, $user)) {
            return response()->json(
                [
                    'status' => false,
                    'message' => "Permission denied."
                ],
                Response_Code::HTTP_BAD_REQUEST
            );
        }

        $Service_Item->delete();
        return $this->responseWithRemovedItem();
    }

    public static function curdPermission($id, $user)
    {
        // Check Has Permission
        $user_role = Role::getRoleNameByID($user->role_id);

        // Request Only From Admin and Company
        if (!in_array($user_role, array_merge(['Admin'], Role::companyRoleList()))) {
            return false;
        }

        // Company Only Change Own service
        $Service_Item = \Blegrator\ServiceItem::find($id);
        if (in_array($user_role, Role::companyRoleList()) and $Service_Item->user_id != $user->id) {
            return false;
        }

        return true;
    }
}
