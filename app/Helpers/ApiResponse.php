<?php

namespace App\Helpers;

class ApiResponse
{
    /**
     * Retorna uma resposta padronizada de sucesso.
     */
    public static function success($data = null, $message = 'Operação realizada com sucesso', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'data' => $data
        ], $code);
    }

    /**
     * Retorna uma resposta padronizada de erro.
     */
    public static function error($message = 'Erro interno do servidor', $code = 500, $data = null)
    {
        return response()->json([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'data' => $data
        ], $code);
    }
}
