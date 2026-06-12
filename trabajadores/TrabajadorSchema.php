<?php
class TrabajadorSchema {

    public static function validateTrabajadorQuery($params) {
        if (empty($params['correo']) && empty($params['matricula'])) {
            Response::error('Se requiere el parámetro correo o matricula', 400);
            exit;
        }
        if (!empty($params['correo']) && !filter_var($params['correo'], FILTER_VALIDATE_EMAIL)) {
            Response::error('Formato de correo inválido', 400);
            exit;
        }
    }

    public static function validateDirectorioQuery($params) {
        $pagina = isset($params['pagina']) ? (int)$params['pagina'] : 1;
        $limite = isset($params['limite']) ? (int)$params['limite'] : 500;

        if ($pagina < 1) {
            Response::error('El parámetro pagina debe ser mayor a 0', 400);
            exit;
        }
        if ($limite < 1 || $limite > 1000) {
            Response::error('El parámetro limite debe estar entre 1 y 1000', 400);
            exit;
        }

        return ['pagina' => $pagina, 'limite' => $limite];
    }
}