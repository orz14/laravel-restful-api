<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function create(ContactCreateRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $user = Auth::user();

            $contact = new Contact($data);
            $contact->user_id = $user->id;
            $contact->save();
            $result = new ContactResource($contact);

            return $this->sendResponse($result, 201);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function get(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
            if (!$contact) {
                return $this->sendError([
                    'message' => [
                        'Contact not found',
                    ],
                ], 404);
            }
            $result = new ContactResource($contact);

            return $this->sendResponse($result);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function update(int $id, ContactUpdateRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
            if (!$contact) {
                return $this->sendError([
                    'message' => [
                        'Contact not found',
                    ],
                ], 404);
            }

            $data = $request->validated();
            $contact->fill($data);
            $contact->save();
            $result = new ContactResource($contact);

            return $this->sendResponse($result);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $contact = Contact::where('id', $id)->where('user_id', $user->id)->first();
            if (!$contact) {
                return $this->sendError([
                    'message' => [
                        'Contact not found',
                    ],
                ], 404);
            }

            $contact->delete();

            return $this->sendResponse();
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function search(Request $request): ContactCollection
    {
        try {
            $user = Auth::user();
            $page = $request->input('page', 1);
            $size = $request->input('size', 10);

            $contacts = Contact::query()->where('user_id', $user->id);

            $contacts = $contacts->where(function (Builder $builder) use ($request) {
                $name = $request->input('name');
                if ($name) {
                    $builder->where(function (Builder $builder) use ($name) {
                        $builder->orWhere('first_name', 'like', '%' . $name . '%');
                        $builder->orWhere('last_name', 'like', '%' . $name . '%');
                    });
                }

                $email = $request->input('email');
                if ($email) {
                    $builder->where('email', 'like', '%' . $email . '%');
                }

                $phone = $request->input('phone');
                if ($phone) {
                    $builder->where('phone', 'like', '%' . $phone . '%');
                }
            });

            $contacts = $contacts->paginate(perPage: $size, page: $page);

            return new ContactCollection($contacts);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }
}
