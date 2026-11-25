<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\reviewRequest;
use App\Http\Requests\API\reviews\create;
use App\Http\Requests\API\reviews\UpdateReviewRequest;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
public function index(reviewRequest $request)
{
    $product = $request -> validated();
    $reviews = Review::with("user")
        ->where("product_id", $product['product_id'])
        ->paginate(10);

    if ($reviews->isEmpty()) {
        return response()->json([
            "success" => true,
            "message" => 'no reviews',
            "data" => []
        ]);
    }

    return response()->json([
        "success" => true,
        "message" => 'retrieved reviews successfully',
        "data" => $reviews
    ]);
}
    public function store(create $request)
    {
        $data = $request->validated();
        $user = Auth::user();
        $reviews = Review::create([
            "comment"=>$data["comment"],
            "rate"=>$data["rate"],
            "user_id"=>$user->id,
            "product_id"=>$data["product_id"],
        ]);
        return response()->json([
            "success"=>true,
            "message"=> __("added reviews succesfully"),
            "data"=>$reviews
        ]);
    }

    /**
     * List the authenticated user's reviews with product metadata.
     */
    public function myReviews()
    {
        $reviews = Review::with(['product' => function ($q) {
            $q->withTrashed()->with(['variants' => function ($vq) {
                $vq->withTrashed();
            }]);
        }])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        if ($reviews->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'no reviews',
                'data'    => [],
            ]);
        }

        $reviews->getCollection()->transform(function ($review) {
            $variant = $review->product?->variants?->first();

            return [
                'id'         => $review->id,
                'product_id' => $review->product_id,
                'user_id'    => $review->user_id,
                'comment'    => $review->comment,
                'rate'       => $review->rate,
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
                'product'    => $review->product ? [
                    'id'         => $review->product->id,
                    'name'       => $review->product->name,
                    'image_path' => optional($variant)->image_path,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'retrieved reviews successfully',
            'data'    => $reviews,
        ]);
    }

    /**
     * Update an existing review owned by the authenticated user.
     */
    public function update(UpdateReviewRequest $request, mixed $id = null)
    {
        $reviewOrResponse = $this->resolveOwnedReview($request, $id);
        if ($reviewOrResponse instanceof JsonResponse) {
            return $reviewOrResponse;
        }

        $data = $request->validated();

        if (array_key_exists('comment', $data) && $data['comment'] !== null) {
            $reviewOrResponse->comment = $data['comment'];
        }

        if (array_key_exists('rate', $data) && $data['rate'] !== null) {
            $reviewOrResponse->rate = (string) $data['rate'];
        }

        $reviewOrResponse->save();

        return response()->json([
            'success' => true,
            'message' => __('Review updated successfully'),
            'data'    => $reviewOrResponse,
        ]);
    }

    /**
     * Delete a review owned by the authenticated user.
     */
    public function destroy(Request $request, mixed $id = null)
    {
        $reviewOrResponse = $this->resolveOwnedReview($request, $id);
        if ($reviewOrResponse instanceof JsonResponse) {
            return $reviewOrResponse;
        }

        $reviewOrResponse->delete();

        return response()->json([
            'success' => true,
            'message' => __('Review deleted successfully'),
        ]);
    }

    /**
     * Locate a review by route, review_id, or id and enforce ownership.
     */
    private function resolveOwnedReview(Request $request, mixed $routeId = null): Review|JsonResponse
    {
        $reviewId = $routeId ?? $request->input('review_id') ?? $request->input('id');

        if (!is_numeric($reviewId)) {
            return response()->json([
                'success' => false,
                'message' => __('Review id is required'),
            ], 422);
        }

        $review = Review::find((int) $reviewId);
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => __('Review not found'),
            ], 404);
        }

        if ((int) $review->user_id !== (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthorized'),
            ], 403);
        }

        return $review;
    }
}