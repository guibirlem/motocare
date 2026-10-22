<?php

return [
    // T3 adiada pelo usuário; manter a API financeira indisponível por padrão.
    'financeiro' => env('FEATURE_FINANCEIRO', false),
];
