<?php

namespace App\Http\Controllers;

use App\Models\Post;
use GrahamCampbell\ResultType\Result;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
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
                'post' => 'required|array'
            ])
        );
        return $this->getValidations();
    }

    private function definePostValidations() {
        $request = $this->getRequest();
        $this->setValidations(
            Validator::make($request->input('post'), [
                'tittle' => 'required|string',
                'content' => 'required|string',
                'image' => 'required|string',
                'author_id' => 'required|numeric'
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
        $post = new Post();
        $result = $post->Search($request->filter)->with(['author'])->simplePaginate($per_page)->toArray();
        $result['total'] = count($post->Search($request->filter)->get());

        for ($i = 0; $i < count($result['data']); $i++) {
            $result['data'][$i]['image'] = url(\Storage::url($result['data'][$i]['image']));
        }
        
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
        $validatorAuthor = $this->definePostValidations();
        if ($validatorAuthor->fails()) {
            return response()->json([
                'error' => $validatorAuthor->errors()->first(),
            ], 401);
        }

        $data = $request->input('post');

        //validacion para la insercion de la imagen
        $imageBase64 = $this->getB64Image($data['image']);
        $extension = $this->getB64Extension($data['image']);
        $img_name = 'imagesPost/post'. time() . '.' . $extension; 
        
        Storage::disk('public')->put($img_name, $imageBase64);
        $data['image'] = $img_name;

        $post = Post::create($data);

        return response()->json([
            'Se inserto el post De manera correcta' => $post,
        ], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::whereId($id)->with('author')->first();
        $post['image'] =  url(\Storage::url($post['image']));
        return $post;
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
        $validatorPost = $this->definePostValidations();
        if ($validatorPost->fails()) {
            return response()->json([
                'error' => $validatorPost->errors()->first(),
            ], 401);
        }

        $data = $request->input('post');

        //validacion para la insercion de la imagen
        $imageBase64 = $this->getB64Image($data['image']);
        $extension = $this->getB64Extension($data['image']);
        $img_name = 'imagesPost/post'. time() . '.' . $extension; 
        
        Storage::disk('public')->put($img_name, $imageBase64);
        $data['image'] = $img_name;
        $post = Post::whereId($id)->update($data);
        
        return response()->json([
            'Se actualizo el post De manera correcta' => $post,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Post::whereId($id)->delete();
    }

    public function getB64Image($base64_image){  
        // Obtener el String base-64 de los datos         
        $image_service_str = substr($base64_image, strpos($base64_image, ",")+1);
        // Decodificar ese string y devolver los datos de la imagen        
        $image = base64_decode($image_service_str);   
        // Retornamos el string decodificado
        return $image; 
   }

   public function getB64Extension($base64_image, $full=null){  
    // Obtener mediante una expresión regular la extensión imagen y guardarla
    // en la variable "img_extension"        
    preg_match("/^data:image\/(.*);base64/i",$base64_image, $img_extension);   
    // Dependiendo si se pide la extensión completa o no retornar el arreglo con
    // los datos de la extensión en la posición 0 - 1
    return ($full) ?  $img_extension[0] : $img_extension[1];  
}
}
