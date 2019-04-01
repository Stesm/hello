<?
namespace App\Controllers\Admin;

use App\Models\User;
use Core\Core;
use Core\Helpers\DBRes;

/**
 * Class AppController
 * @package App\Controllers
 */
class Users
{
    /**
     * @return string
     */
    public static function index()
    {
        if(
            array_key_exists('delete', $_GET) &&
            $_GET['delete'] == 'y' &&
            array_key_exists('id', $_GET) &&
            (int) $_GET['id']
        ){
            $user = User::find((int) $_GET['id']);
            if($user){
                User::drop($user['id']);
                Core::$route->redirect('/manage/users/');
            }
        }

        $usr = User::getList()->absorb(DBRes::ASSOC_KEY_FORCE, 'id');

        return Core::view('admin/users.list', ['users' => $usr]);
    }

    /**
     * @param $user_id
     * @return string
     */
    public static function edit($user_id)
    {
        $user = null;
        if($user_id){
            $user = User::find($user_id);
            $user = $user ?? null;
        }

        $res = [
            'user' => $user,
            'error' => null
        ];

        if(array_key_exists('object', $_POST)){
            $data = $_POST['object'];

            if($data['password'] && $data['repassword']){
                if($data['password'] == $data['repassword']){
                    $data['password'] = User::cryptPass($data['password']);
                }else{
                    $res['error'] = 'Пароли не совпадают';
                    unset($data['password']);
                }
            }else
                unset($data['password']);

            unset($data['repassword']);

            if(!$res['error']){
                try{
                    if(array_key_exists('id', $data) && $data['id']){
                        $id = $data['id'];
                        unset($data['id']);

                        User::update($id, $data);
                    }else
                        User::add($data);

                    Core::$route->redirect('/manage/users/');
                } catch (\Exception $e){
                    $res['error'] = $e->getMessage();
                }
            }
        }

        return Core::view('admin/users.edit', $res);
    }
}