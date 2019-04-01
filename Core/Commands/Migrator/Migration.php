<?
namespace Core\Commands\Migrator;

use Core\Prototypes\Model;

/**
 * Class AutoBrand
 * @package App\Models
 */
class Migration extends Model
{
    protected static $table = 'migrations';
    protected static $fields = [
        'id',
        'filename',
        'stage',
        'date_migrate'
    ];
}
