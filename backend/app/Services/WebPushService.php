<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use RuntimeException;

class WebPushService
{
    public function send(Alerta $alerta, array $subscription): void
    {
        $moto = $alerta->motocicleta;
        $service = $alerta->tipo_alerta === 'troca_oleo' ? 'Troca de óleo' : 'Revisão';
        $limit = $alerta->km_limite ? " Limite: {$alerta->km_limite} km." : '';
        $date = $alerta->data_limite ? ' Data: '.$alerta->data_limite->format('d/m/Y').'.' : '';
        $this->deliver($moto->usuario, $subscription, ['title' => 'MotoCare: '.$service, 'body' => "{$moto->marca} {$moto->modelo}: {$moto->km_atual} km.{$limit}{$date}", 'tag' => 'motocare-alerta-'.$alerta->id, 'url' => '/alertas']);
    }

    public function sendTest(User $user): void
    {
        $this->deliver($user, $user->push_subscription, ['title' => 'MotoCare: teste de notificação', 'body' => 'As notificações deste navegador estão ativadas.', 'tag' => 'motocare-teste', 'url' => '/alertas']);
    }

    private function deliver(User $user, array $data, array $payload): void
    {
        if (! PushEndpoint::allowed($data['endpoint'] ?? '')) {
            throw new RuntimeException('Destino de push inválido.');
        }
        $subscription = Subscription::create([...$data, 'contentEncoding' => 'aes128gcm']);
        $report = app(WebPush::class)->sendOneNotification($subscription, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        if ($report->isSubscriptionExpired()) {
            User::whereKey($user->id)->where('push_endpoint_hash', hash('sha256', $data['endpoint']))->update(['push_subscription' => null, 'push_endpoint_hash' => null]);
        }
        $status = $report->getResponse()?->getStatusCode() ?? 0;
        if (! $report->isSuccess() || $status < 200 || $status >= 300) {
            throw new RuntimeException('O serviço do navegador não aceitou a notificação.');
        }
    }
}
