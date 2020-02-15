<?php

namespace Blegrator\Http\Controllers\Api\Service;

use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Role;
use Blegrator\Rules\CompanyRoleRequest;
use Blegrator\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Response_Code;

class ServiceUser extends ApiController
{

    public function getListUserServices(Request $request)
    {
        // Check Exist User ID
        $validator = Validator::make(['user_id' => $request->route('id')], [
            'user_id' => ['exists:users,id']
        ]);
        if ($validator->fails()) {
            return $this->responseWithValidateError($validator->errors()->all());
        }

        $user = \Blegrator\User::find($request->route('id'));
        return response()->json(
            [
                'status' => true,
                'data' => $user->services()->get()
            ],
            Response_Code::HTTP_OK
        );
    }

    public function getListServicesUser(Request $request)
    {
        $service = \Blegrator\Service::find($request->route('id'));
        return response()->json(
            [
                'status' => true,
                'data' => $service->users()->get()->pluck('id')
            ],
            Response_Code::HTTP_OK
        );
    }

    public function hasAttachServices(Request $request)
    {
        $has_attach = DB::table('service_user')
            ->where('service_id', '=', $request->route('id'))
            ->where('user_id', '=', $request->route('user_id'))
            ->count();
        return response()->json(
            [
                'status' => true,
                'data' => ($has_attach > 0 ? true : false)
            ],
            Response_Code::HTTP_OK
        );
    }

    public function attach(Request $request)
    {
        // Get Current User
        $user = $request->user();

        // Get data Of Request
        $data = $request->only(['user_id', 'service_id']);

        // Validate
        $validator = Validator::make($data, self::validation($user));
        if ($validator->fails()) {
            return $this->responseWithValidateError($validator->errors()->all());
        }

        // @see https://stackoverflow.com/questions/17472128/preventing-laravel-adding-multiple-records-to-a-pivot-table
        User::find($data['user_id'])->services()->sync($data['service_id'], false);

        // Get List Of Services For This User
        return response()->json(
            [
                'status' => true,
                'data' => User::find($data['user_id'])->services()->get()
            ],
            Response_Code::HTTP_OK
        );
    }

    public function detach(Request $request)
    {
        // Get Current User
        $user = $request->user();

        // Get data Of Request
        $data = $request->only(['user_id', 'service_id']);

        // Validate
        $validator = Validator::make($data, self::validation($user));
        if ($validator->fails()) {
            return $this->responseWithValidateError($validator->errors()->all());
        }

        // Detach
        User::find($data['user_id'])->services()->detach($data['service_id']);

        // Get List Of Services For This User
        return response()->json(
            [
                'status' => true,
                'data' => User::find($data['user_id'])->services()->get()
            ],
            Response_Code::HTTP_OK
        );
    }

    public static function validation($user)
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                new CompanyRoleRequest($user)
            ],
            'service_id' => [
                'required',
                'integer',
                'exists:services,id'
            ],
        ];
    }
}
