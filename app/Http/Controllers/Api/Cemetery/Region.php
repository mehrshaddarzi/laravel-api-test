<?php

namespace Blegrator\Http\Controllers\Api\Cemetery;

use Blegrator\Helper\File;
use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Transformers\RegionTransformer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response as Response_Code;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class Region extends ApiController
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
        $list = QueryBuilder::for(\Blegrator\Region::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('name')
            ])
            ->defaultSort('id') //ASC
            ->allowedSorts('id')
            ->paginate(($request->has('per_page') ?: 50))
            ->appends(request()->query());

        return $this->respondWithPagination($list, new RegionTransformer());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->only((new \Blegrator\Region())->getFillable());

        // Validation Request
        $validator = Validator::make($data, [
            'name' => ['required'],
            'photo' => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:10000'] //10MB
        ]);
        if ($validator->fails()) {
            return $this->responseWithValidateError($validator->errors()->all());
        }

        // Upload Image File
        if ($request->hasFile('photo')) {
            $data['photo'] = File::uploadImage($request, 'photo', \Blegrator\Region::$Image_Path, 75, 1024, null);
        }

        // Create New Item
        $region = \Blegrator\Region::create($data);

        // Return Data
        return $this->respondWithItem($region, new RegionTransformer());
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
        $Region = \Blegrator\Region::find($id);
        if (is_null($Region)) {
            return $this->responseWithNotFound();
        }

        // Request Field
        $data = $request->only((new \Blegrator\Region())->getFillable());

        // Validation Request
        $validator = Validator::make($data, [
            'photo' => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:10000'] //10MB
        ]);
        if ($validator->fails()) {
            return $this->responseWithValidateError($validator->errors()->all());
        }

        // Check Upload Image
        if ($request->hasFile('photo')) {
            $data['photo'] = File::uploadImage($request, 'photo', \Blegrator\Region::$Image_Path, 75, 1024, null);
        }

        // Edit Action
        $Region->update($data);

        // Return Data
        return $this->respondWithItem($Region, new RegionTransformer());
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
        $Region = \Blegrator\Region::find($id);
        if (is_null($Region)) {
            return $this->responseWithNotFound();
        }

        // First Remove Attachment
        if (!empty($Region->photo)) {
            File::deleteImageWithOriginal(\Blegrator\Region::$Image_Path, $Region->photo);
        }

        // Remove From Database
        $Region->delete();

        return $this->responseWithRemovedItem();
    }
}
