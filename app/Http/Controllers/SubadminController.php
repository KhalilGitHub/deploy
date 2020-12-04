<?php

namespace App\Http\Controllers;

use DB;
use JWTAuth;
use App\Models\Subadmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class SubadminController extends Controller
{
    public function __construct()
    {
        $this->subadmin = new Subadmin;
    }

    public function loginadmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        config()->set( 'auth.defaults.guard', 'subadmins' );
        \Config::set('jwt.user', 'App\Models\Subadmin');
        \Config::set('auth.providers.users.model', \Subadmin::class);
        $credentials = $request->only('email', 'password');
        $token = null;
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Le Nom d\'Utilisateur ou le Mot Passe est Icorrecte...!'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:subadmins',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $subadmin = Subadmin::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);
        $token = JWTAuth::fromUser($subadmin);
        return response()->json(compact('subadmin','token'),201);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['Cet Utilisateur n\'existe pas...'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }

    public function findInitiative($id)
    {
        try {
            $innov = DB::table('initiatives')
                ->join('users','initiatives.user_id','=','users.id')
                ->where(['initiatives.init_id' => $id])
                ->get();
            if (!$innov) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet enregistrement n\'existe pas'
                ], 400);
            }
            return response()->json([
                'success' => true,
                //'user' => $user->toArray(),
                'progress' => $innov->toArray(),
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'message' => $exception
            ], 400);
        }
    }


    public function nInitiatives()
    {
        $initiatives = DB::table('initiatives')
            ->latest('initiatives.created_at')
            ->join('users', 'initiatives.user_id', '=', 'users.id')
            ->get()
            ->take(3);

        return response()->json([
            'success' => true,
            'initiatives' => $initiatives,
        ]);
    }
    public function allInitiatives()
    {
        $initiatives = DB::table('initiatives')
            ->join('users', 'initiatives.user_id', '=', 'users.id')
            ->get();
        return response()->json([
            'success' => true,
            'initiatives' => $initiatives
        ]);
    }
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
    public function update(Request $request, $id)
    {
        $me = auth()->user()->find($id);
        $me->name = is_null($request->name) ? $me->name : $request->name;
        $me->email = is_null($request->email) ? $me->email : $request->email;

        if (!$me) {
            return response()->json([
                'success' => false,
                'message' => 'Cet enregistrement n\'existe pas'
            ], 400);
        }
        $updated = $me->save();
        if ($updated)
            return response()->json([
                'success' => true
            ], 200);
        else
            return response()->json([
                'success' => false,
                'message' => 'Cet enregistrement ne peut pas être mis à jour'
            ], 304);
    }

    public function changePwd(Request $request, $id)
    {
        $this->validate($request, [
            'oldpassword' => 'required',
            'newpassword' => 'required|string|min:6|confirmed',
        ]);
        $hashedPassword = Auth::user()->password;
        if (\Hash::check($request->oldpassword , $hashedPassword )) {
            if (!\Hash::check($request->newpassword , $hashedPassword)) {

                $users =subadmin::find(Auth::user()->id);
                $users->password = Hash::make($request->newpassword);
                subadmin::where( 'id' , Auth::user()->id)->update( array( 'password' =>  $users->password));

                return response()->json([
                    'message' => 'Le Mot de passe a ete change avec succes...'
                ], 200);
            }
            else{
                return response()->json([
                    'message' => 'Le mot de passe ne peut pas être changer !!!'
                ], 304);
            }
        }
        else{
            return response()->json([
                'message' => 'Le nouveau mot de passe et l\'ancien mot de passe ne se correspondent pas !!!'
            ], 304);
        }
    }


    public function allUsers()
    {
        $users = DB::table('users')->get()->all();
        return response()->json([
            'users' => $users,
        ]);
    }

    public function allAdmins()
    {
        $subadmins = DB::table('subadmins')->get()->all();
        return response()->json([
            'subadmins' => $subadmins,
        ]);
    }

    public function blogs()
    {
        $blogs = DB::table('blogs')
            ->join('subadmins', 'blogs.user_id', '=', 'subadmins.id')
            ->get();
        return response()->json([
            'success' => true,
            'blogs' => $blogs,
        ]);
    }

    public function destroy($id)
    {
        /*
         DB::table('innov_ideations')
            ->where(['innov_ideations.user_id' => $id])
            ->delete();
        DB::table('innov_progress')
            ->where(['innov_progress.user_id' => $id])
            ->delete();
        */
        DB::table('blogs')
            ->where(['blogs.user_id' => $id])
            ->delete();
        $user = auth()->user()->find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Cet Utilisateur n\'existe pas'
            ], 400);
        }
        if ($user->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Votre Compte a été Supprimé définitivement.'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cet Utilisateur ne peut pas être supprimé'
            ], 500);
        }
    }
}
