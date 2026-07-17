<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearTablaComentarios extends AbstractMigration
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
        $table = $this->table('comentarios');
        $table
            ->addColumn('usuario_id', 'integer')
            ->addColumn('curso_id', 'integer')
            ->addColumn('contenido', 'text')
            ->addColumn('fecha_creacion', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('usuario_id', 'usuarios', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->addForeignKey('curso_id', 'cursos', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
            ->create();
    }
}
