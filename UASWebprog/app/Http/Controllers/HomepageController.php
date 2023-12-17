<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Foods;
use App\Models\Order_line;
use App\Models\Orders;
use App\Models\Carts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class HomepageController extends Controller
{
    public function index()
    {
        $dataKategori = Category::all();
        $dataFood = Foods::whereHas('perday', function ($query) {
            $query->where("date", date("Y-m-d"));
        })->get();
        return view('user.homepage', compact('dataKategori', 'dataFood'));
    }

    public function produk()
    {
        $dataFood = Foods::all();
        $dataKategori = Category::all();
        return view('user.product', compact('dataKategori', 'dataFood'));
    }

    public function beli(Request $request)
    {
        $id = $request->query('selectedItem');
        $food = Foods::find($id);
        if (!$food) {
            abort(404, 'Food not found');
        }
        return view('user.buy', ['selectedItem' => $food]);
    }

    public function tentangkami()
    {
        return view('user.aboutus');
    }

    public function infopesanan()
    {
        $order_line = Order_line::join('orders', 'orders.id', '=', 'order_line.orders')
            ->join('foods', 'foods.id', '=', 'order_line.foods')
            ->get();
        return view('user.infopesanan', compact('order_line'));
    }

    // carts

    public function getcart()
    {
        $data = Carts::where("user_id", Auth::id())
                    ->where("checked_out", false)
                    ->join('foods', 'carts.foods', '=', 'foods.id')
                    ->get();

        return datatables()->of($data)
        ->addColumn('action', function ($row) {
            return '<div class="btn-group">
                <button class="btn btn-danger btn-sm" id="btnDelFood" data-id="' . $row->id . '">
                <span class="fas fa-trash-alt"></span>
                </button>
                </div>';
        })
        ->editColumn('gambar', function ($row) {
            return '<img src="' . asset('storage/' . $row->photo) . '" alt="Food Photo" width="200">';
        })
        ->editColumn('total', function ($row) {
            return $row->qty * $row->harga;
        })
        ->rawColumns(['action', 'gambar'])
        ->make(true);
    }

    public function addtocart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'foods' => 'required|integer',
            'qty' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 404,
                'errors' => $validator->errors()->toArray()
            ]);
        } else {
            $dataFood = new Carts();
            $dataFood->foods = $request->input('foods');
            $dataFood->qty = $request->input('qty');
            $dataFood->user_id = Auth::id();
            $dataFood->save();

            return response()->json([
                'status' => 200,
                'message' => 'Pesanan berhasil ditambahkan ke keranjang'
            ]);
        }
    }

    public function removefromcart(Request $request)
    {

        $dataFood = Carts::findOrFail($request->get('id'));

        $dataFood->delete();

        return response()->json([
            'status' => 200,
        ]);
    }

    public function getsubtotal(Request $request)
    {
        $data = Carts::where("user_id", Auth::id())
        ->where("checked_out", false)
        ->join('foods', 'carts.foods', '=', 'foods.id')
        ->get();

        $total = 0;
        foreach($data as $value){
            $total = $total + ($value->harga * $value->qty);
        }

        return response()->json([
            'status' => 200,
            'total' => $total
        ]);
    }
}
