<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AgregarNotaAInscripciones extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('inscripciones');
        $table
            ->addColumn('nota', 'integer', ['null' => true, 'default' => null])
            ->addColumn('aprobado', 'boolean', ['default' => false])
            ->addColumn('fecha_aprobado', 'datetime', ['null' => true, 'default' => null])
            ->update();
    }
}
