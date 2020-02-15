<?php

namespace Blegrator\Http\Controllers\Api\Profile;

use Illuminate\Http\Request;
use Blegrator\Events\User\ChangedAvatar;
use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Http\Requests\User\UploadAvatarRawRequest;
use Blegrator\Repositories\User\UserRepository;
use Blegrator\Services\Upload\UserAvatarManager;
use Blegrator\Transformers\UserTransformer;

/**
 * Class DetailsController
 * @package Blegrator\Http\Controllers\Api\Profile
 */
class AvatarController extends ApiController
{
    /**
     * @var UserRepository
     */
    private $users;
    /**
     * @var UserAvatarManager
     */
    private $avatarManager;

    public function __construct(UserRepository $users, UserAvatarManager $avatarManager)
    {
        $this->middleware('auth');

        $this->users = $users;
        $this->avatarManager = $avatarManager;
    }

    public function update(UploadAvatarRawRequest $request)
    {
        $name = $this->avatarManager->uploadAndCropAvatar(
            $request->file('file')
        );

        $user = $this->users->update(
            auth()->id(),
            ['avatar' => $name]
        );

        event(new ChangedAvatar);

        return $this->respondWithItem($user, new UserTransformer);
    }

    public function updateExternal(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|url'
        ]);

        $this->avatarManager->deleteAvatarIfUploaded(
            auth()->user()
        );

        $user = $this->users->update(
            auth()->id(),
            ['avatar' => $request->url]
        );

        event(new ChangedAvatar);

        return $this->respondWithItem($user, new UserTransformer);
    }

    /**
     * Remove avatar for currently authenticated user and set it to null.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $user = auth()->user();

        $this->avatarManager->deleteAvatarIfUploaded($user);

        $user = $this->users->update(
            $user->id,
            ['avatar' => null]
        );

        event(new ChangedAvatar);

        return $this->respondWithItem($user, new UserTransformer);
    }
}
