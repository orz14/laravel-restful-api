<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function create(int $idContact, AddressCreateRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $contact = Contact::where('user_id', $user->id)->where('id', $idContact)->first();
            if (! $contact) {
                return $this->sendError([
                    'message' => [
                        'Contact not found',
                    ],
                ]);
            }

            $data = $request->validated();
            $address = new Address($data);
            $address->contact_id = $contact->id;
            $address->save();
            $result = new AddressResource($address);

            return $this->sendResponse($result, 201);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function get(int $idContact, int $idAddress): JsonResponse
    {
        try {
            $user = Auth::user();
            $contact = Contact::where('user_id', $user->id)->where('id', $idContact)->first();
            if (! $contact) {
                return $this->sendError([
                    'message' => [
                        'Contact not found',
                    ],
                ]);
            }
            $address = Address::where('contact_id', $contact->id)->where('id', $idAddress)->first();
            if (! $address) {
                return $this->sendError([
                    'message' => [
                        'Address not found',
                    ],
                ]);
            }
            $result = new AddressResource($address);

            return $this->sendResponse($result);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function update(int $idContact, int $idAddress, AddressUpdateRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $contact = Contact::where('user_id', $user->id)->where('id', $idContact)->first();
            if (! $contact) {
                return $this->sendError([
                    'message' => [
                        'Contact not found',
                    ],
                ]);
            }
            $address = Address::where('contact_id', $contact->id)->where('id', $idAddress)->first();
            if (! $address) {
                return $this->sendError([
                    'message' => [
                        'Address not found',
                    ],
                ]);
            }

            $data = $request->validated();
            $address->fill($data);
            $address->save();
            $result = new AddressResource($address);

            return $this->sendResponse($result);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function delete(int $idContact, int $idAddress): JsonResponse
    {
        try {
            $user = Auth::user();
            $contact = Contact::where('user_id', $user->id)->where('id', $idContact)->first();
            if (! $contact) {
                return $this->sendError([
                    'message' => [
                        'Contact not found',
                    ],
                ]);
            }
            $address = Address::where('contact_id', $contact->id)->where('id', $idAddress)->first();
            if (! $address) {
                return $this->sendError([
                    'message' => [
                        'Address not found',
                    ],
                ]);
            }

            $address->delete();

            return $this->sendResponse();
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }

    public function list(int $idContact): JsonResponse
    {
        try {
            $user = Auth::user();
            $contact = Contact::where('user_id', $user->id)->where('id', $idContact)->first();
            if (! $contact) {
                return $this->sendError([
                    'message' => [
                        'Contact not found',
                    ],
                ]);
            }

            $addresses = Address::where('contact_id', $contact->id)->get();
            $result = AddressResource::collection($addresses);

            return $this->sendResponse($result);
        } catch (\Throwable $err) {
            return $this->sendError([
                'message' => [
                    $err->getMessage(),
                ],
            ], 500);
        }
    }
}
