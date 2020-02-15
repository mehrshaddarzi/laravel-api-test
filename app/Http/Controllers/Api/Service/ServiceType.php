<?php

namespace Blegrator\Http\Controllers\Api\Service;

use Blegrator\Helper\File;
use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Transformers\ServiceTypeTransformer;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class ServiceType extends ApiController
{
    /**
     * Instantiate a new PostController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('role:Admin', ['only' => ['store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $list = QueryBuilder::for(\Blegrator\Servicetype::class)
            ->allowedFilters([
                AllowedFilter::exact('name'),
                AllowedFilter::exact('id'),
                AllowedFilter::exact('user_id')
            ])
            ->defaultSort('-id') //DESC
            ->allowedSorts('id')
            ->paginate(($request->has('per_page') ?: 15))
            ->appends(request()->query());

        return $this->respondWithPagination($list, new ServiceTypeTransformer);
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
        $data = $request->only((new \Blegrator\Servicetype())->getFillable());

        // Validation Request
        $validator = Validator::make($data, [
            'name' => ['required'],
            'coverphoto' => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:2000'], //2MB
            'icon' => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:2000']
        ]);
        if ($validator->fails()) {
            return $this->responseWithValidateError($validator->errors()->all());
        }

        // Check Upload Image
        foreach (['icon', 'coverphoto'] as $img_field) {
            if ($request->hasFile($img_field)) {
                $data[$img_field] = File::uploadImage(
                    $request,
                    $img_field,
                    \Blegrator\Servicetype::$Image_Path,
                    75,
                    300,
                    null
                );
            }
        }

        // Create New Item
        $service_type = \Blegrator\Servicetype::create(array_merge([
            'user_id' => $user->id
        ], $data));

        // Return Data
        return $this->respondWithItem($service_type, new ServiceTypeTransformer());
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
        $Service_Type = \Blegrator\Servicetype::find($id);
        if (is_null($Service_Type)) {
            return $this->responseWithNotFound();
        }

        // Sanitize Request Data
        $data = $request->only((new \Blegrator\Servicetype())->getFillable());

        // Check Upload Image
        foreach (['icon', 'coverphoto'] as $img_field) {
            if ($request->hasFile($img_field)) {
                $data[$img_field] = File::uploadImage(
                    $request,
                    $img_field,
                    \Blegrator\Servicetype::$Image_Path,
                    75,
                    300,
                    null
                );
            }
        }

        // Edit Service Type
        $Service_Type->update($data);

        // Return Data
        return $this->respondWithItem($Service_Type, new ServiceTypeTransformer());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $Service_Type = \Blegrator\Servicetype::find($id);
        if (is_null($Service_Type)) {
            return $this->responseWithNotFound();
        }

        // Delete Image
        foreach (['icon', 'coverphoto'] as $img_field) {
            if (!empty($Service_Type->{$img_field})) {
                File::deleteImageWithOriginal(\Blegrator\Servicetype::$Image_Path, $Service_Type->{$img_field});
            }
        }

        // Delete Service Type
        $Service_Type->delete();

        // Return Removed
        return $this->responseWithRemovedItem();
    }
}
