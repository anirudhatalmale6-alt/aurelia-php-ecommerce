<?php
namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Validator;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index(Request $request): void
    {
        $this->view('admin/categories/index', [
            'title'      => 'Categories',
            'categories' => Category::all(),
        ], 'layouts/admin');
    }

    public function store(Request $request): void
    {
        Csrf::verify();

        $v = new Validator($request->all());
        $v->required('name')->max('name', 120);
        if ($v->fails()) {
            Flash::withInput($request->all(), $v->errors());
            redirect('/admin/categories');
        }

        $name = (string) $request->input('name');
        Category::create([
            'name'        => $name,
            'slug'        => Category::uniqueSlug($name),
            'description' => $request->input('description', ''),
            'position'    => $request->int('position', 0),
        ]);

        Flash::add('success', 'Category added.');
        redirect('/admin/categories');
    }

    public function update(Request $request, string $id): void
    {
        Csrf::verify();

        $category = Category::find((int) $id);
        if (!$category) {
            Flash::add('error', 'Category not found.');
            redirect('/admin/categories');
        }

        $name = (string) $request->input('name', $category['name']);
        Category::update((int) $id, [
            'name'        => $name,
            'slug'        => Category::uniqueSlug($name, (int) $id),
            'description' => $request->input('description', ''),
            'position'    => $request->int('position', 0),
        ]);

        Flash::add('success', 'Category updated.');
        redirect('/admin/categories');
    }

    public function destroy(Request $request, string $id): void
    {
        Csrf::verify();
        Category::delete((int) $id);
        Flash::add('success', 'Category removed. Its products are now uncategorised.');
        redirect('/admin/categories');
    }
}
