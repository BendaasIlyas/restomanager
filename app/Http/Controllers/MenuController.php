<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Rules\RestoCategoryValidation;
use App\Services\MenuService;
use App\Models\Menu;

class MenuController extends Controller
{

    public function index($id){
        $restoId = $id;
        $service = new MenuService;
        $menus = $service->getMenuWithCategory($restoId);
    
        return view('menu.menu-index',compact('menus', 'restoId'));

    }

    public function addMenuItem(Request $request)
    {
        $postData = $this->validate($request, [
            'item' => 'required|min:3',
            'price' => 'required|numeric',
            'restoId' => 'required|numeric',
            'description' => 'required|min:3',
            'category' => ['required', new RestoCategoryValidation(request('restoId'))],
        ]);

        $conditions = [
            'resto_id' => $postData['restoId'],
            'name' => $postData['category'],
        ];

        $category = Category::where($conditions)
            ->first();

        $menu = $category->menus()->create([
            'name' => $postData['item'],
            'price' => $postData['price'],
            'description' => $postData['description'],
            'resto_id' => $postData['restoId'],
        ]);

        return response()->json($menu, 201);
    }

    public function getRestoMenu(Request $request){

        $this->validate($request, [
            'restoId' => 'required|exists:restaurants,id'
        ]);

        $menuItems = Menu::where('resto_id', $request->input('restoId'))
        ->orderBy('category_id')    
        ->get();

        return response()->json($menuItems, 200);

    }
    

}
