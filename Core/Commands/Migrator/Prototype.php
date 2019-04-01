<?php

namespace Core\Commands\Migrator;

abstract class Prototype implements MigrationInterface {
    public function migrate(){}
    public function rollback(){}
}
