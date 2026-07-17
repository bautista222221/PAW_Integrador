<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CrearPreguntasYOpciones extends AbstractMigration
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
        $preguntas = $this->table('preguntas');
        $preguntas
            ->addColumn('id_evaluacion', 'integer')
            ->addColumn('tipo', 'string', ['limit' => 30])
            ->addColumn('enunciado', 'text')
            ->addColumn('palabra_correcta', 'string', ['limit' => 255, 'null' => true])
            ->addForeignKey('id_evaluacion', 'evaluaciones', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
            ->create();

        $opciones = $this->table('opciones');
        $opciones
            ->addColumn('id_pregunta', 'integer')
            ->addColumn('texto', 'text', ['null' => true])
            ->addColumn('es_correcta', 'integer', ['default' => 0])
            ->addColumn('posicion_correcta', 'integer', ['null' => true])
            ->addForeignKey('id_pregunta', 'preguntas', 'id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
            ->create();
    }
}
