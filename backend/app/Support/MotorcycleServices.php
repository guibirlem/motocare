<?php

namespace App\Support;

class MotorcycleServices
{
    public const DATE_ALERTS = ['seguro', 'ipva', 'licenciamento'];

    public const LABELS = [
        'troca_oleo' => 'Troca de óleo', 'kit_relacao' => 'Kit relação',
        'pneus' => 'Pneus', 'freios' => 'Freios',
        'lubrificacao_corrente' => 'Lubrificação da corrente', 'ajuste_corrente' => 'Ajuste de tensão da corrente',
        'freio_dianteiro' => 'Pastilhas/lonas dianteiras', 'freio_traseiro' => 'Pastilhas/lonas traseiras',
        'pneu_dianteiro' => 'Pneu dianteiro', 'pneu_traseiro' => 'Pneu traseiro',
        'filtro_oleo' => 'Filtro de óleo', 'fluido_suspensao' => 'Fluido da suspensão (bengala)',
        'liquido_arrefecimento' => 'Líquido de arrefecimento', 'cabo_embreagem' => 'Cabo de embreagem',
        'cabo_acelerador' => 'Cabo de acelerador', 'vela' => 'Vela',
    ];

    public static function alerts(): array
    {
        return [...self::LABELS, 'revisao' => 'Revisão', 'abastecimento' => 'Próximo abastecimento',
            'seguro' => 'Seguro', 'ipva' => 'Impostos (IPVA)', 'licenciamento' => 'Licenciamento'];
    }
}
