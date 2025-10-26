<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Aluno;
use App\Http\Resources\AlunoResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlunoController extends Controller
{
    /**
     * Autoriza automaticamente os recursos baseando-se na policy
     */
    public function __construct()
    {
        $this->authorizeResource(User::class, 'aluno');
    }

    /**
     * @OA\Get(
     *     path="/api/alunos",
     *     summary="Lista todos os alunos",
     *     tags={"Alunos"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Operação bem-sucedida",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/AlunoResource"))
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado"
     *     )
     * )
     */
    public function index()
    {
        $alunos = User::join('alunos', 'users.id', '=', 'alunos.user_id')
            ->select('users.*')
            ->latest('users.created_at')
            ->paginate(15);

        return AlunoResource::collection($alunos);
    }

    /**
     * @OA\Post(
     *     path="/api/alunos",
     *     summary="Cria um novo aluno",
     *     tags={"Alunos"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreAlunoRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Aluno criado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/AlunoResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'curso_id' => 'required|exists:cursos,id',
            'matricula' => 'nullable|string|max:255|unique:alunos,matricula',
        ]);

        $user = DB::transaction(function () use ($validated) {
            // Cria o User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            // Atribui a role 'student' automaticamente
            $user->assignRole('student');

            // Cria o Aluno
            Aluno::create([
                'user_id' => $user->id,
                'curso_id' => $validated['curso_id'],
                'matricula' => $validated['matricula'] ?? null,
            ]);

            return $user->load('aluno.curso');
        });

        return (new AlunoResource($user))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/alunos/{id}",
     *     summary="Busca um aluno pelo ID",
     *     tags={"Alunos"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do aluno",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operação bem-sucedida",
     *         @OA\JsonContent(ref="#/components/schemas/AlunoResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aluno não encontrado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado"
     *     )
     * )
     */
    public function show(User $aluno)
    {
        $alunoRecord = Aluno::where('user_id', $aluno->id)->first();
        if (!$alunoRecord) {
            return response()->json(['message' => 'Aluno não encontrado'], 404);
        }
        return new AlunoResource($aluno);
    }

    /**
     * @OA\Put(
     *     path="/api/alunos/{id}",
     *     summary="Atualiza um aluno existente",
     *     tags={"Alunos"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateAlunoRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Aluno atualizado com sucesso",
     *         @OA\JsonContent(ref="#/components/schemas/AlunoResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aluno não encontrado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado"
     *     )
     * )
     */
    public function update(Request $request, User $aluno)
    {
        $alunoRecord = Aluno::where('user_id', $aluno->id)->first();
        if (!$alunoRecord) {
            return response()->json(['message' => 'Aluno não encontrado'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($aluno->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'curso_id' => 'sometimes|required|exists:cursos,id',
            'matricula' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('alunos')->ignore($alunoRecord->user_id, 'user_id')],
        ]);

        DB::transaction(function () use ($aluno, $alunoRecord, $validated, $request) {
            // Atualiza dados do User
            $userData = $request->only('name', 'email');
            if ($request->filled('password')) {
                $userData['password'] = $validated['password'];
            }
            $aluno->update($userData);

            // Atualiza dados do Aluno
            $alunoData = [];
            if ($request->has('curso_id')) {
                $alunoData['curso_id'] = $validated['curso_id'];
            }
            if ($request->has('matricula')) {
                $alunoData['matricula'] = $validated['matricula'];
            }
            if (!empty($alunoData)) {
                $alunoRecord->update($alunoData);
            }
        });

        return new AlunoResource($aluno->fresh('aluno.curso'));
    }

    /**
     * @OA\Delete(
     *     path="/api/alunos/{id}",
     *     summary="Deleta um aluno",
     *     tags={"Alunos"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Aluno deletado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aluno não encontrado"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso negado"
     *     )
     * )
     */
    public function destroy(User $aluno)
    {
        $alunoRecord = Aluno::where('user_id', $aluno->id)->first();
        if (!$alunoRecord) {
            return response()->json(['message' => 'Aluno não encontrado'], 404);
        }

        DB::transaction(function () use ($aluno, $alunoRecord) {
            $alunoRecord->delete();
            $aluno->delete();
        });

        return response()->noContent();
    }
}
