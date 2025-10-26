<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use Exception;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Atividades",
 *     description="Endpoints para gerenciar as atividades (tarefas com prazo)."
 * )
 */
class AtividadeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Atividade::class, 'atividade');
    }

    /**
     * @OA\Get(
     *      path="/api/atividades",
     *      operationId="getAtividadesList",
     *      tags={"Atividades"},
     *      summary="Lista todas as atividades",
     *      description="Retorna uma lista de todas as atividades cadastradas com seus respectivos problemas detalhados.",
     *      @OA\Response(
     *          response=200,
     *          description="Operação bem-sucedida",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/AtividadeComProblema")
     *          )
     *      )
     * )
     */
    public function index()
    {
        $atividades = Atividade::all();

        return response()->json($atividades);
    }

    /**
     * @OA\Post(
     *      path="/api/atividades",
     *      operationId="storeAtividade",
     *      tags={"Atividades"},
     *      summary="Cria uma nova atividade",
     *      description="Cria uma nova atividade associando um problema e definindo uma data de entrega.",
     *      @OA\RequestBody(
     *          required=true,
     *          description="Dados para a criação da atividade.",
     *          @OA\JsonContent(
     *              required={"data_entrega", "problema_id"},
     *              @OA\Property(property="data_entrega", type="string", format="date-time", example="2024-06-01T23:59:00"),
     *              @OA\Property(property="problema_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Atividade criada com sucesso",
     *          @OA\JsonContent(ref="#/components/schemas/Atividade")
     *      ),
     *      @OA\Response(response=422, description="Erro de validação dos dados")
     * )
     */
    public function store(Request $request)
    {
        $atividade = Atividade::create($request->all());

        return response()->json($atividade);
    }

    /**
     * @OA\Get(
     *      path="/api/atividades/{atividade}",
     *      operationId="getAtividadeById",
     *      tags={"Atividades"},
     *      summary="Exibe uma atividade específica (Não Implementado)",
     *      description="Este endpoint foi planejado, mas ainda não foi implementado.",
     *      deprecated=true,
     *      @OA\Parameter(
     *          name="atividade",
     *          in="path",
     *          required=true,
     *          description="ID da atividade",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=501,
     *          description="Não Implementado"
     *      )
     * )
     */
    public function show(Atividade $atividade)
    {
        //
    }

    /**
     * @OA\Put(
     *      path="/api/atividades/{atividade}",
     *      operationId="updateAtividade",
     *      tags={"Atividades"},
     *      summary="Atualiza uma atividade (Não Implementado)",
     *      description="Este endpoint foi planejado, mas ainda não foi implementado.",
     *      deprecated=true,
     *      @OA\Parameter(name="atividade", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=501,
     *          description="Não Implementado"
     *      )
     * )
     */
    public function update(Request $request, Atividade $atividade)
    {
        //
    }

    /**
     * @OA\Delete(
     *      path="/api/atividades/{atividade}",
     *      operationId="deleteAtividade",
     *      tags={"Atividades"},
     *      summary="Remove uma atividade (Não Implementado)",
     *      description="Este endpoint foi planejado, mas ainda não foi implementado.",
     *      deprecated=true,
     *      @OA\Parameter(name="atividade", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=501,
     *          description="Não Implementado"
     *      )
     * )
     */
    public function destroy(Atividade $atividade)
    {
        //
    }
}
