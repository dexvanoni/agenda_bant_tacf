-- Adicionar campo CPF à tabela de agendamentos
ALTER TABLE agendamentos ADD COLUMN cpf VARCHAR(14) NOT NULL AFTER nome_guerra;

-- Preencher registros existentes com CPFs fictícios únicos
-- Gerar CPFs fictícios para registros existentes (formato: 999.999.999-XX)
UPDATE agendamentos SET cpf = CONCAT('999999999', LPAD(id, 2, '0')) WHERE cpf IS NULL OR cpf = '';

-- Adicionar índice único para CPF para garantir que cada militar só possa ter um agendamento
ALTER TABLE agendamentos ADD UNIQUE INDEX idx_cpf (cpf);

-- Adicionar comentário explicativo
ALTER TABLE agendamentos MODIFY COLUMN cpf VARCHAR(14) NOT NULL COMMENT 'CPF do militar (formato: 000.000.000-00)';
