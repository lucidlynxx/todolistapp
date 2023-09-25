<?php

namespace App\Http\Controllers;

use App\Http\Requests\TodoStoreRequest;
use App\Http\Requests\TodoUpdateRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $todo = Todo::where('user_id', auth()->user()->id)->get();

        // if ($todo->isEmpty()) {
        //     throw new HttpResponseException(response()->json([
        //         'errors' => [
        //             'message' => [
        //                 'Not Found'
        //             ]
        //         ]
        //     ])->setStatusCode(404));
        // }

        $modifiedTodos = $todo->map(function ($todo) {
            $todo->status = true;

            return $todo;
        });

        return (TodoResource::collection($modifiedTodos))->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TodoStoreRequest $todoStoreRequest): JsonResponse
    {
        $validatedData = $todoStoreRequest->validated();

        $validatedData['user_id'] = auth()->user()->id;

        $todo = Todo::create($validatedData);

        $todo->status = true;

        return (new TodoResource($todo))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $todo): JsonResponse
    {
        $data = Todo::find($todo);

        if (is_null($data)) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Not Found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        $data->status = true;

        return (new TodoResource($data))->response()->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TodoUpdateRequest $todoUpdateRequest, string $todo): JsonResponse
    {
        $validatedData = $todoUpdateRequest->validated();

        $data = Todo::find($todo);

        if (is_null($data)) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Not Found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        $data->has_completed = $validatedData['has_completed'];
        $data->save();
        $data->status = true;

        return (new TodoResource($data))->response()->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $todo): JsonResponse
    {
        $data = Todo::find($todo);

        if (is_null($data)) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Not Found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        $data->delete();

        return response()->json([
            'status' => true,
            'message' => 'data deleted'
        ])->setStatusCode(200);
    }
}
