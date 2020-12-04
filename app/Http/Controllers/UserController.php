<?php

namespace App\Http\Controllers;

use DB;
use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function __construct()
    {
        $this->user = new User;
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password'=> 'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        \Config::set('jwt.user', 'App\Models\User');
        \Config::set('auth.providers.users.model', \User::class);
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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
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

    public function getUser($id) {
        if (User::where('id', $id)->exists()) {
            $user = User::where('id', $id)->get();
            return response()->json([
                $user
            ], 200);

        } else {
            return response()->json([
                "message" => "Cet Utilisateur n'est inscrit...!"
            ], 404);
        }
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

                $users =user::find(Auth::user()->id);
                $users->password = Hash::make($request->newpassword);
                user::where( 'id' , Auth::user()->id)->update( array( 'password' =>  $users->password));

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

    public function destroy($id)
    {
        DB::table('initiatives')
            ->where(['initiatives.user_id' => $id])
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

}
