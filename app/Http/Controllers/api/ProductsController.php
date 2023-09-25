<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Product;
use http\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\Providers\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductsController extends Controller
{
    //    public function index()
    //    {
    //        $products = Product::get();
    //
    //
    //        return response()->json($products);
    //    }
    //    public function index(Request $request)
    //    {
    //        $query = Product::query();
    //
    //        if ($request->has('sort')) {
    //            $sort = explode(',', $request->input('sort'));
    //            foreach ($sort as $s) {
    //                $direction = Str::startsWith($s, '-') ? 'desc' : 'asc';
    //                $field = ltrim($s, '-');
    //                $query->orderBy($field, $direction);
    //            }
    //        }
    //
    //        $items = $query->get();
    //
    //        return response()->json($items);
    //    }
    //    public function index(Request $request)
    //    {
    //        // Создаем запрос к модели Product
    //        $query = Product::query();
    //
    //        // Проверяем наличие параметра сортировки в запросе
    //        if ($request->has('sort')) {
    //            // Получаем список полей для сортировки из параметра "sort"
    //            $sortFields = explode(',', $request->input('sort'));
    //
    //            // Проходимся по каждому полю для сортировки
    //            foreach ($sortFields as $sortField) {
    //                // Определяем направление сортировки (по возрастанию или убыванию)
    //                $direction = Str::startsWith($sortField, '-') ? 'desc' : 'asc';
    //
    //                // Удаляем знак минуса (если есть) из имени поля для сортировки
    //                $fieldName = ltrim($sortField, '-');
    //
    //                // Добавляем условие сортировки по полю и направлению в запрос
    //                $query->orderBy($fieldName, $direction === 'asc' ? 'desc' : 'asc'); // изменение направления сортировки
    //            }
    //        }
    //
    //        // Выполняем запрос и получаем список продуктов
    //        $items = $query->get();
    //
    //        // Возвращаем результат в виде JSON-ответа
    //        return response()->json($items);
    //    }

    public function searchProduct()
    {
        $products = Product::take(20)->get();
        return response()->json($products);
    }
    public function showProduct($id)
    {

        $product = Product::with('images')->findOrFail($id);

        return response()->json($product);

    }

    public function allComments($productId)
    {
        $product = Product::findOrFail($productId);

        $comments = $product->comments()->orderBy('created_at', 'desc')->with('user')->get();
        return response()->json($comments);
    }

    public function storeComment($productId, CommentRequest $request)
    {
        $product = Product::findOrFail($productId);

        $user = JWTAuth::user();
        $comment = $product->comments()->create([
            'message' => $request->input('message'),
            'rating' => $request->input('rating'),
            'user_id' => $user->id,
        ]);

        return response()->json($comment);
    }

    public function showRecommendationProducts()
    {
        $products = Product::inRandomOrder()->get();
        return response()->json($products);
    }

    public function ratings($productId)
    {
        $ratings = Comment::where('product_id', $productId)->pluck('rating');
        return response()->json($ratings);
    }


    //    public function index(Request $request)
    //    {
    //        $sortBy = $request->input('order_by', 'price');
    //        $sortOrder = $request->input('sort_by', 'asc');
    //        $perPage = $request->input('per_page', 8);
    //
    //       $products = Product::orderBy($sortBy, $sortOrder)->paginate($perPage);
    //
    //        return response()->json($products);
    //    }


    public function index(Request $request)
    {

        $orderBy = $request->input('order_by', 'name');
        $sortBy = $request->input('sort_by', 'asc');
        $perPage = $request->input('per_page', 8);
        $searchQuery = $request->input('search_query');
        $priceRange = $request->input('price_range');
        $query = Product::query();
        // поиск по строке запроса
        if ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%');
        }
        $maxPriceQuery = $query;
        // фильтрация по диапазону цен
        if ($priceRange ) {
            $minPrice = $priceRange[0];
            $maxPrice = $priceRange[1];
            if ( $maxPrice > 0){
                $query->where('price', '>=', $minPrice)
                    ->where('price', '<=', $maxPrice);
            }

        }
        $query->orderBy($orderBy, $sortBy);
        $products = $query->paginate($perPage);
        $maxPrice = Product::max('price');
        return response()->json([
            'products' => $products,
            'max_price' => $maxPrice
        ]);
    }



    //    public function index(Request $request)
    //    {
    //        $query = Product::query();
    //
    //        if ($request->has('sort_by')&& $request->has('order_by')) {
    //                    $query->orderBy($request->order_by,$request->sort_by);
    //        }
    //        $products = $query->paginate(11);
    //
    //        return response()->json($products);
    //    }

}

