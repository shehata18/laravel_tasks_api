<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//         return auth()->user()->categories;
//        $user = auth()->user();
//        if (!$user) {
//            return response()->json(['error' => 'Unauthenticated.'], 401);
//        }
//        $categories = $user->categories;



//        $categories = Category::all();
        $categories = auth()->user()->categories()->with('tasks')->paginate(2);
        return CategoryResource::collection($categories);

}
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required',
        ]);
        return auth()->user()->categories()->create($request->all());

    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        if(auth()->id() != $category->user_id){
            return response()->json(['messaage'=>'You don\'t own this resource'],401);
        }
        $category->load('tasks');
        return new CategoryResource(($category));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        if (auth()->id() != $category->user_id) {
            return response()->json(['message' => 'You don\'t own this category'], 401);

        }
        $validatedData = $request->validate([
            'title' => 'required',
        ]);
        if ($category->update($request->all())) {
            return response()->json(['message' => 'Category updated Successfully'], 201);
        } else {
            return response()->json(['message' => 'Error try again'], 500);

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Check if the authenticated user owns the category
        if (auth()->id() != $category->user_id) {
            return response()->json(['message' => 'You don\'t own this Category'], 401);
        }

        // Store category details before deleting it
        $deletedCategory = $category->toArray();

        // Delete the category
        if ($category->delete()) {
            return response()->json([
                'message' => 'Category deleted Successfully',
                'deletedCategory' => $deletedCategory
            ], 201);
        }

        return response()->json(['message' => 'Error try again'], 500);
    }

    public function restore($categoryId)
    {
        $category = Category::withTrashed()->findOrFail($categoryId);

        // Check if the authenticated user owns the category
        if (auth()->id() != $category->user_id) {
            return response()->json(['message' => 'You don\'t own this Category'], 401);
        }

        if ($category->restore()) {
            return ['message' => 'Restored Category Successfully'];
        }
        return response()->json(['message' => 'You have an error'], 500);

    }

    public function forceDelete($categoryId)
    {
        $category = Category::withTrashed()->findOrFail($categoryId);

        // Check if the authenticated user owns the category
        if (auth()->id() != $category->user_id) {
            return response()->json(['message' => 'You don\'t own this Category'], 401);
        }

        if ($category->forceDelete()) {
            return response()->json([
                'message' => 'Category force deleted Successfully',
            ], 201);
        }

        return response()->json(['message' => 'Error try again'], 500);
    }

}
