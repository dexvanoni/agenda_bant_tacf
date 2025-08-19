-- 1. Criar tabela datas_liberadas
CREATE TABLE IF NOT EXISTS datas_liberadas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    data DATE NOT NULL UNIQUE,
    limite_agendamentos INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Remover campo espaco_id da tabela agendamentos
ALTER TABLE agendamentos DROP FOREIGN KEY agendamentos_ibfk_1;
ALTER TABLE agendamentos DROP COLUMN espaco_id;

-- 3. Adicionar campo data_liberada_id na tabela agendamentos
ALTER TABLE agendamentos ADD COLUMN data_liberada_id INT AFTER id;
ALTER TABLE agendamentos ADD CONSTRAINT fk_data_liberada FOREIGN KEY (data_liberada_id) REFERENCES datas_liberadas(id);

-- 4. Remover agendamentos que não estejam em datas liberadas
DELETE FROM agendamentos WHERE data_liberada_id IS NULL; 

-- Atualizar tabela agendamentos para novo modelo
ALTER TABLE agendamentos 
    DROP COLUMN posto_graduacao,
    DROP COLUMN nome_solicitante,
    DROP COLUMN setor,
    DROP COLUMN ramal,
    DROP COLUMN nome_evento,
    DROP COLUMN categoria_evento,
    DROP COLUMN quantidade_participantes,
    DROP COLUMN apoio_rancho,
    DROP COLUMN apoio_ti,
    DROP COLUMN email_solicitante,
    ADD COLUMN posto_graduacao VARCHAR(50) NOT NULL AFTER data_liberada_id,
    ADD COLUMN nome_completo VARCHAR(100) NOT NULL AFTER posto_graduacao,
    ADD COLUMN nome_guerra VARCHAR(100) NOT NULL AFTER nome_completo,
    ADD COLUMN email VARCHAR(100) NOT NULL AFTER nome_guerra,
    ADD COLUMN contato VARCHAR(50) NOT NULL AFTER email,
    MODIFY COLUMN data_inicio DATE NOT NULL,
    MODIFY COLUMN data_fim DATE NOT NULL; 