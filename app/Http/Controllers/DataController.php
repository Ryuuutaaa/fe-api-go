<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DataController extends Controller
{
    protected $client;
    protected $apiUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = env("BASE_API_URL", "http://localhost:1323");
    }

    public function index()
    {
        return view("welcome");
    }

    public function getAllUser()
    {
        try {
            $fullUrl = "{$this->apiUrl}/users";

            $response = $this->client->request("GET", $fullUrl, [
                "status" => 200,
                "json" => true
            ]);

            $data = json_decode($response->getBody(), true);
            return response()->json([
                'status' => true,
                'data' => $data
            ], 200);
        } catch (RequestException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createUser(Request $request)
    {
        if (!$request->ajax()) {
            return redirect()->back();
        }

        $name = $request->name;
        $email = $request->email;

        $validator = Validator::make([
            'name' => $name,
            'email' => $email
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        try {
            $fullUrl = "{$this->apiUrl}/users/create";

            $response = $this->client->request("POST", $fullUrl, [
                'form_params' => [
                    'name' => $name,
                    'email' => $email
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'data' => $data
            ], 201);
        } catch (RequestException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateUser(Request $request, $id)
    {
        if (!$request->ajax()) {
            return redirect()->back();
        }

        $name = $request->name;
        $email = $request->email;

        $validator = Validator::make([
            'name' => $name,
            'email' => $email
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()
            ], 422);
        }

        try {
            $fullUrl = "{$this->apiUrl}/users/{$id}";

            $response = $this->client->request("PUT", $fullUrl, [
                'form_params' => [
                    'name' => $name,
                    'email' => $email
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => $data
            ], 200);
        } catch (RequestException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            $fullUrl = "{$this->apiUrl}/users/{$id}";

            $response = $this->client->request("DELETE", $fullUrl);

            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } catch (RequestException $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
