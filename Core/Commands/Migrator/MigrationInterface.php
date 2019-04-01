<?
namespace Core\Commands\Migrator;

interface MigrationInterface {
    public function migrate();
    public function rollback();
}
