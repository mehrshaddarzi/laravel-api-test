<?php

namespace Blegrator\Http\Controllers\Api\Service;

use Blegrator\Helper\File;
use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Transformers\ServiceTransformer;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class Service extends ApiController
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
        $list = QueryBuilder::for(\Blegrator\Service::class)
            ->allowedFilters([
                AllowedFilter::exact('servicetype_id'),
                AllowedFilter::exact('name'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::exact('id')
            ])
            ->defaultSort('-id') //DESC
            ->allowedSorts('id')
            ->paginate(($request->has('per_page') ?: 15))
            ->appends(request()->query());

        return $this->respondWithPagination($list, new ServiceTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->only((new \Blegrator\Service())->getFillable());
        $validator = Validator::make($request->all(), [
            'servicetype_id' => ['required', 'integer', 'exists:servicetypes,id'],
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
                    \Blegrator\Service::$Image_Path,
                    75,
                    1024,
                    null
                );
            }
        }

        // Create New Item
        $service = \Blegrator\Service::create($data);

        // Return Data
        return $this->respondWithItem($service, new ServiceTransformer);
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
        $service_item = \Blegrator\Service::find($id);
        if (is_null($service_item)) {
            return $this->responseWithNotFound();
        }

        // Sanitize Request Data
        $data = $request->only((new \Blegrator\Service())->getFillable());
        $validator = Validator::make($request->all(), [
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
                    \Blegrator\Service::$Image_Path,
                    75,
                    1024,
                    null
                );
            }
        }

        // Edit Service
        $service_item->update($data);

        // Return Data
        return $this->respondWithItem($service_item, new ServiceTransformer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $service_item = \Blegrator\Service::find($id);
        if (is_null($service_item)) {
            return $this->responseWithNotFound();
        }

        // Delete Image
        foreach (['icon', 'coverphoto'] as $img_field) {
            if (!empty($service_item->{$img_field})) {
                File::deleteImageWithOriginal(\Blegrator\Service::$Image_Path, $service_item->{$img_field});
            }
        }

        // Remove Service Item
        $service_item->delete();

        // Return Removed
        return $this->responseWithRemovedItem();
    }
}
