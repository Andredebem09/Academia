<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ZerarBanco extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'academia:zerar-banco';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'zerar o banco de dados, removendo todas as tabelas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $database = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');        
        if (empty($tables))
         {
            $this->info(":marca_de_verificação_branca: Nenhuma tabela encontrada no banco '$database'.");
            return;
        }      
          $key = 'Tables_in_' . $database;
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');       
         foreach ($tables as $table)
         {
            $tableName = $table->$key;
            Schema::drop($tableName);
            $this->line(":x_vermelho: Tabela '$tableName' foi dropada.");
        }        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $this->info(":marca_de_verificação_branca: Todas as tabelas do banco '$database' foram removidas.");
    }

}