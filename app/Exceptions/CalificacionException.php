<?php

namespace App\Exceptions;

use Exception;

class CalificacionException extends Exception
{
    public static function fueraDeRango(string $campo, float $valor): self
    {
        return new self("La calificación de {$campo} debe estar entre 0 y 100. Recibido: {$valor}");
    }

    public static function materiaFinalizada(string $materia): self
    {
        return new self("La materia {$materia} ya fue finalizada. No puedes modificar sus calificaciones.");
    }

    public static function inscripcionBaja(): self
    {
        return new self('No puedes capturar calificaciones de una materia dada de baja.');
    }

    public static function noEsTuInscripcion(): self
    {
        return new self('Esta inscripción no te pertenece.');
    }

    public static function periodoNoEditable(): self
    {
        return new self('El periodo académico ya cerró. No puedes editar calificaciones.');
    }
}