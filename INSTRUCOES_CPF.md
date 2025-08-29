# Instruções para Implementação do Campo CPF

## 1. Aplicar Mudanças no Banco de Dados

Execute o seguinte comando SQL no seu banco de dados para adicionar o campo CPF:

```sql
-- Adicionar campo CPF à tabela de agendamentos
ALTER TABLE agendamentos ADD COLUMN cpf VARCHAR(14) NOT NULL AFTER nome_guerra;

-- Adicionar índice único para CPF para garantir que cada militar só possa ter um agendamento
ALTER TABLE agendamentos ADD UNIQUE INDEX idx_cpf (cpf);

-- Adicionar comentário explicativo
ALTER TABLE agendamentos MODIFY COLUMN cpf VARCHAR(14) NOT NULL COMMENT 'CPF do militar (formato: 000.000.000-00)';
```

Ou execute o arquivo `adicionar_campo_cpf.sql` diretamente no seu banco de dados.

## 2. Arquivos Modificados

- `agendamento.php` - Adicionado campo CPF e validação via AJAX
- `salvar_agendamento.php` - Adicionada validação de CPF único
- `verificar_cpf.php` - Novo arquivo para verificação de CPF via AJAX
- `admin/index.php` - Adicionado botão de limpeza total do sistema
- `admin/limpar_sistema.php` - Novo arquivo para limpeza total do sistema

## 3. Funcionalidades Implementadas

### Campo CPF
- Campo obrigatório no formulário de agendamento
- Formatação automática (000.000.000-00)
- Validação de formato e dígitos verificadores

### Validação de Agendamento Único
- Verificação automática quando o usuário sai do campo CPF
- Consulta via AJAX para verificar se já existe agendamento **ativo**
- **Agendamentos cancelados permitem novo agendamento**
- Mensagem de aviso específica para agendamentos existentes
- Bloqueio de envio do formulário se CPF já possuir agendamento ativo
- **Feedback informativo** para CPFs com agendamentos cancelados anteriores

### Validações
- CPF deve ter 11 dígitos
- CPF deve passar na validação dos dígitos verificadores
- CPF deve ser único (não pode ter agendamento ativo)
- Feedback visual para campos válidos/inválidos

## 4. Fluxo de Validação

1. Usuário digita CPF
2. Sistema formata automaticamente
3. Ao sair do campo, sistema valida formato
4. Se válido, consulta banco via AJAX
5. Se já existe agendamento, mostra aviso e bloqueia
6. Se disponível, permite continuar com o agendamento

## 5. Mensagens de Erro

- "CPF inválido" - Para CPFs com formato incorreto
- "O agendamento é único. Caso haja necessidade de reagendamento, solicite através de Ofício via Cadeia de Comando." - Para CPFs que já possuem agendamento

## 6. Observações Importantes

- O sistema agora impede que um militar faça mais de um agendamento **ativo**
- **Agendamentos cancelados NÃO impedem novos agendamentos** - o militar pode reagendar
- A validação é feita tanto no frontend quanto no backend
- O campo CPF é obrigatório e único no banco de dados
- **Status que impedem novo agendamento**: pendente, aprovado
- **Status que permitem novo agendamento**: cancelado

## 7. Funcionalidade de Limpeza Total

### Botão "Limpar Sistema"
- Localizado no painel administrativo (`admin/index.php`)
- **DUPLA CONFIRMAÇÃO** para evitar limpeza acidental
- Modal vermelho com letras amarelas para alerta visual
- Requer digitação da palavra "LIMPAR" para confirmação
- Confirmação final adicional via `confirm()` do navegador

### O que é removido:
- **TODOS** os agendamentos (pendentes, aprovados, cancelados)
- **TODAS** as datas liberadas
- Auto-increment das tabelas é resetado para 1

### Segurança:
- Apenas administradores logados podem acessar
- Transação de banco para garantir consistência
- Rollback automático em caso de erro
- Log de quantos registros foram removidos

### Arquivo: `admin/limpar_sistema.php`
- Endpoint seguro para limpeza total
- Validação de sessão administrativa
- Tratamento de erros robusto
