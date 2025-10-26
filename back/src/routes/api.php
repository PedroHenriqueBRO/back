<?php

use App\Http\Controllers\AtividadeController;
use App\Http\Controllers\CorrecaoController;
use App\Http\Controllers\ProblemaController;
use App\Http\Controllers\SubmissaoController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AlunoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Todas as rotas da API exigem autenticação via Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Rotas de informações do usuário autenticado
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/user/roles', [AuthController::class, 'roles']);
    Route::get('/user/permissions', [AuthController::class, 'permissions']);

    // ========================================
    // RECURSOS COM AUTORIZAÇÃO VIA POLICIES
    // ========================================
    
    // As policies verificam automaticamente as permissões
    Route::apiResource('atividades', AtividadeController::class);
    Route::apiResource('problemas', ProblemaController::class);
    Route::apiResource('alunos', AlunoController::class);
    Route::apiResource('professores', ProfessorController::class);
    
    // Submissões (sem update e destroy)
    Route::apiResource('submissoes', SubmissaoController::class)
        ->except(['update', 'destroy']);
    
    // Correções
    Route::get('/correcao/busca-por-submissao/{submissao}', [CorrecaoController::class, 'buscaPorSubmissao']);
});
