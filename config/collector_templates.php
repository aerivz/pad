<?php

return [
    'default' => 'base_general',
    'templates' => [
        'base_general' => [
            'name' => 'Base general',
            'description' => 'Plantilla academica general usada en la mayoria de materias.',
            'categories' => [
                ['nombre' => 'Tareas', 'porcentaje' => 10, 'tipo_calculo' => 'normal', 'orden' => 1],
                ['nombre' => 'Examenes', 'porcentaje' => 25, 'tipo_calculo' => 'normal', 'orden' => 2],
                ['nombre' => 'Laboratorios', 'porcentaje' => 20, 'tipo_calculo' => 'laboratorio', 'orden' => 3],
                ['nombre' => 'Actividades', 'porcentaje' => 15, 'tipo_calculo' => 'normal', 'orden' => 4],
                ['nombre' => 'Expresion Oral y Escrita', 'porcentaje' => 10, 'tipo_calculo' => 'normal', 'orden' => 5],
                ['nombre' => 'Participacion', 'porcentaje' => 10, 'tipo_calculo' => 'normal', 'orden' => 6],
                ['nombre' => 'Dominio Conceptual y Semantica', 'porcentaje' => 10, 'tipo_calculo' => 'normal', 'orden' => 7],
            ],
        ],
    ],
];
