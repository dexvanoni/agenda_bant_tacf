<?php
$senha = 'admin123';
$hash = password_hash($senha, PASSWORD_DEFAULT);
echo "Senha: " . $senha . "\n";
echo "Hash: " . $hash . "\n";

// Verificar se o hash está correto
if (password_verify($senha, $hash)) {
    echo "\nHash verificado com sucesso!";
} else {
    echo "\nErro na verificação do hash!";
}
?> 