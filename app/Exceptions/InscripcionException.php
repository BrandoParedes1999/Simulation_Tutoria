<?php

namespace App\Exceptions;

use Exception;

class InscripcionException extends Exception
{
    public static function periodoNoAbierto(): self
    {
        return new self('El periodo de inscripción está cerrado.');
    }

    public static function periodoBajaCerrado(): self
    {
        return new self('El periodo para dar de baja materias ya cerró.');
    }

    public static function prerrequisitosNoCumplidos(string $materia, array $faltantes): self
    {
        $lista = implode(', ', $faltantes);
        return new self("No cumples los prerrequisitos de {$materia}. Faltan: {$lista}");
    }

    public static function yaInscrita(string $materia): self
    {
        return new self("Ya tienes una inscripción activa en {$materia} para este periodo.");
    }

    public static function materiaYaAprobada(string $materia): self
    {
        return new self("Ya aprobaste {$materia}. No puedes volver a inscribirla.");
    }

    public static function excedeCreditos(int $actual, int $max): self
    {
        return new self("Excedes el límite de créditos por semestre ({$actual}/{$max}).");
    }

    public static function materiaInactiva(string $materia): self
    {
        return new self("La materia {$materia} no está disponible actualmente.");
    }

    public static function alumnoInactivo(): self
    {
        return new self('Tu cuenta no está activa. Contacta a control escolar.');
    }

    public static function sinPeriodoActivo(): self
    {
        return new self('No hay un periodo activo en el sistema.');
    }

    public static function noEsTuInscripcion(): self
    {
        return new self('Esta inscripción no te pertenece.');
    }
}