<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Aluno;
use Illuminate\Support\Facades\DB;

class AlunoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usamos uma transação para garantir que o User e o Aluno sejam criados juntos
        DB::transaction(function () {
            // Aluno 1
            $user1 = User::create([
                'name' => 'Ana Carolina',
                'email' => 'ana.carolina@email.com',
                'password' => 'password123'
            ]);
            Aluno::create([
                'user_id' => $user1->id,
                'curso_id' => 1,
                'matricula' => '2023001'
            ]);

            $user1->assignRole('student');


            // Aluno 2
            $user2 = User::create([
                'name' => 'Bruno Dias',
                'email' => 'bruno.dias@email.com',
                'password' => 'password123'
            ]);

            $user2->assignRole('student');

            Aluno::create([
                'user_id' => $user2->id,
                'curso_id' => 2,
                'matricula' => '2023002'
            ]);

            // Aluno 3
            $user3 = User::create([
                'name' => 'Carlos Eduardo',
                'email' => 'carlos.eduardo@email.com',
                'password' => 'password123'
            ]);

            $user3->assignRole('student');
            
            Aluno::create([
                'user_id' => $user3->id,
                'curso_id' => 3,
                'matricula' => '2023003'
            ]);
        });
    }
}
