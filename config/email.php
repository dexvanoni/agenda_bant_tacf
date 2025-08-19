<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

function enviarEmail($destinatario, $assunto, $mensagem) {
    // Tentar SMTPS 465, depois STARTTLS 587
    $tentativas = [
        ['secure' => PHPMailer::ENCRYPTION_SMTPS, 'port' => 465, 'nome' => 'SMTPS/465'],
        ['secure' => PHPMailer::ENCRYPTION_STARTTLS, 'port' => 587, 'nome' => 'STARTTLS/587'],
    ];

    foreach ($tentativas as $cfg) {
        $mail = new PHPMailer(true);
        try {
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'eticbant@gmail.com'; // Substitua pelo email do Gmail
            $mail->Password = 'inpnxjsnoruypony'; // Substitua pela senha de app do Gmail
            $mail->SMTPSecure = $cfg['secure'];
            $mail->Port = $cfg['port'];
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 20;
            $mail->SMTPKeepAlive = false;
            // Debug controlado via log (não imprime no cliente)
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str) use ($cfg) {
                error_log('[PHPMailer][' . $cfg['nome'] . '] ' . $str);
            };
            // Flexibilizar verificação TLS em ambiente local (WAMP/Windows)
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Destinatários
            $mail->setFrom('dex.vanoni@gmail.com', 'Sistema de Agendamento do TACF da BANT');
            $mail->addAddress($destinatario);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $mensagem;
            $mail->AltBody = strip_tags($mensagem);

            error_log('[Email] Tentando enviar via ' . $cfg['nome'] . '...');
            $mail->send();
            error_log('[Email] Enviado com sucesso via ' . $cfg['nome']);
            return true;
        } catch (Exception $e) {
            error_log('[Email] Falha via ' . $cfg['nome'] . ': ' . ($mail->ErrorInfo ?? 'sem detalhes') . ' | Exceção: ' . $e->getMessage());
            // Continua para próxima tentativa
        }
    }

    return false;
}
?> 