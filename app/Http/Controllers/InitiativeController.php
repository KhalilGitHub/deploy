<?php

namespace App\Http\Controllers;

use File;
use JWTAuth;
use App\Models\URL;
use App\Models\Initiative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InitiativeController extends Controller
{
    protected $user;

    public function __construct()
    {
       $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index()
    {
        $user = Auth::user();
        $inov = $this->user
            ->inov()
            ->get()
            ->toArray();
        return response()->json([
            'success' => true,
            'user' => $user,
            'data' => $inov,
        ]);
    }

    public function show($id)
    {
        try {
            $innov = $this->user->inov()->find($id);
            if (!$innov) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet enregistrement n\'existe pas'
                ], 400);
            }
            return response()->json([
                'success' => true,
                //'user' => $user->toArray(),
                'data' => $innov->toArray(),
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'success' => 'Cet enregistrement n\'existe pas'
            ], 400);
        }
    }

    public function imgName($path){
        if($path){
            $name = explode('\\', $path);
            $length = count($name);
            return $name[$length-1];
        }
    }

    public function upload($photo, $path){
        if($photo){

            $name = time().'_'.$this->imgName($path);
            $binary_data = base64_decode($photo);
            Storage::disk('s3')->put('initiatives/'.$name, $binary_data, 'public');

           /* $name = time().'_'.$this->imgName($s3Path);
            $path = public_path('public');
            // if(File::exists($path))
            // File::makeDirectory(public_path().$path, 777, true);
                \Image::make($photo)->save($path.$name);*/
        }
        return $name;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'initname' 	  => 'required'	,
            'field' 		=> 'required',
            'typeorg' 		=> 'required',
            'member' 		=> 'required',
            'level' 		=> 'required',
            'location' 		=> 'required',
            'tel' 			=> 'required',
            'link' 			=> 'required',
            'needs' 		=> 'required',
            'description' => 'required' ,
            'imgurl'     => 'required'
        ]);
        $init = new Initiative();
        $fileName = $this->upload($request->strfile, $request->imgurl);
        $init->initname = $request->initname;
        $init->field = $request->field;
        $init->typeorg = $request->typeorg;
        $init->member = $request->member;
        $init->level = $request->level;
        $init->location = $request->location;
        $init->tel = $request->tel;
        $init->link = $request->link;
        $init->needs = $request->needs;
        $init->description = $request->description;
        $init->imgurl = URL::URL_AWS.'/initiatives/'.$fileName;

        if (auth()->user()->inov()->save($init))
            return response()->json([
                'success' => true,
                'data' => $init->toArray()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Cet enregistrement n\'existe pas'
            ], 500);
    }

    public function dbImgName($path){
        if($path){
            $name = explode('/', $path);
            $length = count($name);
            return $name[$length-1];
        }
    }

    public function deleteImage($path)
    {
        $imageName = $this->dbImgName($path);
        $image_path = 'images/initiatives/'.$imageName;
        if(File::exists($image_path))
        {
            File::delete($image_path);

        }
    }



    public function update(Request $request, $id)
    {
        $innov = $this->user->inov()->find($id);
        if (!$innov) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 400);
        }
        $this->deleteImage($innov->imgurl);
        $fileName = $this->upload($request->strfile, $request->imgurl);
        $imgurl = URL::$url.$fileName;
        $innov->initname = is_null($request->initname) ? $innov->initname : $request->initname;
        $innov->field = is_null($request->field) ? $innov->field : $request->field;
        $innov->typeorg = is_null($request->typeorg) ? $innov->typeorg : $request->typeorg;
        $innov->member = is_null($request->member) ? $innov->member : $request->member;
        $innov->level = is_null($request->level) ? $innov->level : $request->level;
        $innov->location = is_null($request->location) ? $innov->location : $request->location;
        $innov->tel = is_null($request->tel) ? $innov->tel : $request->tel;
        $innov->link = is_null($request->link) ? $innov->link : $request->link;
        $innov->needs = is_null($request->needs) ? $innov->needs : $request->needs;
        $innov->description = is_null($request->description) ? $innov->description : $request->description;
        $innov->imgurl = is_null($request->imgurl) ? $imgurl : $request->imgurl;
        $updated = $innov->save();
        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Cet enregistrement ne peut pas être mis à jour'
            ], 500);
    }

    public function destroy($id)
    {
        $innov = $this->user->inov()->find($id);
        if (!$innov) {
            return response()->json([
                'success' => false,
                'message' => 'Cet enregistrement n\'existe pas'
            ], 400);
        }
        if ($innov->delete()) {
            $this->deleteImage($innov->imgurl);
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cet enregistrement ne peut pas être supprimé'
            ], 500);
        }
    }
}
