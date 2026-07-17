<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AgregarTituloAEvaluaciones extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('evaluaciones');
        $table
            ->addColumn('titulo', 'string', ['limit' => 255, 'null' => true])
            ->changeColumn('tipo', 'string', ['limit' => 30, 'null' => true])
            ->changeColumn('contenido', 'text', ['null' => true])
            ->changeColumn('solucion_correcta', 'text', ['null' => true])
            ->update();
    }
}
