<?php
// Arquivo de teste para validação de CPF
// Este arquivo pode ser removido após os testes

function validarCPF($cpf) {
    // Remove caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    // Verifica se tem 11 dígitos
    if (strlen($cpf) != 11) {
        return false;
    }
    
    // Verifica se todos os dígitos são iguais
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Calcula o primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $cpf[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $dv1 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Calcula o segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $cpf[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $dv2 = ($resto < 2) ? 0 : 11 - $resto;
    
    // Verifica se os dígitos verificadores estão corretos
    return ($cpf[9] == $dv1 && $cpf[10] == $dv2);
}

function formatarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

// CPFs de teste
$cpfs_teste = [
    '111.444.777-35', // Válido
    '123.456.789-09', // Inválido
    '000.000.000-00', // Inválido (todos iguais)
    '111.111.111-11', // Inválido (todos iguais)
    '123.456.789-01', // Inválido
    '529.982.247-25', // Válido
    '111.444.777-36', // Inválido
];

echo "<h2>Teste de Validação de CPF</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>CPF</th><th>Formato</th><th>Válido</th></tr>";

foreach ($cpfs_teste as $cpf) {
    $cpf_limpo = preg_replace('/[^0-9]/', '', $cpf);
    $valido = validarCPF($cpf_limpo);
    $formatado = formatarCPF($cpf_limpo);
    
    echo "<tr>";
    echo "<td>{$cpf}</td>";
    echo "<td>{$formatado}</td>";
    echo "<td style='color: " . ($valido ? 'green' : 'red') . ";'>" . ($valido ? 'SIM' : 'NÃO') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Instruções:</h3>";
echo "<p>1. Execute este arquivo para testar a validação de CPF</p>";
echo "<p>2. Verifique se os CPFs válidos retornam 'SIM'</p>";
echo "<p>3. Após os testes, você pode remover este arquivo</p>";
?>
