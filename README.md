# Sistema de Agendamento - Base Aérea de Natal

## Descrição
Sistema de agendamento de espaços da Base Aérea de Natal, desenvolvido para gerenciar reservas de espaços de forma eficiente e organizada.

## Requisitos do Sistema
- PHP 8.0 ou superior
- MySQL 5.7 ou superior
- Extensões PHP:
  - PDO
  - PDO MySQL
  - OpenSSL
  - Mbstring
- Composer
- Servidor Web (Apache/Nginx)

## Instalação

### 1. Preparação do Ambiente
1. Certifique-se de que todos os requisitos do sistema estão instalados
2. Configure seu servidor web para apontar para o diretório do projeto
3. Configure as permissões dos diretórios:
   ```bash
   chmod 755 -R /caminho/do/projeto
   chmod 777 -R /caminho/do/projeto/config
   ```

### 2. Instalação Automática
1. Acesse o script de instalação através do navegador:
   ```
   http://seu-servidor/install.php
   ```
2. O script irá:
   - Verificar os requisitos do sistema
   - Instalar o Composer (se necessário)
   - Instalar as dependências via Composer
   - Criar o banco de dados
   - Configurar o sistema de email

### 3. Configuração Manual (Alternativa)
Se preferir instalar manualmente:

1. Clone o repositório:
   ```bash
   git clone [url-do-repositorio]
   cd agenda_bant
   ```

2. Instale as dependências via Composer:
   ```bash
   composer install
   ```

3. Crie o banco de dados:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

4. Configure o arquivo de email:
   - Edite `config/email.php`
   - Substitua `seu-email@gmail.com` pelo seu email do Gmail
   - Substitua `sua-senha-de-app` pela senha de app do Gmail

### 4. Configuração do Email
1. Acesse sua conta do Gmail
2. Ative a verificação em duas etapas
3. Gere uma senha de app:
   - Acesse: Configurações da Conta > Segurança
   - Em "Como você acessa o Google", clique em "Senhas de app"
   - Gere uma nova senha para o sistema

### 5. Acesso ao Sistema
- URL do Sistema: `http://seu-servidor/`
- Área Administrativa: `http://seu-servidor/admin/`
- Credenciais padrão:
  - Usuário: admin
  - Senha: admin123

## Funcionalidades

### Página Inicial
- Exibição dos espaços disponíveis para agendamento em cards
- Interface intuitiva e responsiva
- Visualização rápida da disponibilidade dos espaços

### Agendamento
- Calendário interativo com visualização de horários disponíveis
- Formulário de agendamento com os seguintes campos:
  - Nome do solicitante
  - Posto/Graduação
  - Setor
  - Ramal
  - Nome do evento
  - Categoria do evento
  - Quantidade de espectadores/participantes
  - Necessidade de apoio de rancho
  - Necessidade de apoio de TI
  - Observações/Links de reuniões externas

### Administração
- CRUD completo para gerenciamento de espaços
- Configurações do sistema:
  - Antecedência mínima para agendamento
  - Limite de horas consecutivas por usuário
  - Configurações de email para notificações
- Painel de relatórios com:
  - Gráficos de utilização
  - Exportação de dados
  - Filtros por período

### Notificações
- Envio automático de email para a comunicação social
- Confirmação de agendamento
- Notificações de alterações/cancelamentos

## Segurança
- Validação de dados
- Proteção contra SQL Injection
- Sanitização de inputs
- Controle de acesso por nível de usuário

## Suporte
Para suporte técnico, entre em contato com a equipe de TI da Base Aérea de Natal. 