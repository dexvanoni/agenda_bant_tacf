<?php
header('Content-Type: text/html; charset=UTF-8');
// Forçar exibição de erros na tela para facilitar o diagnóstico em ambiente local
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "<p>Inicializando teste de e-mail...</p>";
flush();

require_once 'config/email.php';

// Configurações para teste (permite sobrescrever via query string)
$destinatario = isset($_GET['to']) ? $_GET['to'] : 'vanonidvv@fab.mil.br';
$assunto = 'Teste de Envio de Email - Sistema BANT';
$mensagem = "
    <h2>Teste de Envio de Email</h2>
    <p>Este é um email de teste para verificar se o sistema está enviando emails corretamente.</p>
    <p>Data e hora do teste: " . date('d/m/Y H:i:s') . "</p>
    <p>Se você está recebendo este email, significa que o sistema está configurado corretamente.</p>
";

try {
    echo "<p>Enviando email para: <strong>" . htmlspecialchars($destinatario) . "</strong>...</p>";
    flush();

    // Tentar enviar o email
    $resultado = enviarEmail($destinatario, $assunto, $mensagem);
    
    // Exibir resultado
    echo "<h1>Teste de Envio de Email</h1>";
    echo "<p>Status: " . ($resultado ? "Sucesso" : "Falha") . "</p>";
    echo "<p>Destinatário: " . htmlspecialchars($destinatario) . "</p>";
    echo "<p>Assunto: " . htmlspecialchars($assunto) . "</p>";
    echo "<p>Mensagem:</p>";
    echo "<pre>" . htmlspecialchars($mensagem) . "</pre>";
    
    // Exibir informações do servidor
    echo "<h2>Informações do Servidor</h2>";
    echo "<pre>";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? '') . "\n";
    echo "Server Name: " . ($_SERVER['SERVER_NAME'] ?? '') . "\n";
    echo "Server Protocol: " . ($_SERVER['SERVER_PROTOCOL'] ?? '') . "\n";
    echo "Server Port: " . ($_SERVER['SERVER_PORT'] ?? '') . "\n";
    echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
    echo "</pre>";
    
} catch (Throwable $e) {
    echo "<h1>Erro no Teste de Email</h1>";
    echo "<p>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Arquivo/Linha: " . htmlspecialchars($e->getFile()) . ":" . htmlspecialchars($e->getLine()) . "</p>";
    echo "<p>Stack Trace:</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?> 
 