<?
namespace Core\Migrations;

use Core\Core;
use App\Models\User;
use Core\Commands\Migrator\Prototype as Migration;

class UsersTable extends Migration {

    public function migrate(){
        Core::$db->exec("
            CREATE TABLE IF NOT EXISTS user(
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                login VARCHAR(255) NOT NULL UNIQUE,
                chekword VARCHAR(255) NOT NULL,
                auth_code VARCHAR(255) NULL,
                INDEX mail(email)
            ) ENGINE = innoDB CHARACTER SET = utf8
        ");

        User::add([
            'name' => 'admin',
            'password' => '123456',
            'email' => 'example@mail.com',
            'login' => 'admin'
        ]);
    }

    public function rollback(){
        Core::$db->exec('DROP TABLE IF EXISTS user');
    }
}
