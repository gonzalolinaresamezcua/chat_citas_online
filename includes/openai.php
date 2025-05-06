<?php
/**
 * Funciones para integración con OpenAI API
 */

if (!defined('ACCESO_PERMITIDO')) {
    die('Acceso no autorizado');
}

/**
 * Contador de llamadas a la API para limitar la tasa
 * @return int Número de llamadas realizadas en la sesión actual
 */
function contar_llamada_api() {
    if (!isset($_SESSION['openai_llamadas'])) {
        $_SESSION['openai_llamadas'] = 0;
    }
    $_SESSION['openai_llamadas']++;
    return $_SESSION['openai_llamadas'];
}

/**
 * Verifica si se ha alcanzado el límite de llamadas a la API
 * @return bool True si se alcanzó el límite, false en caso contrario
 */
function limite_alcanzado() {
    return isset($_SESSION['openai_llamadas']) && $_SESSION['openai_llamadas'] >= OPENAI_RATE_LIMIT;
}

/**
 * Realiza una llamada a la API de OpenAI
 * @param string $prompt Texto de entrada para la API
 * @param array $opciones Opciones adicionales para la API
 * @return array Resultado de la operación
 */
function llamar_openai_api($prompt, $opciones = []) {
    if (limite_alcanzado()) {
        return [
            'success' => false,
            'message' => 'Se ha alcanzado el límite de llamadas a la API'
        ];
    }
    
    if (empty(OPENAI_API_KEY)) {
        return [
            'success' => false,
            'message' => 'La clave API de OpenAI no está configurada'
        ];
    }
    
    contar_llamada_api();
    
    $opciones_predeterminadas = [
        'model' => OPENAI_MODEL,
        'temperature' => 0.7,
        'max_tokens' => 150,
        'top_p' => 1.0,
        'frequency_penalty' => 0.0,
        'presence_penalty' => 0.0
    ];
    
    $opciones = array_merge($opciones_predeterminadas, $opciones);
    
    $datos = [
        'model' => $opciones['model'],
        'messages' => [
            ['role' => 'system', 'content' => 'Eres un asistente útil y profesional.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => $opciones['temperature'],
        'max_tokens' => $opciones['max_tokens'],
        'top_p' => $opciones['top_p'],
        'frequency_penalty' => $opciones['frequency_penalty'],
        'presence_penalty' => $opciones['presence_penalty']
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENAI_API_KEY
    ]);
    
    $respuesta = curl_exec($ch);
    $codigo_http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($codigo_http === 200) {
        $respuesta_json = json_decode($respuesta, true);
        if (isset($respuesta_json['choices'][0]['message']['content'])) {
            return [
                'success' => true,
                'text' => trim($respuesta_json['choices'][0]['message']['content'])
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Error al comunicarse con la API de OpenAI',
        'http_code' => $codigo_http,
        'response' => $respuesta
    ];
}

/**
 * Genera una descripción para un servicio basada en palabras clave
 * @param string $keywords Palabras clave separadas por comas
 * @return array Resultado de la operación
 */
function generar_descripcion_servicio($keywords) {
    $prompt = "Genera una descripción profesional y atractiva para un servicio de {$keywords}. " .
              "La descripción debe ser concisa (máximo 150 caracteres), informativa y persuasiva. " .
              "No incluyas precios ni horarios específicos.";
    
    return llamar_openai_api($prompt, [
        'temperature' => 0.8,
        'max_tokens' => 200
    ]);
}

/**
 * Genera un mensaje de confirmación para una cita
 * @param string $servicio Nombre del servicio
 * @param string $fecha Fecha de la cita (formato Y-m-d)
 * @param string $hora Hora de la cita (formato H:i)
 * @return array Resultado de la operación
 */
function generar_mensaje_confirmacion($servicio, $fecha, $hora) {
    $fecha_formateada = date('d/m/Y', strtotime($fecha));
    
    $prompt = "Genera un mensaje de confirmación amable y profesional para una cita. " .
              "Estos son los detalles: " .
              "Servicio: {$servicio}, " .
              "Fecha: {$fecha_formateada}, " .
              "Hora: {$hora}. " .
              "El mensaje debe ser breve, cordial y debe incluir los detalles de la cita.";
    
    return llamar_openai_api($prompt, [
        'temperature' => 0.7,
        'max_tokens' => 150
    ]);
}
