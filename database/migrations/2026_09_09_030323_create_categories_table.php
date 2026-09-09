<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|##########################################################################
| # TEORIA DO DESENVOLVIMENTO WEB: Migrations = Versionamento de BD       #
|##########################################################################
|
| Migrations são o "Git do Banco de Dados".
|
| Elas permitem que EQUIPES DEV compartilhem a MESMA estrutura de banco:
|   - Desenvolvedor A cria uma migration (ex: cria tabela categories)
|   - Desenvolvedor B roda `php artisan migrate` e tem a mesma estrutura
|
| Sintaxe:
|   up()   = o que FAZ quando a migration é executada (aplicada)
|   down() = o que DESFAZ quando roda `php artisan migrate:rollback`
|
| Tipos de colunas comuns:
|   id()         = BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
|   string()     = VARCHAR(255)
|   text()       = TEXT (para textos longos)
|   date()       = DATE (apenas data, sem hora)
|   timestamp()  = DATETIME (data + hora)
|   enum()       = ENUM (valores fixos)
|   foreignId()  = BIGINT UNSIGNED (para FK)
|   nullable()   = permite NULL (padrão é NOT NULL)
|   default(xx)  = valor padrão
|   timestamps() = adiciona colunas 'created_at' e 'updated_at'
|
| Foreign Keys:
|   foreignId('user_id') → constrained()
|      → adiciona automaticamente FK para 'id' da tabela 'users'
|      → onDelete('cascade') = se apagar usuário, apaga categorias dele
|
| Ordem IMPORTA: sempre crie PRIMEIRO a tabela referenciada, depois a que
| tem a FK. Ex: primeiro 'users', depois 'categories' (categories.user_id
| referencia users.id).
|##########################################################################
*/
class CreateCategoriesTable extends Migration
{
    /**
     * Aplica a migration: CRIAR a tabela 'categories'
     *
     * Relação:
     *   Uma categoria PERTENCE a UM usuário (user_id → FK users.id)
     *   Uma categoria TEM MUITAS tarefas (1:N - ver migration de tasks)
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {

            $table->id();

            /* 🔗 Chave estrangeira para usuário dono da categoria
             * Cada categoria pertence a um único usuário.
             * onDelete('cascade') = quando um usuário é deletado,
             *   remove também suas categorias.
             */
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name', 100);                    // Nome exibido: "Trabalho", "Pessoal", etc.
            $table->string('slug', 120)->unique();          // URL amigável: "trabalho", "meu-projeto"
            $table->text('description')->nullable();        // Descrição longa (opcional)

            $table->timestamps();

            // Índice para acelerar buscas do usuário por suas categorias
            $table->index(['user_id']);
        });
    }

    /**
     * Reverte a migration: APAGAR a tabela (rollback).
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
}
