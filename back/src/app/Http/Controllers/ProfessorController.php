<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfessorController extends Controller
{
    /**
     * Autoriza automaticamente os recursos baseando-se na policy
     */
    public function __construct()
    {
        $this->authorizeResource(Professor::class, 'professor');
    }

    public function index()
    {
        $professores = Professor::with('user')->paginate(15);
        return response()->json($professores, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'area_atuacao' => 'nullable|string|max:255',
        ]);

        try {
            $professor = DB::transaction(function () use ($validated) {
                // Cria o User
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => strtolower($validated['email']),
                    'password' => Hash::make($validated['password']),
                ]);

                // Atribui a role 'professor' automaticamente
                $user->assignRole('professor');

                // Cria o Professor
                $professor = Professor::create([
                    'id' => $user->id,
                    'area_atuacao' => $validated['area_atuacao'] ?? null,
                ]);

                return $professor->load('user');
            });

            return response()->json($professor, 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao criar professor',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Professor $professor)
    {
        return response()->json($professor->load('user'), 200);
    }

    public function update(Request $request, Professor $professor)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $professor->id,
            'password' => 'nullable|string|min:8|confirmed',
            'area_atuacao' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($professor, $validated, $request) {
                // Atualiza dados do Professor
                if ($request->has('area_atuacao')) {
                    $professor->area_atuacao = $validated['area_atuacao'];
                    $professor->save();
                }

                // Atualiza dados do User
                $user = $professor->user;
                if ($request->has('name')) {
                    $user->name = $validated['name'];
                }
                if ($request->has('email')) {
                    $user->email = strtolower($validated['email']);
                }
                if ($request->filled('password')) {
                    $user->password = Hash::make($validated['password']);
                }
                $user->save();
            });

            return response()->json($professor->fresh('user'), 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao atualizar professor',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Professor $professor)
    {
        try {
            DB::transaction(function () use ($professor) {
                $user = $professor->user;
                $professor->delete();
                $user->delete();
            });

            return response()->noContent();
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erro ao deletar professor',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
