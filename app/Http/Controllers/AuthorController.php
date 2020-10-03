<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    private $validations;
    private $request;

    private function getValidations() {
        return $this->validations;
    }

    private function setValidations($validations) {
        $this->validations = $validations;
    }

    private function getRequest() {
        return $this->request;
    }

    private function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * Tiene como objetivo definir las validaciones del controllador
     * @author Andres Felipe Torres Vega
     * @param void
     * @return object of Validators
     */
    private function defineInitialValidations() {
        $request = $this->getRequest();
        $this->setValidations(
            Validator::make($request->all(), [
                'author' => 'required|array'
            ])
        );
        return $this->getValidations();
    }

    private function defineAuthorValidations() {
        $request = $this->getRequest();
        $this->setValidations(
            Validator::make($request->input('author'), [
                'name' => 'required|string',
                'last_name' => 'required|string'
            ])
        );
        return $this->getValidations();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page == null ? 10 : $request->per_page;
        $author = new Author();
        $result = $author->Search($request->filter)->with(['posts'])->simplePaginate($per_page)->toArray();
        $result['total'] = count($author->Search($request->filter)->get());
        
        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->setRequest($request);
        //se definen las validaciones iniciales
        $validatorInitial = $this->defineInitialValidations();
        if ($validatorInitial->fails()) {
            return response()->json([
                'error' => $validatorInitial->errors()->first(),
            ], 401);
        }

        //se definen las validaciones del autor
        $validatorAuthor = $this->defineAuthorValidations();
        if ($validatorAuthor->fails()) {
            return response()->json([
                'error' => $validatorAuthor->errors()->first(),
            ], 401);
        }


        $author = Author::create($request->input('author'));

        return response()->json(true);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Author::whereId($id)->with('posts')->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->setRequest($request);
        //se definen las validaciones iniciales
        $validatorInitial = $this->defineInitialValidations();
        if ($validatorInitial->fails()) {
            return response()->json([
                'error' => $validatorInitial->errors()->first(),
            ], 401);
        }

        //se definen las validaciones del autor
        $validatorAuthor = $this->defineAuthorValidations();
        if ($validatorAuthor->fails()) {
            return response()->json([
                'error' => $validatorAuthor->errors()->first(),
            ], 401);
        }

        $author = Author::whereId($id)->update($request->input('author'));
        
        return response()->json(true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Author::whereId($id)->delete();
        return response()->json([
            true,
        ], 200);
    }
}
