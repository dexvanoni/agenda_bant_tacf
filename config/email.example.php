<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

// Copie este arquivo para config/email.php e preencha as credenciais corretas.
function enviarEmail($destinatario, $assunto, $mensagem) {
	$host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
	$username = getenv('SMTP_USERNAME') ?: 'seu_email@gmail.com';
	$password = getenv('SMTP_PASSWORD') ?: 'sua_senha_app';
	$from = getenv('SMTP_FROM') ?: $username;
	$fromName = getenv('SMTP_FROM_NAME') ?: 'Sistema de Agendamento';

	$tentativas = [
		['secure' => PHPMailer::ENCRYPTION_SMTPS, 'port' => 465, 'nome' => 'SMTPS/465'],
		['secure' => PHPMailer::ENCRYPTION_STARTTLS, 'port' => 587, 'nome' => 'STARTTLS/587'],
	];

	foreach ($tentativas as $cfg) {
		$mail = new PHPMailer(true);
		try {
			$mail->isSMTP();
			$mail->Host = $host;
			$mail->SMTPAuth = true;
			$mail->Username = $username;
			$mail->Password = $password;
			$mail->SMTPSecure = $cfg['secure'];
			$mail->Port = $cfg['port'];
			$mail->CharSet = 'UTF-8';
			$mail->Timeout = 20;
			$mail->SMTPKeepAlive = false;
			$mail->SMTPDebug = 0;

			$mail->SMTPOptions = [
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true,
				],
			];

			$mail->setFrom($from, $fromName);
			$mail->addAddress($destinatario);

			$mail->isHTML(true);
			$mail->Subject = $assunto;
			$mail->Body = $mensagem;
			$mail->AltBody = strip_tags($mensagem);

			$mail->send();
			return true;
		} catch (Exception $e) {
			// tenta próxima
		}
	}

	return false;
}
?>
