-- Migração para adicionar campos de reagendamento
-- Execute este script no banco de dados agenda_bant_tacf

-- Adicionar campos na tabela agendamentos
ALTER TABLE agendamentos 
    ADD COLUMN agendamento_original_id INT NULL AFTER id,
    ADD COLUMN motivo_reagendamento TEXT NULL AFTER observacoes,
    ADD COLUMN data_reagendamento DATETIME NULL AFTER updated_at,
    ADD COLUMN usuario_reagendamento VARCHAR(100) NULL AFTER data_reagendamento;

-- Adicionar índice para melhor performance nas consultas de reagendamentos
ALTER TABLE agendamentos 
    ADD INDEX idx_agendamento_original (agendamento_original_id);

-- Adicionar campos na tabela configuracoes
ALTER TABLE configuracoes 
    ADD COLUMN dias_minimos_reagendamento INT NOT NULL DEFAULT 3 AFTER email_sindico_cine_navy,
    ADD COLUMN max_reagendamentos_6meses INT NOT NULL DEFAULT 1 AFTER dias_minimos_reagendamento;

-- Atualizar registros existentes de configuracoes com valores padrão (se necessário)
UPDATE configuracoes 
SET dias_minimos_reagendamento = 3, 
    max_reagendamentos_6meses = 1 
WHERE dias_minimos_reagendamento IS NULL OR max_reagendamentos_6meses IS NULL;

